<?php

declare(strict_types=1);

namespace fKuenzel\Faq;

use Doctrine\DBAL\Connection;
use fKuenzel\Faq\Core\Content\FaqProductAssignment\FaqProductAssignmentDefinition;
use fKuenzel\Faq\Core\Content\FaqProductRule\FaqProductCategoryRuleDefinition;
use fKuenzel\Faq\Core\Content\FaqProductRule\FaqProductTagRuleDefinition;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

final class FkFaq extends Plugin
{
    /**
     * Reihenfolge beachten: abhaengige Tabellen zuerst, sonst blockieren die Fremdschluessel.
     */
    private const TABLES = [
        'fk_faq_product_category_rule',
        'fk_faq_product_tag_rule',
        'fk_faq_product_assignment',
        // Aus einem frueheren Entwurf; wird bei neuen Installationen nicht mehr
        // angelegt, steht hier nur noch fuer den Rueckbau alter Testinstanzen.
        'fk_faq_cms_block_assignment',
        'fk_faq_tag_mapping',
        'fk_faq_tag_translation',
        'fk_faq_tag',
        'fk_faq_translation',
        'fk_faq',
        'fk_faq_category_translation',
        'fk_faq_category',
    ];

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection = $this->container?->get(Connection::class);

        if (!$connection instanceof Connection) {
            return;
        }

        foreach (self::TABLES as $table) {
            $connection->executeStatement(\sprintf('DROP TABLE IF EXISTS `%s`', $table));
        }
    }

    /**
     * Fuer die eigenen FAQ-Entities (fk_faq, fk_faq_category, fk_faq_tag) generiert
     * Shopware die Standard-ACL-Rechte automatisch, weil jede ein eigenes
     * Administration-Modul mit passender `entity`-Bindung hat. Diese drei
     * Zuordnungs-/Regel-Entities haben kein eigenes Modul (sie werden nur intern
     * ueber den FAQ-Tab in der Produktverwaltung angesprochen) und bekommen daher
     * keine eigene Rechtegruppe im Rollen-Editor. Ohne diese Ergaenzung kann ein
     * Redakteur mit der Standardrolle "Produkte" die FAQ-Zuordnung im Produkt-Tab
     * nicht speichern bzw. nicht einmal sehen.
     *
     * @return array<string, list<string>>
     */
    public function enrichPrivileges(): array
    {
        $readPrivileges = [
            FaqProductAssignmentDefinition::ENTITY_NAME . ':read',
            FaqProductCategoryRuleDefinition::ENTITY_NAME . ':read',
            FaqProductTagRuleDefinition::ENTITY_NAME . ':read',
        ];

        $writePrivileges = array_merge($readPrivileges, [
            FaqProductAssignmentDefinition::ENTITY_NAME . ':create',
            FaqProductAssignmentDefinition::ENTITY_NAME . ':update',
            FaqProductAssignmentDefinition::ENTITY_NAME . ':delete',
            FaqProductCategoryRuleDefinition::ENTITY_NAME . ':create',
            FaqProductCategoryRuleDefinition::ENTITY_NAME . ':update',
            FaqProductCategoryRuleDefinition::ENTITY_NAME . ':delete',
            FaqProductTagRuleDefinition::ENTITY_NAME . ':create',
            FaqProductTagRuleDefinition::ENTITY_NAME . ':update',
            FaqProductTagRuleDefinition::ENTITY_NAME . ':delete',
        ]);

        return [
            'product.viewer' => $readPrivileges,
            'product.editor' => $writePrivileges,
        ];
    }
}
