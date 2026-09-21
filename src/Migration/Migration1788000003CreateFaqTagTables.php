<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1788000003CreateFaqTagTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1788000003;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_tag` (
                `id`         BINARY(16)  NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_tag_translation` (
                `fk_faq_tag_id` BINARY(16)   NOT NULL,
                `language_id`   BINARY(16)   NOT NULL,
                `name`          VARCHAR(255) NOT NULL,
                `created_at`    DATETIME(3)  NOT NULL,
                `updated_at`    DATETIME(3)  NULL,
                PRIMARY KEY (`fk_faq_tag_id`, `language_id`),
                CONSTRAINT `fk.fk_faq_tag_translation.fk_faq_tag_id`
                    FOREIGN KEY (`fk_faq_tag_id`) REFERENCES `fk_faq_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.fk_faq_tag_translation.language_id`
                    FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        // Mapping-Tabelle fuer die Many-to-Many-Beziehung FAQ <-> Tag
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_tag_mapping` (
                `fk_faq_id`     BINARY(16) NOT NULL,
                `fk_faq_tag_id` BINARY(16) NOT NULL,
                PRIMARY KEY (`fk_faq_id`, `fk_faq_tag_id`),
                KEY `idx.fk_faq_tag_mapping.fk_faq_tag_id` (`fk_faq_tag_id`),
                CONSTRAINT `fk.fk_faq_tag_mapping.fk_faq_id`
                    FOREIGN KEY (`fk_faq_id`) REFERENCES `fk_faq` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.fk_faq_tag_mapping.fk_faq_tag_id`
                    FOREIGN KEY (`fk_faq_tag_id`) REFERENCES `fk_faq_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
