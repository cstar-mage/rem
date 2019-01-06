<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Model\Source;

class Configurable implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '0',
                'label' =>__('Parent Configurable Product Image')
            ],
            [
                'value' => '1',
                'label' =>__('Child Simple Product Image')
            ]
        ];

        return $options;
    }
}
