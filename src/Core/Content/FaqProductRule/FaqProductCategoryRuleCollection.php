<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqProductRule;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqProductCategoryRuleEntity>
 */
final class FaqProductCategoryRuleCollection extends EntityCollection
{
    /**
     * @return array<int, string>
     */
    public function getFaqCategoryIds(): array
    {
        $ids = [];

        foreach ($this->getElements() as $rule) {
            $categoryId = $rule->getFkFaqCategoryId();

            if ($categoryId !== null) {
                $ids[] = $categoryId;
            }
        }

        return array_values(array_unique($ids));
    }

    public function getApiAlias(): string
    {
        return 'fk_faq_product_category_rule_collection';
    }

    protected function getExpectedClass(): string
    {
        return FaqProductCategoryRuleEntity::class;
    }
}
