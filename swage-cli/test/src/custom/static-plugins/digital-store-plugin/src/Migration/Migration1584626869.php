<?php declare(strict_types=1);

namespace Emphazis\DigitalStore\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

use Emphazis\DigitalStore\DigitalStore;

class Migration1584626869 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1584626869;
    }

    public function update(Connection $connection): void
    {
        $table_prefix = DigitalStore::TABLE_PREFIX;

        $sql = <<<SQL
    CREATE TABLE `{$table_prefix}_asset_access` (
        `id` BINARY(16) NOT NULL,
        `asset_id` BINARY(16) NOT NULL,
        `token_id` BINARY(16) NOT NULL,
        `request_raw` TEXT NOT NULL,
        `downloaded_at` DATETIME(3) NOT NULL,
        PRIMARY KEY (`id`, `asset_id`, `token_id`),
        CONSTRAINT `fk.{$table_prefix}_asset_access.token_id` FOREIGN KEY (`token_id`)
            REFERENCES `{$table_prefix}_asset_token` (`token_id`) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE,
        CONSTRAINT `fk.{$table_prefix}_asset_access.asset_id` FOREIGN KEY (`asset_id`)
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
