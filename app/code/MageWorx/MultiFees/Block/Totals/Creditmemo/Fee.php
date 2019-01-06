<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Totals\Creditmemo;

class Fee extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\MultiFees\Helper\Data $helperFee
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\MultiFees\Helper\Data $helperFee,
        array $data = []
    ) {
        $this->helperData = $helperFee;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Add MageWorx Fee Amount to Invoice
     */
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $creditmemo  = $totalsBlock->getCreditmemo();

        if ((float)$creditmemo->getMageworxFeeAmount()) {
            $multifeesTaxAmount     = (float)$creditmemo->getMageworxFeeTaxAmount();
            $multifeesBaseTaxAmount = (float)$creditmemo->getBaseMageworxFeeTaxAmount();

            $isUseTaxInSales = $this->helperData->getTaxInSales();

            $viewMode = [];
            if ($isUseTaxInSales == \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX) {
                $viewMode[] = false;
            } elseif ($isUseTaxInSales == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX) {
                $viewMode[] = true;
            } elseif ($isUseTaxInSales == \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH) {
                $viewMode[] = false;
                $viewMode[] = true;
            }

            foreach ($viewMode as $inclTax) {
                if ($isUseTaxInSales != \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH) {
                    $label = __('Additional Fees');
                } else {
                    if ($inclTax) {
                        $label = __('Additional Fees (Incl. Tax)');
                    } else {
                        $label = __('Additional Fees (Excl. Tax)');
                    }
                }

                $totalsBlock->addTotalBefore(
                    new \Magento\Framework\DataObject(
                        [
                            'code'       => $inclTax ? 'mageworx_fee_incl_tax' : 'mageworx_fee_amount',
                            'label'      => $label,
                            'value'      => $inclTax ? $creditmemo->getMageworxFeeAmount(
                            ) : $creditmemo->getMageworxFeeAmount() - $multifeesTaxAmount,
                            'base_value' => $inclTax ? $creditmemo->getBaseMageworxFeeAmount(
                            ) : $creditmemo->getBaseMageworxFeeAmount() - $multifeesBaseTaxAmount,
                        ]
                    ),
                    'grand_total'
                );
            }
        }
    }
}
