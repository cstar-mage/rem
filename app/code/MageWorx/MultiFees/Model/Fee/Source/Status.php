<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee\Source;

use MageWorx\MultiFees\Model\AbstractFee;

class Status extends \MageWorx\MultiFees\Model\Source
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractFee::STATUS_ENABLED,
                'label' => __('Enable')
            ],
            [
                'value' => AbstractFee::STATUS_DISABLED,
                'label' => __('Disable')
            ],
        ];
    }
}
