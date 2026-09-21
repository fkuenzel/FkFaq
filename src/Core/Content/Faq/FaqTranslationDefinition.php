<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Faq;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

final class FaqTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'fk_faq_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FaqTranslation::class;
    }

    public function getCollectionClass(): string
    {
        return FaqTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return FaqDefinition::class;
    }

    /**
     * Parent-FK (fk_faq_id) und language_id werden von EntityTranslationDefinition
     * automatisch ergaenzt und duerfen hier nicht erneut definiert werden.
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('title', 'title'))->addFlags(new ApiAware(), new Required()),
            (new LongTextField('answer', 'answer'))->addFlags(new ApiAware(), new AllowHtml()),

            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
