<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Setup;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\EntityManager\MetadataPool;
use MageWorx\MultiFees\Api\Data\FeeInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * UpgradeData constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     */
    public function __construct(
        MetadataPool $metadataPool,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ProductMetadata $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
        $this->metadataPool    = $metadataPool;
        if ($this->isUsedJsonSerializedValues()) {
            $this->aggregatedFieldConverter = $objectManager->get('Magento\Framework\DB\AggregatedFieldDataConverter');
        }
    }

    /**
     * @return bool
     */
    public function isUsedJsonSerializedValues()
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.0.0', '>=') &&
            class_exists('\Magento\Framework\DB\AggregatedFieldDataConverter')
        ) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->convertFeeSerializedDataToJson($setup);
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $this->updateConditionsSerialized($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function updateConditionsSerialized(ModuleDataSetupInterface $setup)
    {
        $feesTableName                  = $setup->getTable('mageworx_multifees_fee');
        $columnNameConditionsSerialized = 'conditions_serialized';
        $feeTypeColumnName              = 'type';
        $shippingFeeType                = FeeInterface::SHIPPING_TYPE;
        $paymentFeeType                 = FeeInterface::PAYMENT_TYPE;
        if ($this->aggregatedFieldConverter) {
            // Update Condition\Combine in the shipping fee
            $setup->getConnection()->update(
                $feesTableName,
                [
                    $columnNameConditionsSerialized => new \Zend_Db_Expr(
                        sprintf(
                            "REPLACE(%s, '%s', '%s')",
                            $columnNameConditionsSerialized,
                            'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\Combine',
                            'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\ShippingFee\\\\\\\\Combine'
                        )
                    )
                ],
                sprintf(
                    '`%s` = %d',
                    $feeTypeColumnName,
                    $shippingFeeType
                )
            );

            // Update Condition\Address in the shipping fee
            $setup->getConnection()->update(
                $feesTableName,
                [
                    $columnNameConditionsSerialized => new \Zend_Db_Expr(
                        sprintf(
                            "REPLACE(%s, '%s', '%s')",
                            $columnNameConditionsSerialized,
                            'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\Address',
                            'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\ShippingFee\\\\\\\\Address'
                        )
                    )
                ],
                sprintf(
                    '`%s` = %d',
                    $feeTypeColumnName,
                    $shippingFeeType
                )
            );

            // Update Condition\Combine in the payment fee
            $setup->getConnection()->update(
                $feesTableName,
                [
                    $columnNameConditionsSerialized => new \Zend_Db_Expr(
                        sprintf(
                            "REPLACE(%s, '%s', '%s')",
                            $columnNameConditionsSerialized,
                            'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\Combine',
                            'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\PaymentFee\\\\\\\\Combine'
                        )
                    )
                ],
                sprintf(
                    '`%s` = %d',
                    $feeTypeColumnName,
                    $paymentFeeType
                )
            );

            // Update Condition\Address in the payment fee
            $setup->getConnection()->update(
                $feesTableName,
                [
                    $columnNameConditionsSerialized => new \Zend_Db_Expr(
                        sprintf(
                            "REPLACE(%s, '%s', '%s')",
                            $columnNameConditionsSerialized,
                            'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\Address',
                            'MageWorx\\\\\\\\MultiFees\\\\\\\\Model\\\\\\\\Fee\\\\\\\\Condition\\\\\\\\PaymentFee\\\\\\\\Address'
                        )
                    )
                ],
                sprintf(
                    '`%s` = %d',
                    $feeTypeColumnName,
                    $paymentFeeType
                )
            );
        } else {
            $candidateForReplace = [
                [
                    'search'  => 'MageWorx\MultiFees\Model\Fee\Condition\Combine',
                    'replace' => 'MageWorx\MultiFees\Model\Fee\Condition\ShippingFee\Combine',
                    'type'    => $shippingFeeType
                ],
                [
                    'search'  => 'MageWorx\MultiFees\Model\Fee\Condition\Address',
                    'replace' => 'MageWorx\MultiFees\Model\Fee\Condition\ShippingFee\Address',
                    'type'    => $shippingFeeType
                ],
                [
                    'search'  => 'MageWorx\MultiFees\Model\Fee\Condition\Combine',
                    'replace' => 'MageWorx\MultiFees\Model\Fee\Condition\PaymentFee\Combine',
                    'type'    => $paymentFeeType
                ],
                [
                    'search'  => 'MageWorx\MultiFees\Model\Fee\Condition\Address',
                    'replace' => 'MageWorx\MultiFees\Model\Fee\Condition\PaymentFee\Address',
                    'type'    => $paymentFeeType
                ],
            ];

            foreach ($candidateForReplace as $candidate) {
                $search  = 's:' . strlen($candidate['search']) . ':"' . $candidate['search'] . '"';
                $replace = 's:' . strlen($candidate['replace']) . ':"' . $candidate['replace'] . '"';
                $type    = $candidate['type'];

                $setup->getConnection()->update(
                    $feesTableName,
                    [
                        $columnNameConditionsSerialized => new \Zend_Db_Expr(
                            sprintf(
                                "REPLACE(%s, '%s', '%s')",
                                $columnNameConditionsSerialized,
                                $search,
                                $replace
                            )
                        )
                    ],
                    sprintf(
                        '`%s` = %d',
                        $feeTypeColumnName,
                        $type
                    )
                );
            }
        }
    }

    /**
     * Convert Fee metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\DB\FieldDataConversionException
     */
    protected function convertFeeSerializedDataToJson(ModuleDataSetupInterface $setup)
    {
        if ($this->aggregatedFieldConverter) {
            $this->aggregatedFieldConverter->convert(
                [
                    new FieldToConvert(
                        SerializedToJson::class,
                        $setup->getTable('mageworx_multifees_fee'),
                        FeeInterface::FEE_ID,
                        'conditions_serialized'
                    ),
                ],
                $setup->getConnection()
            );
        }
    }
}
