<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqTag;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqTagEntity>
 */
final class FaqTagCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FaqTagEntity::class;
    }
}
