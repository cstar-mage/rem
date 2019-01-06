<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Acart\Block;


use Magento\Checkout\Model\Cart as CustomerCart;


/**
 * Base html block
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GrabEmail extends \Magento\Framework\View\Element\Template
{
    protected $_cart;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerCart $cart,
        array $data = []
    ){
        $this->_cart = $cart;

        parent::__construct($context, $data);
    }

    public function getGrabUrl()
    {
        return $this->_urlBuilder->getUrl('amasty_acart/email/grab');
    }

    public function isAvailable()
    {
        $ret = false;

        if ($this->_cart->getQuote() && !$this->_cart->getCustomerId()){
            $ret = true;
        }

        return $ret;
    }
}