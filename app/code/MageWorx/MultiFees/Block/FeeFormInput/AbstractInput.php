<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\FeeFormInput;

use MageWorx\MultiFees\Model\AbstractFee;
use MageWorx\MultiFees\Helper\Data as Helper;
use MageWorx\MultiFees\Helper\Price as HelperPrice;

abstract class AbstractInput implements FeeFormInputRenderInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var HelperPrice
     */
    protected $helperPrice;

    /**
     * @var array
     */
    protected $details = [];

    /**
     * @var \MageWorx\MultiFees\Api\Data\FeeInterface|\MageWorx\MultiFees\Model\AbstractFee
     */
    protected $fee;

    /**
     * @var string
     */
    protected $scope;

    /**
     * DropDown constructor.
     *
     * @param Helper $helper
     * @param HelperPrice $helperPrice
     * @param \MageWorx\MultiFees\Api\Data\FeeInterface $fee
     * @param array $details
     */
    public function __construct(
        Helper $helper,
        HelperPrice $helperPrice,
        \MageWorx\MultiFees\Api\Data\FeeInterface $fee,
        array $details = []
    ) {
        $this->helper      = $helper;
        $this->helperPrice = $helperPrice;
        $this->fee         = $fee;
        $this->details     = $details;
        $this->scope       = $this->getScope($fee->getType());
    }

    /**
     * Return scope for fee type
     *
     * @param int type
     * @return string
     */
    protected function getScope($type)
    {
        switch ($type) {
            case AbstractFee::CART_TYPE:
                return 'mageworxFeeForm';
            case AbstractFee::SHIPPING_TYPE:
                return 'mageworxShippingFeeForm';
            case AbstractFee::PAYMENT_TYPE:
                return 'mageworxPaymentFeeForm';
            default:
                return 'mageworxFeeForm';
        }
    }
}
