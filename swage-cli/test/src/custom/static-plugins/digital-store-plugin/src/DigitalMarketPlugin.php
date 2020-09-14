<?php declare(strict_types=1);

namespace Emphazis\DigitalStore;

use Doctrine\DBAL\Connection;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class DigitalStore extends Plugin
{
    const TABLE_PREFIX = 'plugin_dgmkt';

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $table_prefix = static::TABLE_PREFIX;

        $connection = $this->container->get(Connection::class);


        $connection->executeQuery('DROP TABLE IF EXISTS `' . $table_prefix . '_asset_access`');
        
        $connection->executeQuery('DROP TABLE IF EXISTS `' . $table_prefix . '_asset_token`');
        
        $connection->executeQuery('DROP TABLE IF EXISTS `' . $table_prefix . '_asset`');

    }
}
