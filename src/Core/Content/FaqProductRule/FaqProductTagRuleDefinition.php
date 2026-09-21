<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqProductRule;

use fKuenzel\Faq\Core\Content\FaqTag\FaqTagDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * Regel: alle FAQs mit diesem FAQ-Tags gelten fuer dieses Produkt.
 */
final class FaqProductTagRuleDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'fk_faq_product_tag_rule';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FaqProductTagRuleEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FaqProductTagRuleCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('fk_faq_tag_id', 'fkFaqTagId', FaqTagDefinition::class))->addFlags(new ApiAware(), new Required()),

            new CreatedAtField(),
            new UpdatedAtField(),

            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false),
            new ManyToOneAssociationField('faqTag', 'fk_faq_tag_id', FaqTagDefinition::class, 'id', false),
        ]);
    }
}
