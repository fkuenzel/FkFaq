<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Service;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Core\Content\Faq\FaqEntity;
use fKuenzel\Faq\Core\Content\FaqProductAssignment\FaqProductAssignmentCollection;
use fKuenzel\Faq\Core\Content\FaqProductAssignment\FaqProductAssignmentEntity;
use fKuenzel\Faq\Core\Content\FaqProductRule\FaqProductCategoryRuleCollection;
use fKuenzel\Faq\Core\Content\FaqProductRule\FaqProductTagRuleCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

/**
 * Ermittelt die FAQs, die zu einem Produkt gehoeren.
 *
 * Drei Quellen, die zusammengefuehrt werden:
 *  1. manuelle Zuordnung  – explizit gewaehlte FAQs, in gepflegter Reihenfolge
 *  2. Kategorie-Regel     – alle FAQs der am Produkt hinterlegten FAQ-Kategorien
 *  3. Tag-Regel           – alle FAQs mit den am Produkt hinterlegten FAQ-Tags
 *
 * Die manuellen FAQs stehen vorn, danach folgen die dynamisch ermittelten,
 * sortiert nach der Plugin-Konfiguration. Doppelte werden entfernt.
 */
class FaqStorefrontService
{
    public const SORT_ALPHABETICAL_ASC = 'alphabetical_asc';
    public const SORT_ALPHABETICAL_DESC = 'alphabetical_desc';
    public const SORT_CREATED_ASC = 'created_asc';
    public const SORT_CREATED_DESC = 'created_desc';

    public const SORT_ORDERS = [
        self::SORT_ALPHABETICAL_ASC,
        self::SORT_ALPHABETICAL_DESC,
        self::SORT_CREATED_ASC,
        self::SORT_CREATED_DESC,
    ];

    private const MAX_RESULTS = 100;

    /**
     * @param EntityRepository<FaqCollection> $faqRepository
     * @param EntityRepository<FaqProductAssignmentCollection> $productAssignmentRepository
     * @param EntityRepository<FaqProductCategoryRuleCollection> $productCategoryRuleRepository
     * @param EntityRepository<FaqProductTagRuleCollection> $productTagRuleRepository
     */
    public function __construct(
        private readonly EntityRepository $faqRepository,
        private readonly EntityRepository $productAssignmentRepository,
        private readonly EntityRepository $productCategoryRuleRepository,
        private readonly EntityRepository $productTagRuleRepository,
        private readonly ConfigService $configService,
    ) {
    }

    public function getFaqsForProduct(
        string $productId,
        Context $context,
        ?string $salesChannelId = null,
        ?string $sortOrder = null,
    ): FaqCollection {
        return $this->resolve(
            $this->getManuallyAssignedFaqIds($productId, $context),
            $this->loadCategoryRuleReferences($productId, $context),
            $this->loadTagRuleReferences($productId, $context),
            $context,
            $salesChannelId,
            $sortOrder,
        );
    }

    /**
     * Dieselbe Aufloesung fuer frei gewaehlte Quellen, etwa aus der
     * Konfiguration eines CMS-Elements.
     *
     * @param array<int, string> $faqIds manuell gewaehlte FAQs, in gewuenschter Reihenfolge
     * @param array<int, string> $categoryIds
     * @param array<int, string> $tagIds
     */
    public function getFaqs(
        array $faqIds,
        array $categoryIds,
        array $tagIds,
        Context $context,
        ?string $salesChannelId = null,
        ?string $sortOrder = null,
    ): FaqCollection {
        return $this->resolve($faqIds, $categoryIds, $tagIds, $context, $salesChannelId, $sortOrder);
    }

    /**
     * Fuehrt manuelle Auswahl und Regeltreffer zusammen.
     *
     * @param array<int, string> $manualIds
     * @param array<int, string> $categoryIds
     * @param array<int, string> $tagIds
     */
    private function resolve(
        array $manualIds,
        array $categoryIds,
        array $tagIds,
        Context $context,
        ?string $salesChannelId,
        ?string $sortOrder,
    ): FaqCollection {
        $manualIds = array_values(array_unique(array_filter($manualIds)));

        $dynamicIds = $this->getRuleBasedFaqIds($categoryIds, $tagIds, $context);

        // Manuell zugeordnete gewinnen: dynamische Treffer, die schon manuell
        // gesetzt sind, werden nicht doppelt ausgegeben.
        $dynamicIds = array_values(array_diff($dynamicIds, $manualIds));

        if ($manualIds === [] && $dynamicIds === []) {
            return new FaqCollection();
        }

        $faqs = $this->loadActiveFaqs(array_merge($manualIds, $dynamicIds), $context);

        return $this->order($faqs, $manualIds, $this->resolveSortOrder($sortOrder, $salesChannelId));
    }

    /**
     * Alle aktiven FAQs einer FAQ-Kategorie.
     */
    public function getFaqsByCategory(
        string $categoryId,
        Context $context,
        ?string $salesChannelId = null,
        ?string $sortOrder = null,
    ): FaqCollection {
        $criteria = $this->baseCriteria();
        $criteria->addFilter(new EqualsFilter('categoryId', $categoryId));

        $faqs = $this->faqRepository->search($criteria, $context)->getEntities();

        return $this->order($faqs, [], $this->resolveSortOrder($sortOrder, $salesChannelId));
    }

    /**
     * Alle aktiven FAQs, die mindestens einen der Tags tragen.
     *
     * @param array<int, string> $tagIds
     */
    public function getFaqsByTags(
        array $tagIds,
        Context $context,
        ?string $salesChannelId = null,
        ?string $sortOrder = null,
    ): FaqCollection {
        if ($tagIds === []) {
            return new FaqCollection();
        }

        $criteria = $this->baseCriteria();
        $criteria->addFilter(new EqualsAnyFilter('tags.id', $tagIds));

        $faqs = $this->faqRepository->search($criteria, $context)->getEntities();

        return $this->order($faqs, [], $this->resolveSortOrder($sortOrder, $salesChannelId));
    }

    /**
     * Volltextsuche ueber Frage und Antwort.
     */
    public function searchFaqs(
        string $searchTerm,
        Context $context,
        ?string $salesChannelId = null,
        ?string $sortOrder = null,
    ): FaqCollection {
        $searchTerm = trim($searchTerm);

        if ($searchTerm === '') {
            return new FaqCollection();
        }

        $criteria = $this->baseCriteria();
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new ContainsFilter('title', $searchTerm),
            new ContainsFilter('answer', $searchTerm),
        ]));

        $faqs = $this->faqRepository->search($criteria, $context)->getEntities();

        return $this->order($faqs, [], $this->resolveSortOrder($sortOrder, $salesChannelId));
    }

    /**
     * @return array<int, string> FAQ-IDs in gepflegter Reihenfolge
     */
    private function getManuallyAssignedFaqIds(string $productId, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));
        $criteria->setLimit(self::MAX_RESULTS);

        $ids = [];

        foreach ($this->productAssignmentRepository->search($criteria, $context)->getEntities() as $assignment) {
            if (!$assignment instanceof FaqProductAssignmentEntity) {
                continue;
            }

            $faqId = $assignment->getFkFaqId();

            if ($faqId !== null) {
                $ids[] = $faqId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Aktive FAQs, die in einer der Kategorien liegen oder einen der Tags tragen.
     *
     * @param array<int, string> $categoryIds
     * @param array<int, string> $tagIds
     *
     * @return array<int, string>
     */
    private function getRuleBasedFaqIds(array $categoryIds, array $tagIds, Context $context): array
    {
        $categoryIds = array_values(array_unique(array_filter($categoryIds)));
        $tagIds = array_values(array_unique(array_filter($tagIds)));

        if ($categoryIds === [] && $tagIds === []) {
            return [];
        }

        $conditions = [];

        if ($categoryIds !== []) {
            $conditions[] = new EqualsAnyFilter('categoryId', $categoryIds);
        }

        if ($tagIds !== []) {
            $conditions[] = new EqualsAnyFilter('tags.id', $tagIds);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $conditions));
        $criteria->setLimit(self::MAX_RESULTS);

        /** @var array<int, string> $ids */
        $ids = $this->faqRepository->searchIds($criteria, $context)->getIds();

        return $ids;
    }

    /**
     * @return array<int, string>
     */
    private function loadCategoryRuleReferences(string $productId, Context $context): array
    {
        return $this->productCategoryRuleRepository
            ->search($this->ruleCriteria($productId), $context)
            ->getEntities()
            ->getFaqCategoryIds();
    }

    /**
     * @return array<int, string>
     */
    private function loadTagRuleReferences(string $productId, Context $context): array
    {
        return $this->productTagRuleRepository
            ->search($this->ruleCriteria($productId), $context)
            ->getEntities()
            ->getFaqTagIds();
    }

    private function ruleCriteria(string $productId): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->setLimit(self::MAX_RESULTS);

        return $criteria;
    }

    /**
     * @param array<int, string> $faqIds
     */
    private function loadActiveFaqs(array $faqIds, Context $context): FaqCollection
    {
        $criteria = $this->baseCriteria();
        $criteria->setIds($faqIds);

        return $this->faqRepository->search($criteria, $context)->getEntities();
    }

    private function baseCriteria(): Criteria
    {
        $criteria = new Criteria();
        $criteria->addAssociation('category');
        $criteria->addAssociation('tags');
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->setLimit(self::MAX_RESULTS);

        return $criteria;
    }

    /**
     * Manuelle FAQs in gepflegter Reihenfolge, danach die uebrigen sortiert.
     *
     * @param array<int, string> $manualIds
     */
    private function order(FaqCollection $faqs, array $manualIds, string $sortOrder): FaqCollection
    {
        $manual = [];
        $dynamic = [];

        foreach ($faqs as $faq) {
            if (!$faq instanceof FaqEntity) {
                continue;
            }

            $position = array_search($faq->getId(), $manualIds, true);

            if ($position === false) {
                $dynamic[] = $faq;

                continue;
            }

            $manual[$position] = $faq;
        }

        ksort($manual);
        usort($dynamic, $this->comparator($sortOrder));

        return new FaqCollection(array_merge(array_values($manual), $dynamic));
    }

    /**
     * @return callable(FaqEntity, FaqEntity): int
     */
    private function comparator(string $sortOrder): callable
    {
        return static function (FaqEntity $a, FaqEntity $b) use ($sortOrder): int {
            return match ($sortOrder) {
                self::SORT_ALPHABETICAL_DESC => strnatcasecmp((string) $b->getTitle(), (string) $a->getTitle()),
                self::SORT_CREATED_ASC => ($a->getCreatedAt()?->getTimestamp() ?? 0) <=> ($b->getCreatedAt()?->getTimestamp() ?? 0),
                self::SORT_CREATED_DESC => ($b->getCreatedAt()?->getTimestamp() ?? 0) <=> ($a->getCreatedAt()?->getTimestamp() ?? 0),
                default => strnatcasecmp((string) $a->getTitle(), (string) $b->getTitle()),
            };
        };
    }

    private function resolveSortOrder(?string $sortOrder, ?string $salesChannelId): string
    {
        if ($sortOrder !== null && \in_array($sortOrder, self::SORT_ORDERS, true)) {
            return $sortOrder;
        }

        $configured = $this->configService->getDefaultSortOrder($salesChannelId);

        return \in_array($configured, self::SORT_ORDERS, true) ? $configured : self::SORT_ALPHABETICAL_ASC;
    }
}
