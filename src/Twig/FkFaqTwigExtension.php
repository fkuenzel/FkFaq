<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Twig;

use fKuenzel\Faq\Core\Content\Faq\FaqCollection;
use fKuenzel\Faq\Service\FaqStorefrontService;
use fKuenzel\Faq\Service\StructuredDataService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig-Funktionen fuer die Storefront.
 */
final class FkFaqTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly FaqStorefrontService $faqStorefrontService,
        private readonly StructuredDataService $structuredDataService,
    ) {
    }

    /**
     * @return array<int, TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('fk_faq_for_product', $this->getFaqsForProduct(...), ['needs_context' => true]),
            new TwigFunction('fk_faq_search', $this->searchFaqs(...), ['needs_context' => true]),
            new TwigFunction('fk_faq_schema_tag', $this->getSchemaTag(...), [
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @param array<string, mixed> $twigContext
     */
    public function getFaqsForProduct(array $twigContext, string $productId, ?string $sortOrder = null): FaqCollection
    {
        return $this->faqStorefrontService->getFaqsForProduct(
            $productId,
            $this->resolveContext($twigContext),
            $this->resolveSalesChannelId($twigContext),
            $sortOrder,
        );
    }

    /**
     * @param array<string, mixed> $twigContext
     */
    public function searchFaqs(array $twigContext, string $searchTerm, ?string $sortOrder = null): FaqCollection
    {
        return $this->faqStorefrontService->searchFaqs(
            $searchTerm,
            $this->resolveContext($twigContext),
            $this->resolveSalesChannelId($twigContext),
            $sortOrder,
        );
    }

    /**
     * @param array<string, mixed> $twigContext
     */
    public function getSchemaTag(array $twigContext, FaqCollection $faqs, ?string $pageUrl = null): string
    {
        if (!$this->structuredDataService->isSchemaGenerationEnabled($this->resolveSalesChannelId($twigContext))) {
            return '';
        }

        return $this->structuredDataService->renderSchemaTag(
            $this->structuredDataService->generateFaqPageSchema($faqs, $pageUrl),
        );
    }

    /**
     * In der Storefront liegt unter "context" ein SalesChannelContext,
     * in der Administration ein Context.
     *
     * @param array<string, mixed> $twigContext
     */
    private function resolveContext(array $twigContext): Context
    {
        $context = $twigContext['context'] ?? null;

        if ($context instanceof SalesChannelContext) {
            return $context->getContext();
        }

        if ($context instanceof Context) {
            return $context;
        }

        return Context::createDefaultContext();
    }

    /**
     * @param array<string, mixed> $twigContext
     */
    private function resolveSalesChannelId(array $twigContext): ?string
    {
        $context = $twigContext['context'] ?? null;

        return $context instanceof SalesChannelContext ? $context->getSalesChannelId() : null;
    }
}
