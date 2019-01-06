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

        $table = $installer->getConnection()
                ->newTable($installer->getTable('sms_event_entity'))
                ->addColumn(
                        'entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Entity ID'
                )
                ->addColumn(
                        'attribute_set_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Attribute Set ID'
                )
                ->addColumn(
                        'type_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 32, ['nullable' => false, 'default' => 'simple'], 'Type ID'
                )
                ->addColumn(
                        'Smsevent', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'Sms Event'
                )
                ->addColumn(
                        'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Creation Time'
                )
                ->addColumn(
                        'updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE], 'Update Time'
                )
                ->addIndex(
                        $installer->getIdxName('sms_event_entity', ['attribute_set_id']), ['attribute_set_id']
                )
                ->addForeignKey(
                        $installer->getFkName(
                                'sms_event_entity', 'attribute_set_id', 'eav_attribute_set', 'attribute_set_id'
                        ), 'attribute_set_id', $installer->getTable('eav_attribute_set'), 'attribute_set_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('SMS event table');
        $installer->getConnection()->createTable($table);

        $smsnotificationEav = array();
        $smsnotificationEav['int'] = array(
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            'length' => null,
            'comment' => 'SMS Notification Datetime Attribute Backend Table'
        );

        $smsnotificationEav['varchar'] = array(
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'SMS Notification Varchar Attribute Backend Table'
        );

        $smsnotificationEav['text'] = array(
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => '64k',
            'comment' => 'SMS Notification Text Attribute Backend Table'
        );

        $smsnotificationEav['datetime'] = array(
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            'length' => null,
            'comment' => 'SMS Notification Datetime Attribute Backend Table'
        );

        $smsnotificationEav['decimal'] = array(
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'SMS Notification Datetime Attribute Backend Table'
        );


        foreach ($smsnotificationEav as $type => $options) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('sms_event_entity_' . $type))
                    ->addColumn(
                            'value_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Value ID'
                    )
                    ->addColumn(
                            'type_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Entity Type ID'
                    )
                    ->addColumn(
                            'attribute_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Attribute ID'
                    )
                    ->addColumn(
                            'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Store ID'
                    )
                    ->addColumn(
                            'entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Entity ID'
                    )
                    ->addColumn(
                            'value', $options['type'], null, [], 'Entity ID'
                    )


                    //index part 
                    ->addIndex(
                            $installer->getIdxName(
                                    'sms_event_entity_' . $type, ['entity_id', 'attribute_id', 'store_id'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                            ), ['entity_id', 'attribute_id', 'store_id'], ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->addIndex(
                            $installer->getIdxName('sms_event_entity_' . $type, ['attribute_id']), ['attribute_id']
                    )
                    ->addIndex(
                            $installer->getIdxName('sms_event_entity_' . $type, ['store_id']), ['store_id']
                    )
                    ->addForeignKey(
                            $installer->getFkName('sms_event_entity_' . $type, 'attribute_id', 'eav_attribute', 'attribute_id'), 'attribute_id', $installer->getTable('eav_attribute'), 'attribute_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                            $installer->getFkName('sms_event_entity_' . $type, 'entity_id', 'sms_event_entity', 'entity_id'), 'entity_id', $installer->getTable('sms_event_entity'), 'entity_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                            $installer->getFkName('sms_event_entity' . $type, 'store_id', 'store', 'store_id'), 'store_id', $installer->getTable('store'), 'store_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    //end 
                    ->setComment($options['comment']);
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }

}
