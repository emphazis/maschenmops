<?php declare(strict_types=1);

namespace Emphazis\DigitalStore\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

use Emphazis\DigitalStore\DigitalStore;

class Migration1584625811 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1584625811;
    }

    public function update(Connection $connection): void
    {
        $table_prefix = DigitalStore::TABLE_PREFIX;

        $sql = <<<SQL
    CREATE TABLE `{$table_prefix}_asset_token` (
        `token_id` BINARY(16) NOT NULL,
        `token_value` VARCHAR(40) NOT NULL,
        `asset_id` BINARY(16) NOT NULL,
        `customer_id` BINARY(16),
        `created_at` DATETIME(3) NOT NULL,
        PRIMARY KEY (`token_id`, `asset_id`),
        CONSTRAINT `fk.{$table_prefix}_asset_token.asset_id` FOREIGN KEY (`asset_id`)
            REFERENCES `{$table_prefix}_asset` (`id`) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE
    ) 
    ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4 
    COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeQuery($sql);

    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
