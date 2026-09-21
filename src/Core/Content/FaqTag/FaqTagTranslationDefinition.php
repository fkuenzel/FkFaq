<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqTag;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

final class FaqTagTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'fk_faq_tag_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FaqTagTranslation::class;
    }

    public function getCollectionClass(): string
    {
        return FaqTagTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return FaqTagDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),

            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
