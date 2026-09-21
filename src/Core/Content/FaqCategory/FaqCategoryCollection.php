<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqCategory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqCategoryEntity>
 */
final class FaqCategoryCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FaqCategoryEntity::class;
    }
}
