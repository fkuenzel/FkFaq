<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Faq;

use fKuenzel\Faq\Core\Content\FaqCategory\FaqCategoryDefinition;
use fKuenzel\Faq\Core\Content\FaqProductAssignment\FaqProductAssignmentDefinition;
use fKuenzel\Faq\Core\Content\FaqTag\FaqTagDefinition;
use fKuenzel\Faq\Core\Content\FaqTagMapping\FaqTagMappingDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

final class FaqDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'fk_faq';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FaqEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FaqCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => true,
            'position' => 0,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            (new TranslatedField('title'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('answer'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::LOW_SEARCH_RANKING)),

            (new FkField('category_id', 'categoryId', FaqCategoryDefinition::class))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),

            new CreatedAtField(),
            new UpdatedAtField(),

            (new TranslationsAssociationField(FaqTranslationDefinition::class, 'fk_faq_id'))->addFlags(new ApiAware(), new Required(), new CascadeDelete()),

            (new ManyToOneAssociationField('category', 'category_id', FaqCategoryDefinition::class, 'id', false))->addFlags(new ApiAware()),

            (new ManyToManyAssociationField(
                'tags',
                FaqTagDefinition::class,
                FaqTagMappingDefinition::class,
                'fk_faq_id',
                'fk_faq_tag_id'
            ))->addFlags(new ApiAware(), new CascadeDelete()),

            (new OneToManyAssociationField('productAssignments', FaqProductAssignmentDefinition::class, 'fk_faq_id'))->addFlags(new ApiAware(), new CascadeDelete()),
        ]);
    }
}
