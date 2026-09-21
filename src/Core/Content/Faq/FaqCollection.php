<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Faq;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqEntity>
 */
final class FaqCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FaqEntity::class;
    }
}
