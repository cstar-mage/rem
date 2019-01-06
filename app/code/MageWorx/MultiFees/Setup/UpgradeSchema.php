<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->modifyTotalBaseAmount($setup);
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->addMethodsColumns($setup);
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->removeSalesMethodsColumn($setup);
        }

        $setup->endSetup();
    }

    /**
     * Remove unused column `sales_methods`
     *
     * @param SchemaSetupInterface $setup
     */
    protected function removeSalesMethodsColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->dropColumn($setup->getTable('mageworx_multifees_fee'), 'sales_methods');
    }

    /**
     * Update total base amount field
     *
     * @param SchemaSetupInterface $setup
     *
     */
    protected function modifyTotalBaseAmount(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->modifyColumn(
            $setup->getTable('mageworx_multifees_fee'),
            'total_base_amount',
            [
                'type'      => Table::TYPE_DECIMAL,
                'scale'     => '2',
                'precision' => '10'
            ]
        );
    }

    /**
     * Add shipping and payment methods columns
     *
     * @param SchemaSetupInterface $setup
     *
     */
    protected function addMethodsColumns(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable('mageworx_multifees_fee'),
            FeeInterface::SHIPPING_METHODS,
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => '64k',
                'comment' => 'Shipping Methods'
            ]
        );

        $connection->addColumn(
            $setup->getTable('mageworx_multifees_fee'),
            FeeInterface::PAYMENT_METHODS,
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => '64k',
                'comment' => 'Payment Methods'
            ]
        );
    }
}
