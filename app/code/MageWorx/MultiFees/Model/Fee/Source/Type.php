<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee\Source;

use MageWorx\MultiFees\Model\AbstractFee;

class Type extends \MageWorx\MultiFees\Model\Source
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => AbstractFee::CART_TYPE, 'label' => __('Cart Fee')],
            ['value' => AbstractFee::PAYMENT_TYPE, 'label' => __('Payment Fee')],
            ['value' => AbstractFee::SHIPPING_TYPE, 'label' => __('Shipping Fee')],
        ];
    }
}
