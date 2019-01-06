<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderAdminObserver implements ObserverInterface
{
    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $feeHelper;

    /**
     * AddFeeToOrder constructor.
     *
     * @param \MageWorx\MultiFees\Helper\Fee $feeHelper
     */
    public function __construct(
        \MageWorx\MultiFees\Helper\Fee $feeHelper
    ) {
        $this->feeHelper = $feeHelper;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // check submit fees when admin/sales_order_create
        $post     = $observer->getEvent()->getRequest();
        $feesPost = isset($post['fee']) ? $post['fee'] : [];
        if (isset($post['fee_type'])) {
            $this->feeHelper->addFeesToQuote(
                $feesPost,
                $this->feeHelper->getQuote()->getStoreId(),
                true,
                0
            );
        }
    }
}
