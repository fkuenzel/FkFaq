<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqCategory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqCategoryTranslation>
 */
final class FaqCategoryTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'fk_faq_category_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return FaqCategoryTranslation::class;
    }
}
