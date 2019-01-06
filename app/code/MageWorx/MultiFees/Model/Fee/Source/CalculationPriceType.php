<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee\Source;

class CalculationPriceType extends \MageWorx\MultiFees\Model\Source
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $options[] = [
            'value' => \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX,
            'label' => __('Excluding Tax'),
        ];
        $options[] = [
            'value' => \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX,
            'label' => __('Including Tax'),
        ];

        return $options;
    }
}
