<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Cms;

use fKuenzel\Faq\Service\FaqStorefrontService;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;

/**
 * Laedt die FAQs fuer das CMS-Element aus der Slot-Konfiguration.
 *
 * Die Konfiguration eines CMS-Elements liegt in cms_slot.config, nicht in
 * einer eigenen Tabelle. Der Resolver liest sie und reicht die IDs an den
 * FaqStorefrontService weiter, der dieselbe Aufloesung wie auf der
 * Produktseite vornimmt: manuelle Auswahl zuerst, danach die ueber
 * FAQ-Kategorie und FAQ-Tag ermittelten, ohne Doppelte.
 */
final class FaqCmsElementResolver extends AbstractCmsElementResolver
{
    public const TYPE = 'fk-faq';

    public function __construct(
        private readonly FaqStorefrontService $faqStorefrontService,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        // Die Abfragen laufen im FaqStorefrontService, nicht ueber den
        // Criteria-Sammler des CMS.
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $struct = new FaqCmsStruct();
        $slot->setData($struct);

        $config = $slot->getFieldConfig();

        $struct->setHeadline($this->readString($slot, 'headline'));
        $struct->setShowHeadline($config->get('showHeadline')?->getBoolValue() ?? true);

        $faqIds = $this->readIds($slot, 'faqIds');
        $categoryIds = $this->readIds($slot, 'categoryIds');
        $tagIds = $this->readIds($slot, 'tagIds');

        if ($faqIds === [] && $categoryIds === [] && $tagIds === []) {
            return;
        }

        $salesChannelContext = $resolverContext->getSalesChannelContext();

        $struct->setFaqs($this->faqStorefrontService->getFaqs(
            $faqIds,
            $categoryIds,
            $tagIds,
            $salesChannelContext->getContext(),
            $salesChannelContext->getSalesChannelId(),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function readIds(CmsSlotEntity $slot, string $key): array
    {
        $config = $slot->getFieldConfig()->get($key);

        if ($config === null) {
            return [];
        }

        $ids = [];

        foreach ($config->getArrayValue() as $value) {
            if (\is_string($value) && $value !== '') {
                $ids[] = $value;
            }
        }

        return array_values(array_unique($ids));
    }

    private function readString(CmsSlotEntity $slot, string $key): ?string
    {
        $config = $slot->getFieldConfig()->get($key);

        if ($config === null) {
            return null;
        }

        $value = $config->getValue();

        if (!\is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
