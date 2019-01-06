<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Total\Quote;

use Magento\Tax\Model\Config as TaxConfig;
use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $mageworxFeeAmount;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

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
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelperData;

    /**
     * @var bool
     */
    protected $isCollected;

    /**
     * @var FeeCollectionManagerInterface
     */
    protected $feeCollectionManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \MageWorx\MultiFees\Api\Data\FeeInterface[]
     */
    protected $possibleFeesItems;

    /**
     * Fee constructor.
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Fee $helperFee
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param \Magento\Tax\Helper\Data $taxHelperData
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \MageWorx\MultiFees\Helper\Price $helperPrice,
        \Magento\Tax\Helper\Data $taxHelperData,
        \MageWorx\MultiFees\Api\FeeCollectionManagerInterface $feeCollectionManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->setCode('mageworx_fee');
        $this->eventManager         = $eventManager;
        $this->storeManager         = $storeManager;
        $this->priceCurrency        = $priceCurrency;
        $this->helperData           = $helperData;
        $this->helperFee            = $helperFee;
        $this->helperPrice          = $helperPrice;
        $this->taxHelperData        = $taxHelperData;
        $this->feeCollectionManager = $feeCollectionManager;
        $this->_logger              = $logger;
    }

    /**
     * Collect address fee amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();

        if ($this->out($address, $shippingAssignment)) {
            return $this;
        }

        // Get data about all fees from the session, in the case of sending the form there are already updated fees
        $feesData = $this->helperFee->getQuoteDetailsMultifees($address->getId());
        $feesData = array_filter($feesData, 'is_array');

        // add default fees
        $missedRequiredFees = $this->checkIsRequiredFeesMissed($feesData, $quote);
        if (is_null($feesData) || !empty($missedRequiredFees)) {
            // Adding the fees automatically if there are missed required fees
            // I think here it is necessary to add only the missing fees and not all in a row!
            $feesData = $this->autoAddFeesByParams($quote, $address, $feesData);
        }

        if (empty($feesData)) {
            return $this;
        }

        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $possibleFees */
        $possibleFees = $this->getAllPossibleFees($quote);
        $store        = $quote->getStore();

        $baseMageworxFeeAmount    = 0;
        $baseMageworxFeeTaxAmount = 0;

        foreach ($feesData as $feeId => $data) {
            if (!isset($data['options'])) {
                unset($feesData[$feeId]); // @protection: fee without options
                continue;
            }

            if (empty($possibleFees[$feeId])) {
                unset($feesData[$feeId]); // @protection: remove invalid fees
                continue;
            }

            $appliedTotals       = is_array($data[\MageWorx\MultiFees\Api\Data\FeeInterface::APPLIED_TOTALS]) ?
                $data[\MageWorx\MultiFees\Api\Data\FeeInterface::APPLIED_TOTALS] :
                explode(',', $data[\MageWorx\MultiFees\Api\Data\FeeInterface::APPLIED_TOTALS]);
            $baseMageworxFeeLeft = $this->helperFee->getBaseFeeLeft(
                $total,
                $appliedTotals
            );

            $taxClassId = $data['tax_class_id'];

            $feePrice = 0;
            $feeTax   = 0;
            foreach ($data['options'] as $optionId => $value) {
                /**
                 * @see \MageWorx\MultiFees\Helper\Price::getOptionFormatPrice();
                 */
                if (isset($value['percent'])) {
                    $opBasePrice = ($baseMageworxFeeLeft * $value['percent']) / 100;
                } else {
                    $opBasePrice = $value['base_price'];
                    if (!$possibleFees[$feeId]->getIsOnetime()) {
                        $opBasePrice = $this->helperPrice->getQtyMultiplicationPrice($opBasePrice);
                    }
                }

                $opPrice = $this->priceCurrency->convert($opBasePrice, $store);

                if ($this->helperData->isTaxCalculationIncludesTax()) {
                    $opTax     = $opPrice - $this->helperPrice->getPriceExcludeTax(
                            $opPrice,
                            $quote,
                            $taxClassId,
                            $address
                        );
                    $opBaseTax = $opBasePrice - $this->helperPrice->getPriceExcludeTax(
                            $opBasePrice,
                            $quote,
                            $taxClassId,
                            $address
                        );
                } else {
                    // add tax
                    $opTax     = $this->helperPrice->getTaxPrice($opPrice, $quote, $taxClassId, $address);
                    $opBaseTax = $this->helperPrice->getTaxPrice($opBasePrice, $quote, $taxClassId, $address);

                    $opPrice     += $opTax;
                    $opBasePrice += $opBaseTax;
                }

                // round price
                extract($this->massRound(compact($opPrice, $opBasePrice, $opTax, $opBaseTax)));

                //$opPrice, $opBasePrice = inclTax
                $feesData[$feeId]['options'][$optionId]['price']      = $opPrice;
                $feesData[$feeId]['options'][$optionId]['base_price'] = $opBasePrice;
                $feesData[$feeId]['options'][$optionId]['tax']        = $opTax;
                $feesData[$feeId]['options'][$optionId]['base_tax']   = $opBaseTax;

                $feeTax   += $opBaseTax;
                $feePrice += $opBasePrice;
            }

            $feesData[$feeId]['base_price'] = $feePrice;
            $feesData[$feeId]['price']      = $this->priceCurrency->convert($feePrice, $store);
            $feesData[$feeId]['base_tax']   = $feeTax;
            $feesData[$feeId]['tax']        = $this->priceCurrency->convert($feeTax, $store);

            $baseMageworxFeeAmount    += $feePrice;
            $baseMageworxFeeTaxAmount += $feeTax;
        }

        $mageworxFeeAmount     = $this->priceCurrency->convertAndRound($baseMageworxFeeAmount, $store);
        $baseMageworxFeeAmount = $this->priceCurrency->round($baseMageworxFeeAmount);

        $mageworxFeeTaxAmount     = $this->priceCurrency->convertAndRound($baseMageworxFeeTaxAmount, $store);
        $baseMageworxFeeTaxAmount = $this->priceCurrency->round($baseMageworxFeeTaxAmount);

        $this->addPricesToAddress($total, $address, $mageworxFeeAmount, $mageworxFeeTaxAmount, $feesData);
        $this->addBasePricesToAddress($total, $address, $baseMageworxFeeAmount, $baseMageworxFeeTaxAmount, $feesData);
        $this->addFeesDetailsToAddress($total, $address, $feesData);

        if ($address->getData('applied_taxes')) {
            // emulation $this->_saveAppliedTaxes($address, $applied, $tax, $baseTax, $rate);
            $appliedTaxes = $address->getAppliedTaxes();
            if (is_array($appliedTaxes)) {
                foreach ($appliedTaxes as $row) {
                    if (isset($appliedTaxes[$row['id']]['amount'])) {
                        $appliedTaxes[$row['id']]['amount']      += $mageworxFeeTaxAmount;
                        $appliedTaxes[$row['id']]['base_amount'] += $baseMageworxFeeTaxAmount;
                        break;
                    }
                }
                $address->setAppliedTaxes($appliedTaxes);
            }
        }

        $this->isCollected = true;

        return $this;
    }

    /**
     * Get all possible fees (not only required)
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAllPossibleFees(\Magento\Quote\Model\Quote $quote)
    {
        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $possibleFeesCollection */
        if ($quote->getIsVirtual()) {
            $requiredCartFees       = $this->feeCollectionManager->getCartFeeCollection()
                                                                 ->getItems();
            $requiredPaymentFees    = $this->feeCollectionManager->getPaymentFeeCollection()
                                                                 ->getItems();
            $possibleFeesCollection = array_merge($requiredCartFees, $requiredPaymentFees);
        } else {
            $requiredCartFees       = $this->feeCollectionManager->getCartFeeCollection()
                                                                 ->getItems();
            $requiredShippingFees   = $this->feeCollectionManager->getShippingFeeCollection()
                                                                 ->getItems();
            $requiredPaymentFees    = $this->feeCollectionManager->getPaymentFeeCollection()
                                                                 ->getItems();
            $possibleFeesCollection = array_merge($requiredCartFees, $requiredShippingFees, $requiredPaymentFees);
        }

        $possibleFees = [];
        foreach ($possibleFeesCollection as $fee) {
            $possibleFees[$fee->getId()] = $fee;
        }

        return $possibleFees;
    }

    /**
     * Get all available required fees from the corresponding collections
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function collectAllRequiredFeesItems(\Magento\Quote\Model\Quote $quote)
    {
        if ($this->possibleFeesItems !== null) {
            return $this->possibleFeesItems;
        }

        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $possibleFeesItems */
        if ($quote->getIsVirtual()) {
            $this->feeCollectionManager->setQuote($quote);
            $requiredCartFees    = $this->feeCollectionManager
                ->getCartFeeCollection(true)
                ->getItems();
            $requiredPaymentFees = $this->feeCollectionManager
                ->getPaymentFeeCollection(true)
                ->getItems();
            $possibleFeesItems   = array_merge($requiredCartFees, $requiredPaymentFees);
        } else {
            $this->feeCollectionManager->setQuote($quote);
            $requiredCartFees     = $this->feeCollectionManager
                ->getCartFeeCollection(true)
                ->getItems();
            $requiredShippingFees = $this->feeCollectionManager
                ->getShippingFeeCollection(true)
                ->getItems();
            $requiredPaymentFees  = $this->feeCollectionManager
                ->getPaymentFeeCollection(true)
                ->getItems();
            $possibleFeesItems    = array_merge($requiredCartFees, $requiredShippingFees, $requiredPaymentFees);
        }

        $possibleFees = [];
        foreach ($possibleFeesItems as $fee) {
            $possibleFees[$fee->getId()] = $fee;
        }

        $this->possibleFeesItems = $possibleFeesItems;

        return $this->possibleFeesItems;
    }

    /**
     * Check is required fees are missed in the current quote
     *
     * @param array $multiFeesInQuote
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkIsRequiredFeesMissed(array $multiFeesInQuote = [], \Magento\Quote\Model\Quote $quote)
    {
        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $possibleFeesItems */
        $possibleFeesItems = $this->collectAllRequiredFeesItems($quote);
        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $missedFeeItems */
        $missedFeeItems = [];
        foreach ($possibleFeesItems as $key => $possibleItem) {
            if (empty($multiFeesInQuote[$possibleItem->getId()])) {
                $missedFeeItems[$key] = $possibleItem;
            }
        }

        return !empty($missedFeeItems);
    }

    /**
     * Retrieve fees and corresponding options array
     *
     * @param null $quote
     * @param null $address
     * @param array $feesPost
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function autoAddFeesByParams($quote = null, $address = null, $feesPost = [])
    {
        if (empty($feesPost)) {
            $feesPost = $this->helperFee->getQuoteDetailsMultifees();
            $feesPost = array_filter($feesPost, 'is_array');
        }

        // @uncompleted
        // @TODO add this feature later, it is cool!
        // As I understood it will add the custom message visible for the customer when a fee was applied.
        $feeMessages = null;

        $this->feeCollectionManager
            ->clean()
            ->setQuote($quote)
            ->setAddress($address);

        $requiredCartFees     = $this->feeCollectionManager->getCartFeeCollection(1, 1)
                                                           ->getItems();
        $requiredShippingFees = $this->feeCollectionManager->getShippingFeeCollection(1, 1)
                                                           ->getItems();
        $requiredPaymentFees  = $this->feeCollectionManager->getPaymentFeeCollection(1, 1)
                                                           ->getItems();

        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $fees */
        $fees = array_merge($requiredCartFees, $requiredShippingFees, $requiredPaymentFees);
        if (!count($fees)) {
            return null;
        }

        foreach ($fees as $fee) {
            // Do not add already existent fee
            if (!empty($feesPost[$fee->getId()])) {
                continue;
            }

            // Do not add fee without options
            $feeOptions = $fee->getOptions();
            if (!$feeOptions) {
                continue;
            }

            // Add default data from the model to the fee data-array, needed later during the totals collecting
            $feesPost[$fee->getId()] = $fee->getData();

            // Process options, find default
            /** @var \MageWorx\MultiFees\Model\Option $option */
            foreach ($feeOptions as $option) {
                if (!$option->getIsDefault()) {
                    continue;
                }

                $opValue['title'] = $option->getTitle();

                if ($option->getPriceType() == 'percent') {
                    $opValue['percent'] = $option->getPrice();
                } else {
                    $opValue['base_price'] = $option->getPrice();
                }
                $feesPost[$fee->getId()]['options'][$option->getId()] = $opValue;

                // @uncompleted
                // @TODO add this feature later, it is cool!
                // As I understood it will add the custom message visible for the customer when a fee was applied.
                if ($feeMessages) {
                    foreach ($feeMessages as $feeMessage) {
                        if ($fee->getId() == $feeMessage->getFeeId()) {
                            $feesPost[$fee->getId()]['message'] = $feeMessage->getMessage();
                        }
                    }
                }
            }
        }

        // Add fees if data is not empty
        if ($feesPost) {
            $this->helperFee->addFeesToQuote($feesPost, $quote->getStoreId(), false);
        }

        return $feesPost;
    }

    /**
     * Add fee total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if (!$total->getMageworxFeeDetails() && !$this->isCollected) {
            $quote->collectTotals();
        }

        if ($total->getMageworxFeeAmount() && $total->getMageworxFeeDetails()) {
            $taxMode = $this->helperData->getTaxInCart();

            if (in_array((int)$taxMode, [TaxConfig::DISPLAY_TYPE_BOTH, TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX])) {
//                if ($taxMode == TaxConfig::DISPLAY_TYPE_BOTH) {
//                    $title = __('Additional Fees (Excl. Tax)');
//                } else {
//                    $title = __('Additional Fees');
//                }

                $applied = $total->getMageworxFeeDetails();

                if (is_string($applied)) {
                    $applied = unserialize($applied);
                }

                $applied = $this->convertFeeDetailsForTax($applied);

                return [
                    'code'      => $this->getCode(),
                    'title'     => __('Additional Fees'),
                    'value'     => $total->getMageworxFeeAmount() - $total->getMageworxFeeTaxAmount(),
                    'full_info' => $applied,
                ];
            }
        }

        return null;
    }

    protected function convertFeeDetailsForTax($applied)
    {
        foreach ($applied as $feeId => $feeData) {
            if (empty($feeData['options'])) {
                continue;
            }

            foreach ($feeData['options'] as $optionId => $optionData) {
                $price     = &$applied[$feeId]['options'][$optionId]['price'];
                $basePrice = &$applied[$feeId]['options'][$optionId]['base_price'];
                $price     = $this->priceCurrency->round($optionData['price'] - $optionData['tax']);
                $basePrice = $this->priceCurrency->round($optionData['base_price'] - $optionData['base_tax']);
            }
        }

        return $applied;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param double $mageworxFeeAmount
     * @param double $mageworxFeeTaxAmount
     * @return $this
     */
    protected function addPricesToAddress($total, $address, $mageworxFeeAmount, $mageworxFeeTaxAmount)
    {
        $total->setMageworxFeeAmount($mageworxFeeAmount);
        $total->setMageworxFeeTaxAmount($mageworxFeeTaxAmount);
        $total->setTotalAmount('mageworx_fee', $mageworxFeeAmount);

        $address->setMageworxFeeAmount($mageworxFeeAmount);
        $address->setMageworxFeeTaxAmount($mageworxFeeTaxAmount);
        $address->setTotalAmount('mageworx_fee', $mageworxFeeAmount);

        $address->setTaxAmount($address->getTaxAmount() + $mageworxFeeTaxAmount);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param double $baseMageworxFeeAmount
     * @param double $baseMageworxFeeTaxAmount
     * @return $this
     */
    protected function addBasePricesToAddress($total, $address, $baseMageworxFeeAmount, $baseMageworxFeeTaxAmount)
    {
        $total->setBaseMageworxFeeAmount($baseMageworxFeeAmount);
        $total->setBaseMageworxFeeTaxAmount($baseMageworxFeeTaxAmount);
        $total->setBaseTotalAmount('mageworx_fee', $baseMageworxFeeAmount);

        $address->setBaseMageworxFeeAmount($baseMageworxFeeAmount);
        $address->setBaseMageworxFeeTaxAmount($baseMageworxFeeTaxAmount);
        $address->setBaseTotalAmount('mageworx_fee', $baseMageworxFeeAmount);

        $address->setBaseTaxAmount($address->getBaseTaxAmount() + $baseMageworxFeeTaxAmount);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param array $feesData
     * @return $this
     */
    protected function addFeesDetailsToAddress($total, $address, $feesData)
    {
        $address->setMageworxFeeDetails(serialize($feesData));
        $total->setMageworxFeeDetails(serialize($feesData));

        return $this;
    }

    /**
     * @param $address
     * @return $this
     */
    protected function resetAddress($address)
    {
        $address->setMageworxFeeAmount(0);
        $address->setBaseMageworxFeeAmount(0);
        $address->setMageworxFeeDetails(null);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return bool
     */
    protected function out($address, $shippingAssignment)
    {
        if (!$this->helperData->isEnable()) {
            return true;
        }

        if ($address->getSubtotal() == 0) {
            return true;
        }

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return true;
        }

        return false;
    }

    /**
     * @param array $values
     * @return array
     */
    protected function massRound(array $values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = $this->priceCurrency->round($value);
        }

        return $values;
    }
}
