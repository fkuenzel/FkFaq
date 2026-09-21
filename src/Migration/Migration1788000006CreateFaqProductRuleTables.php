<?php

declare(strict_types=1);

namespace fKuenzel\Faq\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Dynamische Zuordnung: je Produkt koennen FAQ-Kategorien und FAQ-Tags
 * hinterlegt werden. Die daraus resultierenden FAQs kommen zusaetzlich zu
 * den manuell zugeordneten.
 */
final class Migration1788000006CreateFaqProductRuleTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1788000006;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_product_category_rule` (
                `id`                 BINARY(16)  NOT NULL,
                `product_id`         BINARY(16)  NOT NULL,
                `product_version_id` BINARY(16)  NOT NULL,
                `fk_faq_category_id` BINARY(16)  NOT NULL,
                `created_at`         DATETIME(3) NOT NULL,
                `updated_at`         DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.fk_faq_product_category_rule.product_category` (`product_id`, `product_version_id`, `fk_faq_category_id`),
                KEY `idx.fk_faq_product_category_rule.fk_faq_category_id` (`fk_faq_category_id`),
                CONSTRAINT `fk.fk_faq_product_category_rule.product_id`
                    FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.fk_faq_product_category_rule.fk_faq_category_id`
                    FOREIGN KEY (`fk_faq_category_id`) REFERENCES `fk_faq_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `fk_faq_product_tag_rule` (
                `id`                 BINARY(16)  NOT NULL,
                `product_id`         BINARY(16)  NOT NULL,
                `product_version_id` BINARY(16)  NOT NULL,
                `fk_faq_tag_id`      BINARY(16)  NOT NULL,
                `created_at`         DATETIME(3) NOT NULL,
                `updated_at`         DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.fk_faq_product_tag_rule.product_tag` (`product_id`, `product_version_id`, `fk_faq_tag_id`),
                KEY `idx.fk_faq_product_tag_rule.fk_faq_tag_id` (`fk_faq_tag_id`),
                CONSTRAINT `fk.fk_faq_product_tag_rule.product_id`
                    FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.fk_faq_product_tag_rule.fk_faq_tag_id`
                    FOREIGN KEY (`fk_faq_tag_id`) REFERENCES `fk_faq_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
