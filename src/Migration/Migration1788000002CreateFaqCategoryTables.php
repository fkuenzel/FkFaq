<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1788000002CreateFaqCategoryTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1788000002;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_category` (
                `id`         BINARY(16)  NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_category_translation` (
                `fk_faq_category_id` BINARY(16)   NOT NULL,
                `language_id`        BINARY(16)   NOT NULL,
                `name`               VARCHAR(255) NOT NULL,
                `created_at`         DATETIME(3)  NOT NULL,
                `updated_at`         DATETIME(3)  NULL,
                PRIMARY KEY (`fk_faq_category_id`, `language_id`),
                CONSTRAINT `fk.fk_faq_category_translation.fk_faq_category_id`
                    FOREIGN KEY (`fk_faq_category_id`) REFERENCES `fk_faq_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.fk_faq_category_translation.language_id`
                    FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        // Fremdschluessel fk_faq.category_id nur anlegen, wenn er noch nicht existiert.
        // Migrationen laufen nicht in einer Transaktion, deshalb muss der Schritt idempotent sein.
        $faqTable = $connection->createSchemaManager()->introspectTable('fk_faq');

        if (!$faqTable->hasForeignKey('fk.fk_faq.category_id')) {
            $connection->executeStatement('
                ALTER TABLE `fk_faq`
                ADD CONSTRAINT `fk.fk_faq.category_id`
                FOREIGN KEY (`category_id`) REFERENCES `fk_faq_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
