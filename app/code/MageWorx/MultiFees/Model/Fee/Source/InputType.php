<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee\Source;

use MageWorx\MultiFees\Model\AbstractFee;

class InputType extends \MageWorx\MultiFees\Model\Source
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => AbstractFee::FEE_INPUT_TYPE_DROP_DOWN, 'label' => __('Drop down')],
            ['value' => AbstractFee::FEE_INPUT_TYPE_RADIO, 'label' => __('Radio')],
            ['value' => AbstractFee::FEE_INPUT_TYPE_CHECKBOX, 'label' => __('Checkbox')],
            ['value' => AbstractFee::FEE_INPUT_TYPE_HIDDEN, 'label' => __('Hidden')],
            //['value' => AbstractFee::FEE_INPUT_TYPE_NOTICE,    'label' => __('Notice')],
        ];
    }
}
