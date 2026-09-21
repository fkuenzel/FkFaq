<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqProductAssignment;

use fKuenzel\Faq\Core\Content\Faq\FaqDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

final class FaqProductAssignmentDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'fk_faq_product_assignment';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FaqProductAssignmentEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FaqProductAssignmentCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'position' => 0,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new FkField('fk_faq_id', 'fkFaqId', FaqDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new IntField('position', 'position'))->addFlags(new ApiAware()),

            new CreatedAtField(),
            new UpdatedAtField(),

            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false),
            new ManyToOneAssociationField('faq', 'fk_faq_id', FaqDefinition::class, 'id', false),
        ]);
    }
}
