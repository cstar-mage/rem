<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Api\Data;

/**
 * Fee interface
 *
 * @api
 */
interface FeeInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const FEE_ID                  = 'fee_id';
    const TYPE                    = 'type';
    const INPUT_TYPE              = 'input_type';
    const IS_ONETIME              = 'is_onetime';
    const REQUIRED                = 'required';
    const SALES_METHODS           = 'sales_methods'; // Removed 2.0.3
    const APPLIED_TOTALS          = 'applied_totals';
    const TAX_CLASS_ID            = 'tax_class_id';
    const CONDITIONS_SERIALIZED   = 'conditions_serialized';
    const ENABLE_CUSTOMER_MESSAGE = 'enable_customer_message';
    const ENABLE_DATA_FIELD       = 'enable_date_field';
    const TOTAL_ORDERED           = 'total_ordered';
    const TOTAL_BASE_AMOUNT       = 'total_base_amount';
    const SORT_ORDER              = 'sort_order';
    const STATUS                  = 'status';
    const SHIPPING_METHODS        = 'shipping_methods'; // Removed 2.0.3
    const PAYMENT_METHODS         = 'payment_methods'; // Removed 2.0.3
    const STORE_ID                = 'store_id';
    const TITLE                   = 'title';
    const DESCRIPTION             = 'description';
    /**#@-*/

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;

    const CART_TYPE     = 1;
    const PAYMENT_TYPE  = 2;
    const SHIPPING_TYPE = 3;

    const PERCENT_ACTION = 'percent';
    const FIXED_ACTION   = 'fixed';
    const CART_FIXED     = 'cart_fixed';

    const FEE_INPUT_TYPE_DROP_DOWN = 'drop_down';
    const FEE_INPUT_TYPE_RADIO     = 'radio';
    const FEE_INPUT_TYPE_CHECKBOX  = 'checkbox';
    const FEE_INPUT_TYPE_HIDDEN    = 'hidden';
    const FEE_INPUT_TYPE_NOTICE    = 'notice';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get type
     *
     * @return int|null
     */
    public function getType();

    /**
     * Get input type
     *
     * @return string|null
     */
    public function getInputType();

    /**
     * Get is onetime
     *
     * @return bool|null
     */
    public function getIsOnetime();

    /**
     * Get is required
     *
     * @return bool|null
     */
    public function getRequired();

    /**
     * Get applied totals
     *
     * @return string|null
     */
    public function getAppliedTotals();

    /**
     * Get tax class id
     *
     * @return int|null
     */
    public function getTaxClassId();

    /**
     * Get serialized conditions
     *
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * Get is enable customer message
     *
     * @return int|null
     */
    public function getEnableCustomerMessage();

    /**
     * Get is enable date field
     *
     * @return int|null
     */
    public function getEnableDateField();

    /**
     * Get total ordered (statistics)
     *
     * @return int|null
     */
    public function getTotalOrdered();

    /**
     * Get total base amount (statistics)
     *
     * @return double
     */
    public function getTotalBaseAmount();

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Returns fee title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get corresponding store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Get fee description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get options for the fee
     *
     * @return \MageWorx\MultiFees\Model\Option[]
     */
    public function getOptions();

    /**
     * Sets description of the fee
     *
     * @param $text
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setDescription($text);

    /**
     * Sets title for the fee
     *
     * @param string $title
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setTitle($title);

    /**
     * Set ID
     *
     * @param int $id
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setId($id);

    /**
     * Set type
     *
     * @param int $type
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setType($type);

    /**
     * Set input type
     *
     * @param int $inputType
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setInputType($inputType);

    /**
     * Set is onetime
     *
     * @param bool $isOnetime
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setIsOnetime($isOnetime);

    /**
     * Set required
     *
     * @param bool $required
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setRequired($required);

    /**
     * Set applied totals
     *
     * @param string $appliedTotals
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setAppliedTotals($appliedTotals);

    /**
     * Set tax class ID
     *
     * @param int $taxClassId
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setTaxClassId($taxClassId);

    /**
     * Set serialized conditions
     *
     * @param string $conditionsSerialized
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Set is enable customer message
     *
     * @param int $isEnableCustomerMessage
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setEnableCustomerMessage($isEnableCustomerMessage);

    /**
     * Set enable date field
     *
     * @param int $enableDateField
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setEnableDateField($enableDateField);

    /**
     * Set total ordered (statistics)
     *
     * @param int $totalOrdered
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setTotalOrdered($totalOrdered);

    /**
     * Set total base amount (statistics)
     *
     * @param int $totalBaseAmount
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setTotalBaseAmount($totalBaseAmount);

    /**
     * Set sort order
     *
     * @param string $sortOrder
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set status
     *
     * @param int|bool $status
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setStatus($status);

    /**
     * Set corresponding store id
     *
     * @param int $id
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setStoreId($id);
}
