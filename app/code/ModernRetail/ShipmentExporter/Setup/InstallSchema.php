<?php

namespace ModernRetail\ShipmentExporter\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /** 
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface    $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $salesShipmentTable = $installer->getTable('sales_shipment');

        $columns = [
            'middlware_number' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'default' => null,
                'comment' => 'ID In Middlware',
            ],

        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($salesShipmentTable, $name, $definition);
        }

        $installer->endSetup();
    }
}