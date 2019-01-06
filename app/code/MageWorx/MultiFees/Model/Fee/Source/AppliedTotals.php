<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee\Source;

class AppliedTotals extends \MageWorx\MultiFees\Model\Source
{
    public function toOptionArray()
    {
        return [
            ['value' => 'subtotal', 'label' => __('Subtotal with Discount')],
            ['value' => 'shipping', 'label' => __('Shipping & Handling')],
            ['value' => 'tax', 'label' => __('Tax')],
        ];
    }
}
