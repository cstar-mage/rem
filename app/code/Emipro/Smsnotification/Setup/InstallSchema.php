<?php

/**
 * {{Emipro}}_{{Smsnotification}} extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Emipro
 *                     @package   Emipro_Smsnotification
 *                     @copyright Copyright (c) 2015
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */

namespace Emipro\Smsnotification\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface {

    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('emipro_smsnotification_smsevent')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('emipro_smsnotification_smsevent')
                    )
                    ->addColumn(
                            'smsevent_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                            ], 'SMS Event ID'
                    )
                    ->addColumn(
                            'sms_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'SMS Event SMS  Title'
                    )
                    ->addColumn(
                            'sms_events', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'SMS Event SMS Event'
                    )
                    ->addColumn(
                            'sms_content', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', ['nullable => false'], 'SMS Event SMS Content'
                    )
                    ->addColumn(
                            'is_active', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                        'nullable' => false,
                        'unsigned' => true,
                            ], 'Is Active'
                    )
                    ->addColumn(
                            'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'SMS Event Created At'
                    )
                    ->addColumn(
                            'updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'SMS Event Updated At'
                    )
                    ->setComment('SMS Event Table');
            $installer->getConnection()->createTable($table);



            //data table for store wise data start
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('emipro_smsnotification_smsevent_store')
                    )
                    ->addColumn(
                            'smsevent_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                            ], 'SMS Event ID'
                    )
                    ->addColumn(
                            'sms_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'SMS Event SMS  Title'
                    )
                    ->addColumn(
                            'sms_events', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [], 'SMS Event SMS Event'
                    )
                    ->addColumn(
                            'sms_content', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', ['nullable => false'], 'SMS Event SMS Content'
                    )
                    ->addColumn(
                            'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false,
                        'unsigned' => true,], 'SMS Event SMS Content'
                    )
                    ->addColumn(
                            'main_entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false,
                        'unsigned' => true,], 'Main table id '
                    )
                    ->addColumn(
                            'use_default', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                        'nullable' => false,
                        'unsigned' => true,
                            ], 'Use Default'
                    )
                    ->addColumn(
                            'is_active', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                        'nullable' => false,
                        'unsigned' => true,
                            ], 'Is Active'
                    )
                    ->addColumn(
                            'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'SMS Event Created At'
                    )
                    ->addColumn(
                            'updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'SMS Event Updated At'
                    )
                    ->setComment('SMS Event Table');
            $installer->getConnection()->createTable($table);


            //data table for store wise data end

            if (!$installer->tableExists('emipro_smsnotification_smslog')) {
                $table = $installer->getConnection()->newTable(
                                $installer->getTable('emipro_smsnotification_smslog')
                        )
                        ->addColumn(
                                'smslog_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                            'identity' => true,
                            'nullable' => false,
                            'primary' => true,
                            'unsigned' => true,
                                ], 'SMS Log ID'
                        )
                        ->addColumn(
                                'order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 100, [], 'SMS Order number'
                        )
                        ->addColumn(
                                'customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 100, [], 'SMS Customer id'
                        )
                        ->addColumn(
                                'sms_content', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', ['nullable => false'], 'SMS Event SMS Content'
                        )
                        ->addColumn(
                                'message_type', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [], 'SMS Type'
                        )
                        ->addColumn(
                                'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'SMS Log Created At'
                        )
                        ->addColumn(
                                'updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'SMS Log Updated At'
                        )
                        ->addColumn(
                                'api_result', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', ['nullable => false'], 'api result'
                        )
                        ->addColumn(
                                'contact_number', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'contact_number'
                        )
                        ->setComment('SMS Log Table');
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                        $installer->getTable('emipro_smsnotification_smsevent'), $setup->getIdxName(
                                $installer->getTable('emipro_smsnotification_smsevent'), ['sms_title', 'sms_events', 'sms_content'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                        ), ['sms_title', 'sms_events', 'sms_content'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }
            $installer->endSetup();
        }
    }

}
