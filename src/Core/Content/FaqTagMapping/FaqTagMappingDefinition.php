<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\FaqTagMapping;

use fKuenzel\Faq\Core\Content\Faq\FaqDefinition;
use fKuenzel\Faq\Core\Content\FaqTag\FaqTagDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

final class FaqTagMappingDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'fk_faq_tag_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('fk_faq_id', 'fkFaqId', FaqDefinition::class))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('fk_faq_tag_id', 'fkFaqTagId', FaqTagDefinition::class))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            new ManyToOneAssociationField('faq', 'fk_faq_id', FaqDefinition::class, 'id', false),
            new ManyToOneAssociationField('tag', 'fk_faq_tag_id', FaqTagDefinition::class, 'id', false),
        ]);
    }
}
