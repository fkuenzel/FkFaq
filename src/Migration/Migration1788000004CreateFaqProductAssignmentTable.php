<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1788000004CreateFaqProductAssignmentTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1788000004;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_product_assignment` (
                `id`                 BINARY(16)  NOT NULL,
                `product_id`         BINARY(16)  NOT NULL,
                `product_version_id` BINARY(16)  NOT NULL,
                `fk_faq_id`          BINARY(16)  NOT NULL,
                `position`           INT(11)     NOT NULL DEFAULT 0,
                `created_at`         DATETIME(3) NOT NULL,
                `updated_at`         DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.fk_faq_product_assignment.product_faq` (`product_id`, `product_version_id`, `fk_faq_id`),
                KEY `idx.fk_faq_product_assignment.fk_faq_id` (`fk_faq_id`),
                CONSTRAINT `fk.fk_faq_product_assignment.product_id`
                    FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.fk_faq_product_assignment.fk_faq_id`
                    FOREIGN KEY (`fk_faq_id`) REFERENCES `fk_faq` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
