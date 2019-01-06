<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model;

use Magento\Rule\Model\AbstractModel;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Quote\Model\Quote\Address;

abstract class AbstractFee extends AbstractModel implements FeeInterface, IdentityInterface
{
    const RESOURCE_MODEL_CLASS = 'MageWorx\MultiFees\Model\ResourceModel\FeeAbstractResource';

    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageworx_multifees_abstract_fee';

    /**
     * @var \MageWorx\Multifees\Model\ResourceModel\Option\CollectionFactory
     */
    protected $feeOptionCollectionFactory;

    /**
     * @var \MageWorx\MultiFees\Model\Fee\Condition\CombineFactory
     */
    protected $condCombineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $condProdCombineFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $helperFee;

    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $_validatedAddresses = [];

    protected function _construct()
    {
        parent::_construct();
        $this->_init(static::RESOURCE_MODEL_CLASS);
        $this->setIdFieldName('fee_id');
    }

    /**
     * Retrieve model resource
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\MageWorx\MultiFees\Model\ResourceModel\FeeAbstractResource
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \MageWorx\MultiFees\Model\Fee\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        $factory = $this->condProdCombineFactory;
        $result  = $factory->create();

        return $result;
    }

    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);

        return isset($this->_validatedAddresses[$addressId]) ? true : false;
    }

    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId                             = $this->_getAddressId($address);
        $this->_validatedAddresses[$addressId] = $validationResult;

        return $this;
    }

    /**
     * Get cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);

        return isset($this->_validatedAddresses[$addressId]) ? $this->_validatedAddresses[$addressId] : false;
    }

    /**
     * Return id for address
     *
     * @param Address $address
     * @return string
     */
    protected function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }

        return $address;
    }

    /**
     * @return bool
     */
    public function isCartFee()
    {
        return $this->getType() == static::CART_TYPE;
    }

    /**
     * @return bool
     */
    public function isPaymentFee()
    {
        return $this->getType() == static::PAYMENT_TYPE;
    }

    /**
     * @return bool
     */
    public function isShippingFee()
    {
        return $this->getType() == static::SHIPPING_TYPE;
    }

    /**
     * Get options for the fee
     *
     * @return \MageWorx\MultiFees\Model\Option[]|\Magento\Framework\DataObject[]
     */
    public function getOptions()
    {
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Option\Collection $collection */
        $collection = $this->feeOptionCollectionFactory->create()
                                                       ->addFeeFilter($this->getId())
                                                       ->addStoreLanguage($this->getStoreId())
                                                       ->sortByPosition(
                                                           \Magento\Framework\Data\Collection::SORT_ORDER_ASC
                                                       )
                                                       ->load();

        return $collection->getItems();
    }

    /**
     * Check is fee valid for the address
     *
     * @param $address
     * @return bool
     */
    public function canProcessFee($address)
    {
        return $this->isValidForTheAddress($address);
    }

    /**
     * Check is fee valid for the address
     *
     * @param $address
     * @return bool
     */
    public function isValidForTheAddress($address)
    {
        if ($this->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $this->getIsValidForAddress($address);
        }

        if (!$this->validate($address)) {
            $this->setIsValidForAddress($address, false);

            return false;
        }

        $this->setIsValidForAddress($address, true);

        return true;
    }

    /**
     *
     * @param \MageWorx\MultiFees\Model\ResourceModel\Option\Collection $collection
     * @param int $addressId
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _setCheckedFeeOption($collection, $addressId = 0)
    {
        $detailsFees = $this->helperFee->getQuoteDetailsMultifees($addressId);

        $items = $collection->getItems();
        if ($items) {
            foreach ($items as $item) {
                if (isset($detailsFees[$this->getFeeId()]['options'][$item->getId()])) {
                    $item->setIsDefault(1);
                } else {
                    $item->setIsDefault(0);
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->getInputType() == FeeInterface::FEE_INPUT_TYPE_HIDDEN) {
            $this->setRequired(true);
        }

        return $this;
    }

    /**
     * Get type
     *
     * @return int|null
     */
    public function getType()
    {
        return $this->getData(static::TYPE);
    }

    /**
     * Get input type
     *
     * @return string|null
     */
    public function getInputType()
    {
        return $this->getData(static::INPUT_TYPE);
    }

    /**
     * Get is onetime
     *
     * @return bool|null
     */
    public function getIsOnetime()
    {
        return $this->getData(static::IS_ONETIME);
    }

    /**
     * Get is required
     *
     * @return bool|null
     */
    public function getRequired()
    {
        return $this->getData(static::REQUIRED);
    }

    /**
     * Get applied totals
     *
     * @return string|null
     */
    public function getAppliedTotals()
    {
        return $this->getData(static::APPLIED_TOTALS);
    }

    /**
     * Get tax class id
     *
     * @return int|null
     */
    public function getTaxClassId()
    {
        return $this->getData(static::TAX_CLASS_ID);
    }

    /**
     * Get serialized conditions
     *
     * @return string|null
     */
    public function getConditionsSerialized()
    {
        return $this->getData(static::CONDITIONS_SERIALIZED);
    }

    /**
     * Get is enable customer message
     *
     * @return int|null
     */
    public function getEnableCustomerMessage()
    {
        return $this->getData(static::ENABLE_CUSTOMER_MESSAGE);
    }

    /**
     * Get is enable date field
     *
     * @return int|null
     */
    public function getEnableDateField()
    {
        return $this->getData(static::ENABLE_DATA_FIELD);
    }

    /**
     * Get total ordered
     *
     * @return int|null
     */
    public function getTotalOrdered()
    {
        return $this->getData(static::TOTAL_ORDERED);
    }

    /**
     * Set total ordered
     *
     * @return double
     */
    public function getTotalBaseAmount()
    {
        return $this->getData(static::TOTAL_BASE_AMOUNT);
    }

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(static::STATUS);
    }

    /**
     * Get fee description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(FeeInterface::DESCRIPTION);
    }

    /**
     * Sets description of the fee
     *
     * @param $text
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setDescription($text)
    {
        return $this->setData(FeeInterface::DESCRIPTION, $text);
    }

    /**
     * Set type
     *
     * @param int $type
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setType($type)
    {
        return $this->setData(static::TYPE, $type);
    }

    /**
     * Set input type
     *
     * @param int $inputType
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setInputType($inputType)
    {
        return $this->setData(static::INPUT_TYPE, $inputType);
    }

    /**
     * Set is onetime
     *
     * @param bool $isOnetime
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setIsOnetime($isOnetime)
    {
        return $this->setData(static::IS_ONETIME, $isOnetime);
    }

    /**
     * Set required
     *
     * @param bool $required
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setRequired($required)
    {
        return $this->setData(static::REQUIRED, $required);
    }

    /**
     * Set applied totals
     *
     * @param string $appliedTotals
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setAppliedTotals($appliedTotals)
    {
        return $this->setData(static::APPLIED_TOTALS, $appliedTotals);
    }

    /**
     * Set tax class ID
     *
     * @param int $taxClassId
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setTaxClassId($taxClassId)
    {
        return $this->setData(static::TAX_CLASS_ID, $taxClassId);
    }

    /**
     * Set serialized conditions
     *
     * @param string $conditionsSerialized
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(static::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * Set is enable customer message
     *
     * @param int $isEnableCustomerMessage
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setEnableCustomerMessage($isEnableCustomerMessage)
    {
        return $this->setData(static::ENABLE_CUSTOMER_MESSAGE, $isEnableCustomerMessage);
    }

    /**
     * Set enable date field
     *
     * @param int $enableDateField
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setEnableDateField($enableDateField)
    {
        return $this->setData(static::ENABLE_DATA_FIELD, $enableDateField);
    }

    /**
     * Set total ordered
     *
     * @param int $totalOrdered
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setTotalOrdered($totalOrdered)
    {
        return $this->setData(static::TOTAL_ORDERED, $totalOrdered);
    }

    /**
     * Set total ordered
     *
     * @param int|double $totalBaseAmount
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setTotalBaseAmount($totalBaseAmount)
    {
        return $this->setData(static::TOTAL_BASE_AMOUNT, $totalBaseAmount);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(static::SORT_ORDER, $sortOrder);
    }

    /**
     * Set status
     *
     * @param int|bool $status
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setStatus($status)
    {
        return $this->setData(static::STATUS, $status);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [static::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Validate rule conditions to determine if rule can run
     *
     * @param \Magento\Framework\DataObject|AbstractFee $object
     * @return bool
     */
    public function validate(\Magento\Framework\DataObject $object)
    {
        return parent::validate($object);
    }


    /**
     * Returns fee title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(FeeInterface::TITLE);
    }

    /**
     * Sets title for the fee
     *
     * @param string $title
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setTitle($title)
    {
        return $this->setData(FeeInterface::TITLE, $title);
    }

    /**
     * Get corresponding store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(FeeInterface::STORE_ID);
    }

    /**
     * Set corresponding store id
     *
     * @param int $id
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setStoreId($id)
    {
        return $this->setData(FeeInterface::STORE_ID, $id);
    }
}
