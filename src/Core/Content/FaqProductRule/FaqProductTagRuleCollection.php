<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqProductRule;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqProductTagRuleEntity>
 */
final class FaqProductTagRuleCollection extends EntityCollection
{
    /**
     * @return array<int, string>
     */
    public function getFaqTagIds(): array
    {
        $ids = [];

        foreach ($this->getElements() as $rule) {
            $tagId = $rule->getFkFaqTagId();

            if ($tagId !== null) {
                $ids[] = $tagId;
            }
        }

        return array_values(array_unique($ids));
    }

    public function getApiAlias(): string
    {
        return 'fk_faq_product_tag_rule_collection';
    }

    protected function getExpectedClass(): string
    {
        return FaqProductTagRuleEntity::class;
    }
}
