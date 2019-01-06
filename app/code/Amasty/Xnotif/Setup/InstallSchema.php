<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $tableName = $setup->getTable('product_alert_stock');
        $this->updateAlertTable($setup, $tableName);

        $tableName = $setup->getTable('product_alert_price');
        $this->updateAlertTable($setup, $tableName);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param $tableName
     */
    private function updateAlertTable(SchemaSetupInterface $installer, $tableName)
    {
        $config = $installer->getConnection()->getConfig();
        $dbname = $config['dbname'];

        $installer->getConnection()->addColumn(
            $tableName,
            'parent_id',
            [
                'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'LENGTH' => null,
                'COMMENT' => 'Parent Id'
            ]
        );

        $installer->getConnection()->addColumn(
            $tableName,
            'email',
            [
                'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'LENGTH' => null,
                'COMMENT' => 'Email'
            ]
        );

        $select = $installer->getConnection()->select()
            ->from('information_schema.key_column_usage')
            ->where('table_name = ?', $tableName)
            ->where('column_name = ?', 'customer_id')
            ->where('table_schema = ?', $dbname);
        $keys = $select->query()->fetchAll();

        foreach ($keys as $keyName) {
            if (isset($keyName['CONSTRAINT_NAME'])) {
                $installer->getConnection()->query(
                    "
                    ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$keyName['CONSTRAINT_NAME']}`;
                "
                );
            }
        }

        $sql = "SHOW INDEX FROM `{$tableName}` where column_name = 'customer_id'";
        $keys = $installer->getConnection()->rawQuery($sql)->fetchAll();
        foreach ($keys as $keyName) {
            if (isset($keyName['Key_name'])) {
                $installer->getConnection()->query(
                    "
                        ALTER TABLE `{$tableName}` DROP INDEX `{$keyName['Key_name']}`;
                    "
                );
            }
        }
    }
}
