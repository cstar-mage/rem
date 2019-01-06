<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

namespace Amasty\Acart\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('amasty_acart_attribute'))
                ->addColumn(
                    'attr_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Attribute Id'
                )
                ->addColumn(
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Rule Id'
                )
                ->addColumn(
                    'code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Email'
                )
                ->addForeignKey(
                    $setup->getFkName('amasty_acart_attribute', 'rule_id', 'amasty_acart_rule', 'rule_id'),
                    'rule_id',
                    $setup->getTable('amasty_acart_rule'),
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Amasty Acart Attribute');
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $table = $setup->getTable('amasty_acart_history');
            $connection = $setup->getConnection();

            $connection->addColumn(
                $table,
                'sales_rule_coupon_expiration_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'comment' => 'Expiration Date'
                ]
            );
        }

        $setup->endSetup();
    }
}