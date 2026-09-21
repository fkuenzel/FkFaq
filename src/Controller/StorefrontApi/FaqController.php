<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Controller\StorefrontApi;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Core\Content\Faq\FaqEntity;
use fKuenzel\Faq\Service\FaqStorefrontService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Store-API Endpunkte für FAQ-Abfragen.
 *
 * Diese Controller-Klasse stellt rein lesende REST-API-Endpunkte für:
 * - FAQ-Listing mit Filterung und Suche
 * - FAQ-Abfragen pro Produkt
 * - FAQ-Kategorien und Tags
 *
 * zur Verfügung, um Headless-Commerce-Implementierungen zu unterstützen.
 * Schreibende Operationen laufen bewusst nicht über die Store-API (oeffentlich,
 * ohne Authentifizierung erreichbar), sondern ueber die generische, ACL-
 * geschuetzte Admin-API, die Shopware fuer jede shopware.entity.definition
 * (hier: FaqDefinition) automatisch bereitstellt (/api/fk-faq).
 */
#[Route(path: '/store-api', defaults: ['_routeScope' => ['store-api']])]
final class FaqController extends AbstractController
{
    /**
     * @param EntityRepository<FaqCollection> $faqRepository
     * @param EntityRepository<\Shopware\Core\Content\Category\CategoryCollection> $faqCategoryRepository
     * @param EntityRepository<\Shopware\Core\System\Tag\TagCollection> $faqTagRepository
     */
    public function __construct(
        private readonly FaqStorefrontService $faqStorefrontService,
        private readonly EntityRepository $faqRepository,
        private readonly EntityRepository $faqCategoryRepository,
        private readonly EntityRepository $faqTagRepository,
    ) {
    }

    /**
     * Sucht FAQs nach Kategorie, Tags oder Suchbegriff.
     *
     * Query-Parameter:
     * - search: Volltextsuche in Titel und Antwort
     * - categoryId: Filter nach FAQ-Kategorie
     * - tagIds: Filter nach FAQ-Tags (komma-getrennt)
     * - sortOrder: alphabetical_asc|alphabetical_desc|created_asc|created_desc
     * - limit: Ergebnisse pro Seite (1-100, Standard 25)
     * - page: Seite (Standard 1)
     */
    #[Route(path: '/faq', name: 'api.faq.search', methods: ['GET'])]
    public function searchFaqs(Request $request, SalesChannelContext $context): JsonResponse
    {
        $searchTerm = $request->query->getString('search', '');
        $categoryId = $request->query->getString('categoryId', '');
        $tagIds = array_filter(explode(',', $request->query->getString('tagIds', '')));
        $sortOrder = $request->query->getString('sortOrder', '');
        $limit = $this->validateLimit((int)$request->query->get('limit', 25));
        $page = max(1, (int)$request->query->get('page', 1));

        try {
            $faqs = match (true) {
                !empty($searchTerm) => $this->faqStorefrontService->searchFaqs(
                    $searchTerm,
                    $context->getContext(),
                    $context->getSalesChannel()->getId(),
                    $sortOrder ?: null
                ),
                !empty($categoryId) => $this->faqStorefrontService->getFaqsByCategory(
                    $categoryId,
                    $context->getContext(),
                    $context->getSalesChannel()->getId(),
                    $sortOrder ?: null
                ),
                !empty($tagIds) => $this->faqStorefrontService->getFaqsByTags(
                    $tagIds,
                    $context->getContext(),
                    $context->getSalesChannel()->getId(),
                    $sortOrder ?: null
                ),
                default => $this->faqStorefrontService->getFaqs(
                    [],
                    [],
                    [],
                    $context->getContext(),
                    $context->getSalesChannel()->getId(),
                    $sortOrder ?: null
                )
            };

            $total = $faqs->count();
            $offset = ($page - 1) * $limit;
            $paginatedFaqs = array_slice($faqs->getElements(), $offset, $limit);

            return new JsonResponse([
                'data' => array_map(
                    fn(FaqEntity $faq) => $this->serializeFaq($faq),
                    $paginatedFaqs
                ),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => (int)ceil($total / $limit),
                ],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Fehler beim Abrufen der FAQs',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Gibt eine einzelne FAQ aus.
     */
    #[Route(path: '/faq/{faqId}', name: 'api.faq.detail', methods: ['GET'])]
    public function getFaq(string $faqId, SalesChannelContext $context): JsonResponse
    {
        $criteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria([$faqId]);
        $criteria->addAssociation('category');
        $criteria->addAssociation('tags');
        $criteria->addFilter(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter('active', true));

        $faq = $this->faqRepository->search($criteria, $context->getContext())->first();

        if (!$faq) {
            return new JsonResponse([
                'error' => 'FAQ nicht gefunden',
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'data' => $this->serializeFaq($faq),
        ]);
    }

    /**
     * Gibt alle FAQs eines Produkts aus.
     *
     * Query-Parameter:
     * - sortOrder: alphabetical_asc|alphabetical_desc|created_asc|created_desc
     */
    #[Route(path: '/products/{productId}/faqs', name: 'api.product.faqs', methods: ['GET'])]
    public function getProductFaqs(
        string $productId,
        Request $request,
        SalesChannelContext $context
    ): JsonResponse {
        $sortOrder = $request->query->getString('sortOrder', '');

        try {
            $faqs = $this->faqStorefrontService->getFaqsForProduct(
                $productId,
                $context->getContext(),
                $context->getSalesChannel()->getId(),
                $sortOrder ?: null
            );

            return new JsonResponse([
                'data' => array_map(
                    fn(FaqEntity $faq) => $this->serializeFaq($faq),
                    $faqs->getElements()
                ),
                'meta' => [
                    'total' => $faqs->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Fehler beim Abrufen der Produkt-FAQs',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Gibt alle FAQ-Kategorien aus.
     */
    #[Route(path: '/faq-categories', name: 'api.faq.categories', methods: ['GET'])]
    public function getFaqCategories(
        Request $request,
        SalesChannelContext $context
    ): JsonResponse {
        $limit = $this->validateLimit((int)$request->query->get('limit', 25));
        $page = max(1, (int)$request->query->get('page', 1));

        $criteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $criteria->setOffset(($page - 1) * $limit);
        $criteria->setLimit($limit);

        $result = $this->faqCategoryRepository->search($criteria, $context->getContext());

        return new JsonResponse([
            'data' => array_map(
                fn($category) => [
                    'id' => $category->getId(),
                    'name' => $category->get('name'),
                ],
                $result->getEntities()->getElements()
            ),
            'meta' => [
                'total' => $result->getTotal(),
                'page' => $page,
                'limit' => $limit,
                'pages' => (int)ceil($result->getTotal() / $limit),
            ],
        ]);
    }

    /**
     * Gibt alle FAQ-Tags aus.
     */
    #[Route(path: '/faq-tags', name: 'api.faq.tags', methods: ['GET'])]
    public function getFaqTags(
        Request $request,
        SalesChannelContext $context
    ): JsonResponse {
        $limit = $this->validateLimit((int)$request->query->get('limit', 25));
        $page = max(1, (int)$request->query->get('page', 1));

        $criteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $criteria->setOffset(($page - 1) * $limit);
        $criteria->setLimit($limit);

        $result = $this->faqTagRepository->search($criteria, $context->getContext());

        return new JsonResponse([
            'data' => array_map(
                fn($tag) => [
                    'id' => $tag->getId(),
                    'name' => $tag->get('name'),
                ],
                $result->getEntities()->getElements()
            ),
            'meta' => [
                'total' => $result->getTotal(),
                'page' => $page,
                'limit' => $limit,
                'pages' => (int)ceil($result->getTotal() / $limit),
            ],
        ]);
    }

    /**
     * Validiert und begrenzt den Limit-Parameter.
     */
    private function validateLimit(int $limit): int
    {
        return max(1, min(100, $limit));
    }

    /**
     * Serialisiert eine FAQ-Entity zu einem Array.
     *
     * @return array<string, mixed>
     */
    private function serializeFaq(FaqEntity $faq): array
    {
        return [
            'id' => $faq->getId(),
            'title' => $faq->getTitle(),
            'answer' => $faq->getAnswer(),
            'categoryId' => $faq->getCategoryId(),
            'category' => $faq->getCategory() ? [
                'id' => $faq->getCategory()->getId(),
                'name' => $faq->getCategory()->get('name'),
            ] : null,
            'tags' => $faq->getTags() ? array_map(
                fn($tag) => [
                    'id' => $tag->getId(),
                    'name' => $tag->get('name'),
                ],
                $faq->getTags()->getElements()
            ) : [],
            'active' => $faq->isActive(),
            'createdAt' => $faq->getCreatedAt()?->format('c'),
            'updatedAt' => $faq->getUpdatedAt()?->format('c'),
        ];
    }
}
