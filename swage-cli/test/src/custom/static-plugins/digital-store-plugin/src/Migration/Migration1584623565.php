<?php declare(strict_types=1);

namespace Emphazis\DigitalStore\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

use Emphazis\DigitalStore\DigitalStore;

class Migration1584623565 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1584623565;
    }

    public function update(Connection $connection): void
    {
        $table_prefix = DigitalStore::TABLE_PREFIX;

        $sql = <<<SQL
CREATE TABLE `{$table_prefix}_asset` (
    `id` BINARY(16) NOT NULL,
    `name` VARCHAR(80) NOT NULL,
    `description` VARCHAR(255) DEFAULT '',
    `access_level` VARCHAR(15) NOT NULL DEFAULT 'public',
    `asset_latest` BINARY(16) NOT NULL,
    `version_counter` INT DEFAULT 0,
    `version_latest` VARCHAR(5) NOT NULL DEFAULT 'v1',
    `notfiy_on_update` BOOLEAN DEFAULT 1,
    `max_times_usable` INT DEFAULT -1,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`)
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
