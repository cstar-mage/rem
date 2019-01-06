<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $moduleResource;

    /**
     * InstallSchema constructor.
     *
     * @param \Magento\Framework\Module\ResourceInterface $moduleResource
     */
    public function __construct(
        \Magento\Framework\Module\ResourceInterface $moduleResource
    ) {
        $this->moduleResource = $moduleResource;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'mageworx_multifees_fee'
         */
        $tableFee = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_multifees_fee')
        )->addColumn(
            'fee_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Fee ID'
        )->addColumn(
            'type',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 1,
            ],
            '1-Cart Fee,2-Payment Fee,3-Shipping Fee'
        )->addColumn(
            'input_type',
            Table::TYPE_TEXT,
            '64k',
            [
                'nullable' => false,
            ],
            'Input Types'
        )->addColumn(
            'is_onetime',
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => false,
                'default'  => 1
            ],
            'Is Onetime'
        )->addColumn(
            'required',
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => false,
                'default'  => 1
            ],
            'Is Required'
        )->addColumn(
            'sales_methods',
            Table::TYPE_TEXT,
            '64k',
            [
                'nullable' => false,
            ],
            'Sales Methods'
        )->addColumn(
            'applied_totals',
            Table::TYPE_TEXT,
            '255',
            [
                'nullable' => false,
                'default'  => 'subtotal'
            ],
            'Applied Totals'
        )->addColumn(
            'tax_class_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0,
            ],
            'Tax Class ID (0-None)'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '64k',
            [
                //                    'nullable' => false,
                //                    'default'  => 0,
            ],
            'Conditions Serialized'
        )->addColumn(
            'enable_customer_message',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0,
            ],
            'Is Enable Customer Message'
        )->addColumn(
            'enable_date_field',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0,
            ],
            'Is Enable Date Field'
        )->addColumn(
            'total_ordered',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0,
            ],
            'Total Ordered'
        )->addColumn(
            'total_base_amount',
            Table::TYPE_DECIMAL,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => '0.0000',
            ],
            'Total Base Amount'
        )->addColumn(
            'sort_order',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0,
            ],
            'Sort Order'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 1,
            ],
            'Is Active'
        );
        $installer->getConnection()->createTable($tableFee);

        /**
         * Create table 'mageworx_multifees_fee_language'
         */
        $tableFeelanguage = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_multifees_fee_language')
        )->addColumn(
            'fee_lang_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Fee Language ID'
        )->addColumn(
            'fee_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Fee ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
            ],
            'Store ID'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            512,
            [
                'nullable' => false,
            ],
            'Title'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            2048,
            [
                'nullable' => false,
            ],
            'Description'
        )->addColumn(
            'customer_message_title',
            Table::TYPE_TEXT,
            512,
            [
                'nullable' => false,
            ],
            'Customer Message Title'
        )->addColumn(
            'date_field_title',
            Table::TYPE_TEXT,
            512,
            [
                'nullable' => false,
            ],
            'Date Field Title'
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_multifees_fee_language',
                'fee_id',
                'mageworx_multifees_fee',
                'fee_id'
            ),
            'fee_id',
            $installer->getTable('mageworx_multifees_fee'),
            'fee_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_multifees_fee_language',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Fee Language Table'
        );
        $installer->getConnection()->createTable($tableFeelanguage);

        /**
         * Create table 'mageworx_multifees_fee_store'
         */
        $tableFeeStore = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_multifees_fee_store')
        )->addColumn(
            'fee_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Fee ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Store ID'
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_multifees_fee_store',
                'fee_id',
                'mageworx_multifees_fee',
                'fee_id'
            ),
            'fee_id',
            $installer->getTable('mageworx_multifees_fee'),
            'fee_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_multifees_fee_store',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Fee To Store Linkage Table'
        );
        $installer->getConnection()->createTable($tableFeeStore);

        /**
         * Create table 'mageworx_multifees_fee_option'
         */
        $tableFeeOption = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_multifees_fee_option')
        )->addColumn(
            'fee_option_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Fee Option ID'
        )->addColumn(
            'fee_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Fee ID'
        )->addColumn(
            'price',
            Table::TYPE_DECIMAL,
            '12,4',
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => '0.0000',
            ],
            'Price'
        )->addColumn(
            'price_type',
            Table::TYPE_TEXT,
            7,
            [
                'nullable' => false,
                'default'  => 'fixed'
            ],
            'Price Type'
        )->addColumn(
            'is_default',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0
            ],
            'Is Default'
        )->addColumn(
            'position',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
            ],
            'Position'
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_multifees_fee_option',
                'fee_id',
                'mageworx_multifees_fee',
                'fee_id'
            ),
            'fee_id',
            $installer->getTable('mageworx_multifees_fee'),
            'fee_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Fee Option Table'
        );
        $installer->getConnection()->createTable($tableFeeOption);

        /**
         * Create table 'mageworx_multifees_fee_option_language'
         */
        $tableFeeOptionLanguage = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_multifees_fee_option_language')
        )->addColumn(
            'fee_option_lang_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Fee Option Lang ID'
        )->addColumn(
            'fee_option_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Fee Option ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
            ],
            'Store ID'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            512,
            [
                'nullable' => false,
            ],
            'Title'
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_multifees_fee_option_language',
                'fee_option_id',
                'mageworx_multifees_fee_option',
                'fee_option_id'
            ),
            'fee_option_id',
            $installer->getTable('mageworx_multifees_fee_option'),
            'fee_option_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_multifees_fee_option_language',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Fee Option Language Table'
        );
        $installer->getConnection()->createTable($tableFeeOptionLanguage);
        $installer->endSetup();

        /**
         * Create table 'mageworx_multifees_fee_customer_group'
         */
        $tableFeeCustomerGroup = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_multifees_fee_customer_group')
        )->addColumn(
            'fee_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Fee ID'
        )->addColumn(
            'customer_group_id',
            $this->getCustomerGroupColumnType(),
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Customer Group ID'
        )
                                           ->addIndex(
                                               $setup->getIdxName(
                                                   'mageworx_multifees_fee_customer_group',
                                                   ['customer_group_id']
                                               ),
                                               ['customer_group_id']
                                           )
                                           ->addForeignKey(
                                               $setup->getFkName(
                                                   'mageworx_multifees_fee_customer_group',
                                                   'fee_id',
                                                   'mageworx_multifees_fee',
                                                   'fee_id'
                                               ),
                                               'fee_id',
                                               $setup->getTable('mageworx_multifees_fee'),
                                               'fee_id',
                                               Table::ACTION_CASCADE
                                           )
                                           ->addForeignKey(
                                               $setup->getFkName(
                                                   'mageworx_multifees_fee_customer_group',
                                                   'customer_group_id',
                                                   'customer_group',
                                                   'customer_group_id'
                                               ),
                                               'customer_group_id',
                                               $setup->getTable('customer_group'),
                                               'customer_group_id',
                                               Table::ACTION_CASCADE
                                           )
                                           ->setComment('MageWorx Fees To Customer Groups Relations');

        $installer->getConnection()->createTable($tableFeeCustomerGroup);

        $this->extendQuoteAddressTable($installer);
        $this->extendSalesOrderTable($installer);
        $this->extendSalesInvoiceTable($installer);
        $this->extendSalesCreditMemoTable($installer);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param null|string $tableName
     */
    protected function extendTable($installer, $tableName)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable($tableName),
            'mageworx_fee_amount',
            [
                'type'     => Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => false,
                'default'  => '0.0000',
                'comment'  => 'MageWorx Fee Amount'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable($tableName),
            'base_mageworx_fee_amount',
            [
                'type'     => Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => false,
                'default'  => '0.0000',
                'comment'  => 'Base MageWorx Fee Amount'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable($tableName),
            'mageworx_fee_tax_amount',
            [
                'type'     => Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => false,
                'default'  => '0.0000',
                'comment'  => 'Mageworx Fee Tax Amount'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable($tableName),
            'base_mageworx_fee_tax_amount',
            [
                'type'     => Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => false,
                'default'  => '0.0000',
                'comment'  => 'Base MageWorx Fee Tax Amount'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable($tableName),
            'mageworx_fee_details',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'MageWorx Fee Details'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    protected function extendQuoteAddressTable($installer, $tableName = null)
    {
        $this->extendTable($installer, 'quote_address');
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    protected function extendSalesOrderTable($installer)
    {
        $this->extendTable($installer, 'sales_order');

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'mageworx_fee_invoiced',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'MageWorx Fee Invoiced'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'base_mageworx_fee_invoiced',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Base MageWorx Fee Invoiced'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'mageworx_fee_refunded',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'MageWorx Fee Refunded'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'base_mageworx_fee_refunded',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Base MageWorx Fee Refunded'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'mageworx_fee_cancelled',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'MageWorx Fee Canceled'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'base_mageworx_fee_cancelled',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Base MageWorx Fee Canceled'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    protected function extendSalesInvoiceTable($installer)
    {
        $this->extendTable($installer, 'sales_invoice');
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    protected function extendSalesCreditMemoTable($installer)
    {
        $this->extendTable($installer, 'sales_creditmemo');
    }

    /**
     * @return string
     */
    protected function getCustomerGroupColumnType()
    {
        $customerDbVersion = $this->moduleResource->getDbVersion('Magento_Customer');

        if (version_compare($customerDbVersion, '2.0.10', '<')) {
            return Table::TYPE_SMALLINT;
        }

        return Table::TYPE_INTEGER;
    }
}
