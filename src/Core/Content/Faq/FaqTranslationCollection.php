<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Faq;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqTranslation>
 */
final class FaqTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'fk_faq_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return FaqTranslation::class;
    }
}
