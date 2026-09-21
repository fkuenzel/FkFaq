<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Core\Content\Product;

use fKuenzel\Faq\Core\Content\FaqProductAssignment\FaqProductAssignmentDefinition;
use fKuenzel\Faq\Core\Content\FaqProductRule\FaqProductCategoryRuleDefinition;
use fKuenzel\Faq\Core\Content\FaqProductRule\FaqProductTagRuleDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * Haengt die FAQ-Zuordnungen als Assoziationen an die Produkt-Entity.
 *
 * Damit schreibt der normale Produkt-Speichern-Vorgang die Zuordnungen mit,
 * genau wie bei Medien, Preisen oder Cross-Sellings. Der Reiter braucht
 * dadurch keinen eigenen Speichern-Button und keine eigenen API-Aufrufe.
 *
 * Der Extension-Flag wird von EntityDefinition::compile() automatisch gesetzt,
 * die Felder liegen im Ergebnis unter product.extensions.*.
 */
final class ProductExtension extends EntityExtension
{
    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'fkFaqAssignments',
                FaqProductAssignmentDefinition::class,
                'product_id'
            ))->addFlags(new ApiAware(), new CascadeDelete())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'fkFaqCategoryRules',
                FaqProductCategoryRuleDefinition::class,
                'product_id'
            ))->addFlags(new ApiAware(), new CascadeDelete())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'fkFaqTagRules',
                FaqProductTagRuleDefinition::class,
                'product_id'
            ))->addFlags(new ApiAware(), new CascadeDelete())
        );
    }
}
