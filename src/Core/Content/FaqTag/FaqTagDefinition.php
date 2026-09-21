<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqTag;

use fKuenzel\Faq\Core\Content\Faq\FaqDefinition;
use fKuenzel\Faq\Core\Content\FaqTagMapping\FaqTagMappingDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

final class FaqTagDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'fk_faq_tag';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FaqTagEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FaqTagCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            (new TranslatedField('name'))->addFlags(new ApiAware()),

            new CreatedAtField(),
            new UpdatedAtField(),

            (new TranslationsAssociationField(FaqTagTranslationDefinition::class, 'fk_faq_tag_id'))->addFlags(new ApiAware(), new Required(), new CascadeDelete()),

            (new ManyToManyAssociationField(
                'faqs',
                FaqDefinition::class,
                FaqTagMappingDefinition::class,
                'fk_faq_tag_id',
                'fk_faq_id'
            ))->addFlags(new ApiAware(), new CascadeDelete()),
        ]);
    }
}
