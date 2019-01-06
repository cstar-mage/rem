<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Order\View;

class Info extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    protected $helperAdmin;

    /**
     * Info constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \Magento\Sales\Helper\Admin $helperAdmin,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->helperData   = $helperData;
        $this->helperAdmin  = $helperAdmin;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('sales_order');
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    /**
     * @return array
     */
    public function getFeeDetails()
    {
        $fees = [];

        $fullInfo = $this->getOrder()->getMageworxFeeDetails();
        if ($fullInfo) {
            $fullInfo = unserialize($fullInfo);

            foreach ($fullInfo as $feeId => $fee) {
                if (isset($fee['type'])) {
                    switch ($fee['type']) {
                        case \MageWorx\MultiFees\Model\AbstractFee::CART_TYPE:
                            $fees[\MageWorx\MultiFees\Model\AbstractFee::CART_TYPE][$feeId] = $fee;
                            break;
                        case \MageWorx\MultiFees\Model\AbstractFee::SHIPPING_TYPE:
                            $fees[\MageWorx\MultiFees\Model\AbstractFee::SHIPPING_TYPE][$feeId] = $fee;
                            break;
                        case \MageWorx\MultiFees\Model\AbstractFee::PAYMENT_TYPE:
                            $fees[\MageWorx\MultiFees\Model\AbstractFee::PAYMENT_TYPE][$feeId] = $fee;
                            break;
                    }
                }
            }
        }

        return $fees;
    }

    /**
     * @return string
     */
    public function getTaxInSales()
    {
        return $this->helperData->getTaxInSales();
    }

    /**
     * @return \Magento\Sales\Helper\Admin
     */
    public function getHelperAdmin()
    {
        return $this->helperAdmin;
    }

    /**
     * @param array $option
     * @return string
     */
    public function getOptionPriceHtml($option)
    {
        $string = '';
        if (isset($option['percent'])) {
            $string .= (float)$option['percent'] . '%';
        }

        $string .= ' - ';

        if ($this->getTaxInSales() == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX) {
            $price     = $option['price'];
            $basePrice = $option['base_price'];
        } else {
            $price     = $option['price'] - $option['tax'];
            $basePrice = $option['base_price'] - $option['base_tax'];
        }

        $string .= $this->helperAdmin->displayPrices($this->getSource(), $basePrice, $price);

        if ($this->getTaxInSales() == \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH) {
            $tax    = $this->helperAdmin->displayPrices(
                $this->getSource(),
                $option['base_price'],
                $option['price']
            );
            $string .= '(' . __('Incl. Tax ') . $tax . ')';
        };

        return $string;
    }

    /**
     * @param array $fee
     * @return string
     */
    public function getFeeDateHtml($fee)
    {
        $string = '';
        if (isset($fee['date']) && $fee['date']) {
            $string .= $this->escapeHtml(trim($fee['date_title'], ':')) . ': <i>';
            $string .= $this->escapeHtml($fee['date']) . '</i><br/>';
        }

        return $string;
    }

    /**
     * @param array $fee
     * @return string
     */
    public function getFeeMessageHtml($fee)
    {
        $string = '';
        if (isset($fee['message']) && $fee['message']) {
            $string .= $this->escapeHtml(trim($fee['message_title'], ':'));
            $string .= ': <i>' . $this->escapeHtml($fee['message']) . '</i>';
        }

        return $string;
    }
}
