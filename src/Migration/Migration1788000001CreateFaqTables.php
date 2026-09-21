<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1788000001CreateFaqTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1788000001;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq` (
                `id`          BINARY(16)  NOT NULL,
                `category_id` BINARY(16)  NULL,
                `active`      TINYINT(1)  NOT NULL DEFAULT 1,
                `position`    INT(11)     NOT NULL DEFAULT 0,
                `created_at`  DATETIME(3) NOT NULL,
                `updated_at`  DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `idx.fk_faq.category_id` (`category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_translation` (
                `fk_faq_id`   BINARY(16)   NOT NULL,
                `language_id` BINARY(16)   NOT NULL,
                `title`       VARCHAR(255) NOT NULL,
                `answer`      LONGTEXT     NULL,
                `created_at`  DATETIME(3)  NOT NULL,
                `updated_at`  DATETIME(3)  NULL,
                PRIMARY KEY (`fk_faq_id`, `language_id`),
                CONSTRAINT `fk.fk_faq_translation.fk_faq_id`
                    FOREIGN KEY (`fk_faq_id`) REFERENCES `fk_faq` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.fk_faq_translation.language_id`
                    FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
