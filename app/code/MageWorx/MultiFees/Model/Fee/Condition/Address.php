<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee\Condition;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Exception\ValidationException;

/**
 * Class Address
 *
 * @method string getAttribute()
 * @method array getAttributeOption()
 * @method Address setInputType($string)
 * @method Address setOperator($string)
 * @method Address setValue($string)
 * @method Address setAttributeOption($array)
 */
class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    /**
     * Main attributes, common for all entities, which should be set in all conditions
     *
     * @var array
     */
    protected $attributes = [
        'base_subtotal'                => 'Subtotal',
        'total_qty'                    => 'Total Items Quantity',
        'weight'                       => 'Total Weight',
        'base_subtotal_after_discount' => 'Base Subtotal with Discount'
    ];

    /**
     * Additional attributes, specific for each entity, which should be set in that condition only
     *
     * @var array
     */
    protected $attributesAdditional = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Address constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        array $data = []
    ) {
        $this->logger = $context->getLogger();
        parent::__construct(
            $context,
            $directoryCountry,
            $directoryAllregion,
            $shippingAllmethods,
            $paymentAllmethods,
            $data
        );
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = $this->getAttributes();
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get all available attributes for validation
     *
     * @return array
     */
    protected function getAttributes()
    {
        $attributes = array_merge($this->attributes, $this->attributesAdditional);

        foreach ($attributes as $attribute => $label) {
            $attributes[$attribute] = __($label);
        }

        return $attributes;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal_after_discount':
                return 'numeric';
        }

        return parent::getInputType();
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        try {
            /** @var \Magento\Quote\Model\Quote\Address $address */
            $address = $this->resolveAddressEntity($model);

            $this->modifyBeforeLoadAddress($address, $model);

            if (!$address->hasData($this->getAttribute())) {
                $address->getResource()->load($address, $address->getId());
            }

            $this->modifyAfterLoadAddress($address, $model);

            return parent::validate($address);
        } catch (ValidationException $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return \Magento\Framework\Model\AbstractModel
     * @throws ValidationException
     */
    protected function resolveAddressEntity(\Magento\Framework\Model\AbstractModel $model)
    {
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            $this->validateModelEntity($model);
            if ($model->getQuote()->isVirtual()) {
                $address = $model->getQuote()->getBillingAddress();
            } else {
                $address = $model->getQuote()->getShippingAddress();
            }
        }

        return $address;
    }

    /**
     * @param $model
     * @throws ValidationException
     */
    protected function validateModelEntity($model)
    {
        if (!$model->getQuote()) {
            $e = new ValidationException(__('Model %1 should have a quote', get_class($model)));
            $e->setIsValidResult(false);
            throw $e;
        }

        if (!$model->getQuote() instanceof \Magento\Quote\Model\Quote) {
            $e = new ValidationException(
                __(
                    'Quote in the model %1 should be instance of \Magento\Quote\Model\Quote . %2 instance got.',
                    get_class($model),
                    get_class($model->getQuote())
                )
            );
            $e->setIsValidResult(false);
            throw $e;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function modifyBeforeLoadAddress(
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        if ('base_subtotal_after_discount' == $this->getAttribute() && !$address->hasData($this->getAttribute())) {
            $baseSubtotalAfterDiscount = $this->calculateBaseSubtotalAfterDiscount($address);
            $address->setData('base_subtotal_after_discount', $baseSubtotalAfterDiscount);
        }

        return $address;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function modifyAfterLoadAddress(
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        if ('total_qty' == $this->getAttribute()) {
            $qty = $model->getItemQty() ? $model->getItemQty() : $address->getQuote()->getItemsQty();
            $address->setTotalQty($qty);
        }

        return $address;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return float
     */
    protected function calculateBaseSubtotalAfterDiscount(\Magento\Quote\Model\Quote\Address $address)
    {
        $baseSubtotalAfterDiscount = $address->getBaseSubtotalWithDiscount();

        return $baseSubtotalAfterDiscount;
    }
}
