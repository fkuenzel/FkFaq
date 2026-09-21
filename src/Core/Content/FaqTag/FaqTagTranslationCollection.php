<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqTag;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<FaqTagTranslation>
 */
final class FaqTagTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'fk_faq_tag_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return FaqTagTranslation::class;
    }
}
