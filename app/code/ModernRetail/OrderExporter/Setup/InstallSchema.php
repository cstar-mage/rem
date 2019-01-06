<?php

namespace ModernRetail\OrderExporter\Setup;

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

        $salesOrderTable = $installer->getTable('sales_order');
        $salesInvoiceTable = $installer->getTable('sales_invoice');
        $salesCreditmemoTable = $installer->getTable('sales_creditmemo');

        $columns = [
            'is_sent_to_middlware' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length' => 1,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Is Sent to Middlware',
            ],

        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($salesOrderTable, $name, $definition);
            $connection->addColumn($salesInvoiceTable, $name, $definition);
            $connection->addColumn($salesCreditmemoTable, $name, $definition);
        }

        $installer->endSetup();
    }
}