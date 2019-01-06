<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Order;

use Magento\Sales\Model\Order\Address;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;

/**
 * Invoice view  comments form
 *
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'order/info.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var AddressRenderer
     */
    protected $addressRenderer;

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
     * @param TemplateContext $context
     * @param Registry $registry
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \Magento\Sales\Helper\Admin $helperAdmin
     * @param PaymentHelper $paymentHelper
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \Magento\Sales\Helper\Admin $helperAdmin,
        PaymentHelper $paymentHelper,
        array $data = []
    ) {
        $this->paymentHelper   = $paymentHelper;
        $this->helperData      = $helperData;
        $this->helperAdmin     = $helperAdmin;
        $this->coreRegistry    = $registry;
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getOrder()->getRealOrderId()));
        $infoBlock = $this->paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout());
        $this->setChild('payment_info', $infoBlock);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
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
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getOrder();
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
            $string .= __(
                '(Incl. Tax %1)',
                $this->helperAdmin->displayPrices(
                    $this->getSource(),
                    $option['base_price'],
                    $option['price']
                )
            );
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
