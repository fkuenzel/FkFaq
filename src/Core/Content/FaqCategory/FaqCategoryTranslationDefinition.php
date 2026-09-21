<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqCategory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

final class FaqCategoryTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'fk_faq_category_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FaqCategoryTranslation::class;
    }

    public function getCollectionClass(): string
    {
        return FaqCategoryTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return FaqCategoryDefinition::class;
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
