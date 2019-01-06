<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 *
 * @event sales_quote_collect_totals_after
 */
class ValidateQuoteTotals implements ObserverInterface
{
    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $helperFee;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \MageWorx\MultiFees\Api\FeeCollectionManagerInterfaceFactory
     */
    protected $collectionManagerInterfaceFactory;

    /**
     * ValidateQuoteTotals constructor.
     *
     * @param $helperFee
     * @param $helperData
     */
    public function __construct(
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \Magento\Framework\App\RequestInterface $request,
        \MageWorx\MultiFees\Api\FeeCollectionManagerInterfaceFactory $collectionManagerInterfaceFactory
    ) {
        $this->helperFee                         = $helperFee;
        $this->helperData                        = $helperData;
        $this->request                           = $request;
        $this->collectionManagerInterfaceFactory = $collectionManagerInterfaceFactory;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // check required fees
        if (!$this->helperData->isEnable()) {
            return $this;
        }
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote    = $observer->getEvent()->getQuote();
        $session  = $this->helperFee->getCurrentSession();
        $feesData = $this->helperFee->getQuoteDetailsMultifees();

        $session->setMultifeesValidationFailed(false);

        /** @var \MageWorx\MultiFees\Api\FeeCollectionManagerInterface $feesManager */
        $feesManager = $this->collectionManagerInterfaceFactory->create();

        // Validate cart fees
        $requiredCartFees = $feesManager->setQuote($quote)->getRequiredCartFees();
        if (count($requiredCartFees)) {
            foreach ($requiredCartFees as $fee) {
                if (!isset($feesData[$fee->getFeeId()])) {
                    $quote->addErrorInfo(
                        'error',
                        'multifees',
                        \MageWorx\MultiFees\Helper\Fee::ERROR_REQUIRED_CART_FEE_MISS,
                        __('%1 cart fee is required', $fee->getTitle())
                    );
                    $session->setMultifeesValidationFailed(true);
                }
            }
        }

        return $this;
    }
}
