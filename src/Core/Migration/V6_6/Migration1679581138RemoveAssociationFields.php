<?php declare(strict_types=1);

namespace Shopware\Core\Migration\V6_6;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1679581138RemoveAssociationFields extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1679581138;
    }

    public function update(Connection $connection): void
    {
        if ($this->columnExists($connection, 'media_default_folder', 'association_fields')) {
            $connection->executeStatement('ALTER TABLE `media_default_folder` CHANGE `association_fields` `association_fields` JSON NULL');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        $this->dropColumnIfExists($connection, 'media_default_folder', 'association_fields');
    }
}
