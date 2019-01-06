<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Order\Create\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\MultiFees\Exception\RefactoringException;

/**
 * Create order fee form
 *
 * @TODO Should be refactored to separate blocks and layouts
 * @see  app/code/MageWorx/MultiFees/view/adminhtml/layout/sales_order_create_index.xml
 * @see  app/code/MageWorx/MultiFees/view/adminhtml/templates/order/create/form/fees.phtml
 */
class Fee extends AbstractForm
{
    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $helperFee;

    /**
     * @var \MageWorx\MultiFees\Helper\Price
     */
    protected $helperPrice;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection
     */
    protected $loadedCartFeeCollection;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection
     */
    protected $loadedShippingFeeCollection;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection
     */
    protected $loadedPaymentFeeCollection;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollectionFactory
     */
    protected $cartFeeCollectionFactory;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollectionFactory
     */
    protected $shippingFeeCollectionFactory;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollectionFactory
     */
    protected $paymentFeeCollectionFactory;

    /**
     * @var array
     */
    protected $storedQuoteDetailsMultifees = [];

    /**
     * Fee constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Fee $helperFee
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \MageWorx\MultiFees\Helper\Price $helperPrice,
        \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollectionFactory $cartFeeCollectionFactory,
        \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollectionFactory $shippingFeeCollectionFactory,
        \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollectionFactory $paymentFeeCollectionFactory,
        array $data = []
    ) {
        $this->helperData  = $helperData;
        $this->helperFee   = $helperFee;
        $this->helperPrice = $helperPrice;

        $this->cartFeeCollectionFactory     = $cartFeeCollectionFactory;
        $this->shippingFeeCollectionFactory = $shippingFeeCollectionFactory;
        $this->paymentFeeCollectionFactory  = $paymentFeeCollectionFactory;

        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $data
        );
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-fee';
    }

    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Additional Fees');
    }

    /**
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @param                                       $attributes
     * @return mixed
     * @throws RefactoringException
     */
    private function addFeeToForm(\MageWorx\MultiFees\Model\AbstractFee $fee, &$attributes)
    {
        $details     = $this->getQuoteDetailsMultifees();
        $feesRequest = $this->getRequest()->getParam('fee');

        /** @var \MageWorx\MultiFees\Model\AbstractFee $fee */
        $feeRequestData = !empty($feesRequest[$fee->getId()]) ? $feesRequest[$fee->getId()] : [];
        $feeOptions     = $fee->getOptions();

        if (!empty($feeOptions)) {
            $attribute                   = [];
            $attribute['attribute_code'] = 'multifees_' . $fee->getId();
            $attribute['name']           = 'fee[' . $fee->getId() . '][options][]';
            $attribute['frontend_input'] = $this->getFrontendInput($fee);
            $attribute['store_label']    = $fee->getTitle();
            $attribute['visible']        = true;
            $attribute['is_required']    = $fee->getRequired();
            $attribute['note']           = $fee->getDescription();

            switch ($fee->getType()) {
                case \MageWorx\MultiFees\Model\AbstractFee::CART_TYPE:
                    $class = 'multifees_field';
                    break;
                case \MageWorx\MultiFees\Model\AbstractFee::SHIPPING_TYPE:
                    $class = 'multifees_field_shipping';
                    break;
                case \MageWorx\MultiFees\Model\AbstractFee::PAYMENT_TYPE:
                    $class = 'multifees_field_payment';
                    break;
                default:
                    $class = 'multifees_field';
            }

            $attribute['frontend_class'] = $fee->getRequired() ? $class . ' required-entry' : $class;

            $selectedOption = null;
            $options        = $this->prepareOptions($fee, $attribute);

            foreach ($feeOptions as $option) {
                if (!empty($details[$fee->getId()]['options'][$option->getId()])) {
                    if (in_array($attribute['frontend_input'], ['select', 'radios'])) {
                        $selectedOption = $option->getId();
                    } else {
                        $selectedOption[] = $option->getId();
                    }
                }
                $options[] = [
                    'value' => $option->getId(),
                    'label' => $option->getTitle() . ' - ' . $this->getOptionFormatPrice($option, $fee)
                ];
            }

            // Overwrite values by requested values
            if (!empty($feeRequestData['options'])) {
                if (in_array($attribute['frontend_input'], ['select', 'radios']) &&
                    is_array($feeRequestData['options'])
                ) {
                    $selectedOption = $feeRequestData['options'][0];
                } else {
                    $selectedOption = $feeRequestData['options'];
                }
            }

            $attribute['options'] = $options;
            $attribute['value']   = is_array($selectedOption) ? implode(',', $selectedOption) : $selectedOption;

            $attributes[$attribute['attribute_code']] = $attribute;

            if ($fee->getEnableDateField()) {
                $attributeDate                                = $this->getAttributeDate($fee, $details);
                $attributes[$attributeDate['attribute_code']] = $attributeDate;
            }

            if ($fee->getEnableCustomerMessage()) {
                $attributeMessage                                = $this->getAttributeMessage($fee, $details);
                $attributes[$attributeMessage['attribute_code']] = $attributeMessage;
            }
        }

        return $attributes;
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws RefactoringException
     */
    protected function prepareForm()
    {
        $attributes = [];

        $cartFeesCollection = $this->getCartFeeCollection();
        foreach ($cartFeesCollection as $cartFee) {
            $this->addFeeToForm($cartFee, $attributes);
        }

        $shippingFeesCollection = $this->getShippingFeeCollection();
        foreach ($shippingFeesCollection as $shippingFee) {
            $this->addFeeToForm($shippingFee, $attributes);
        }

        $paymentFeesCollection = $this->getPaymentFeeCollection();
        foreach ($paymentFeesCollection as $paymentFee) {
            $this->addFeeToForm($paymentFee, $attributes);
        }

        $fieldset = $this->form->addFieldset('fee', []);
        $this->addAttributesToForm($attributes, $fieldset);

        return $this;
    }


    /**
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @param array $attribute
     * @return array
     */
    protected function prepareOptions($fee, $attribute)
    {
        $options = [];

        if (!$fee->getRequired() && in_array($attribute['frontend_input'], ['select', 'radios'])) {
            $options[] =
                [
                    'label' => __('None'),
                    'value' => 0
                ];
        }

        return $options;
    }

    /**
     * Add additional data to form element
     *
     * @param AbstractElement $element
     * @return $this
     */
    protected function addAdditionalFormElementData(AbstractElement $element)
    {
        return $this;
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote|\Magento\Checkout\Model\Session
     */
    public function getSession()
    {
        return $this->helperFee->getCurrentSession();
    }

    /**
     * Return multifees details from current quote
     *
     * @return array|null
     */
    public function getQuoteDetailsMultifees()
    {
        if ($this->storedQuoteDetailsMultifees) {
            return $this->storedQuoteDetailsMultifees;
        }

        $this->storedQuoteDetailsMultifees = $this->helperFee->getQuoteDetailsMultifees();

        return $this->storedQuoteDetailsMultifees;
    }

    /**
     * @return void
     * @throws RefactoringException
     */
    public function getMultifees()
    {
        throw new RefactoringException(__('Get MultiFees method called #251628'));
    }

    /**
     * Get collection of the available cart fees
     *
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @TODO: Refactoring candidate: code duplication
     */
    private function getCartFeeCollection()
    {
        if ($this->loadedCartFeeCollection) {
            return $this->loadedCartFeeCollection;
        }

        // Get multifees
        $quote   = $this->helperFee->getQuote();
        $address = $this->helperFee->getSalesAddress($quote);
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection $feeCollection */
        $feeCollection = $this->cartFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $this->helperFee->getCustomerGroupId()
            )
            ->addRequiredFilter(false)
            ->addIsDefaultFilter(false)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        /**
         * @var \MageWorx\MultiFees\Model\CartFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->isValidForTheAddress($address)) {
                $feeCollection->removeItemByKey($key);
            }

            $fee->setStoreId($quote->getStoreId());
        }

        $this->loadedCartFeeCollection = $feeCollection;

        return $this->loadedCartFeeCollection;
    }

    /**
     * Get collection of the available shipping fees
     *
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @TODO: Refactoring candidate: code duplication
     */
    private function getShippingFeeCollection()
    {
        if ($this->loadedShippingFeeCollection) {
            return $this->loadedShippingFeeCollection;
        }

        // Get multifees
        $quote   = $this->helperFee->getQuote();
        $address = $this->helperFee->getSalesAddress($quote);
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection $feeCollection */
        $feeCollection = $this->shippingFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $this->helperFee->getCustomerGroupId()
            )
            ->addRequiredFilter(false)
            ->addIsDefaultFilter(false)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        /**
         * @var \MageWorx\MultiFees\Model\ShippingFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->isValidForTheAddress($address)) {
                $feeCollection->removeItemByKey($key);
            }

            $fee->setStoreId($quote->getStoreId());
        }
        // Get multifees end

        $this->loadedShippingFeeCollection = $feeCollection;

        return $this->loadedShippingFeeCollection;
    }

    /**
     * Get collection of the available payment fees
     *
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @TODO: Refactoring candidate: code duplication
     */
    private function getPaymentFeeCollection()
    {
        if ($this->loadedPaymentFeeCollection) {
            return $this->loadedPaymentFeeCollection;
        }

        // Get multifees
        $quote   = $this->helperFee->getQuote();
        $address = $this->helperFee->getSalesAddress($quote);
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection $feeCollection */
        $feeCollection = $this->paymentFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $this->helperFee->getCustomerGroupId()
            )
            ->addRequiredFilter(false)
            ->addIsDefaultFilter(false)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        /**
         * @var \MageWorx\MultiFees\Model\PaymentFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->isValidForTheAddress($address)) {
                $feeCollection->removeItemByKey($key);
            }

            $fee->setStoreId($quote->getStoreId());
        }
        // Get multifees end

        $this->loadedPaymentFeeCollection = $feeCollection;

        return $this->loadedPaymentFeeCollection;
    }

    /**
     * @param \MageWorx\MultiFees\Model\Option $option
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @return float|int|mixed|string
     */
    public function getOptionFormatPrice($option, $fee)
    {
        return $this->helperPrice->getOptionFormatPrice($option, $fee);
    }

    /**
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @return string
     * @throws RefactoringException
     */
    protected function getFrontendInput($fee)
    {
        switch ($fee->getInputType()) {
            case $fee::FEE_INPUT_TYPE_RADIO:
                $inputType = 'radios';
                break;
            case $fee::FEE_INPUT_TYPE_DROP_DOWN:
                $inputType = 'select';
                break;
            case $fee::FEE_INPUT_TYPE_CHECKBOX:
                // Checkboxes has require validation problem
                // $inputType = 'checkboxes';
                $inputType = 'multiselect';
                break;
            case $fee::FEE_INPUT_TYPE_HIDDEN:
                $inputType = 'hidden';
                break;
            default:
                throw new RefactoringException(__('Unrecognized input type.'));
        }

        return $inputType;
    }

    /**
     * @return bool
     */
    public function getIsEnable()
    {
        if (!$this->helperData->isEnable()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        $rawFormHtml = str_replace(
            'admin__control-radio',
            'admin__control-radio multifees_field',
            $this->getForm()->getHtml()
        );

        return str_replace('admin__control-checkbox', 'admin__control-checkbox multifees_field', $rawFormHtml);
    }

    /**
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @param array $details
     * @return array
     */
    protected function getAttributeMessage($fee, $details)
    {
        $attribute                   = [];
        $attribute['attribute_code'] = 'multifees_' . $fee->getFeeId() . '_message';
        $attribute['name']           = 'fee[' . $fee->getFeeId() . '][message]';
        $attribute['frontend_input'] = 'textarea';

        if ($fee->getCustomerMessageTitle()) {
            $attribute['store_label'] = $fee->getCustomerMessageTitle();
        } else {
            $attribute['store_label'] = __('Message for') . ' "' . $fee->getTitle() . '"';
        }

        $attribute['visible']        = true;
        $attribute['is_required']    = false;
        $attribute['frontend_class'] = 'multifees_field fee_message';
        $attribute['value']          = null;
        if (!empty($details[$fee->getId()]['message'])) {
            $attribute['value'] = $details[$fee->getId()]['message'];
        }

        return $attribute;
    }

    /**
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @param array $details
     * @return array
     */
    protected function getAttributeDate($fee, $details)
    {
        $attribute                   = [];
        $attribute['attribute_code'] = 'multifees_' . $fee->getFeeId() . '_date';
        $attribute['name']           = 'fee[' . $fee->getFeeId() . '][date]';
        $attribute['frontend_input'] = 'date';

        if ($fee->getDateTitle()) {
            $attribute['store_label'] = $fee->getDateTitle();
        } else {
            $attribute['store_label'] = __('Date for') . ' "' . $fee->getTitle() . '"';
        }

        $attribute['visible']        = true;
        $attribute['is_required']    = false;
        $attribute['frontend_class'] = 'multifees_field fee_date';

        $attribute['value'] = null;
        if (!empty($details[$fee->getId()]['date'])) {
            $attribute['value'] = $details[$fee->getId()]['date'];
        }

        return $attribute;
    }
}
