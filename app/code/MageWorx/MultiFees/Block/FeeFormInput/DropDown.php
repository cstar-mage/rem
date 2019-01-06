<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\FeeFormInput;

class DropDown extends AbstractInput
{
    /**
     * Render form input component for the fee
     *
     * @return array
     */
    public function render()
    {
        $isApplyOnClick                  = $this->helper->isApplyOnClick();
        $component                       = [];
        $component['component']          = 'MageWorx_MultiFees/js/form/element/select';
        $component['config']             = [
            'customScope' => $this->scope,
            'template'    => 'MageWorx_MultiFees/form/field',
            'elementTmpl' => 'ui/form/element/select'
        ];
        $component['dataScope']          = $this->scope . '.fee[' . $this->fee->getId() . '][options][]';
        $component['label']              = $this->fee->getTitle();
        $component['provider']           = 'checkoutProvider';
        $component['visible']            = true;
        $component['validation']         = [];
        $component['applyOnClick']       = $isApplyOnClick;
        $component['isVisibleInputType'] = static::VISIBLE_TYPE;

        $options = [];

        if ($this->fee->getRequired()) {
            $component['validation']['required-entry'] = 'true';
        } else {
            $options[] =
                [
                    'label' => __('None'),
                    'value' => 0
                ];
        }

        foreach ($this->fee->getOptions() as $option) {
            if (!empty($this->details[$this->fee->getId()]['options'][$option->getId()])) {
                $component['value'] = $option->getId();
            }

            $optionTitle = $option->getTitle();
            $optionPrice = $this->helperPrice->getOptionFormatPrice($option, $this->fee);
            $optionLabel = $optionTitle . ' - ' . $optionPrice;

            $options[] =
                [
                    'label' => $optionLabel,
                    'value' => $option->getId()
                ];

            if ($option->getIsDefault()) {
                $defaultOption = $option;
            }
        }

        if (empty($component['value'])) {
            if (isset($defaultOption)) {
                $component['value'] = $defaultOption->getId();
            } else {
                $component['value'] = !empty($options[0]['value']) ? $options[0]['value'] : null;
            }
        }

        $component['notice']    = $this->fee->getDescription();
        $component['options']   = $options;
        $component['sortOrder'] = $this->fee->getSortOrder();
        $component['feeType']   = $this->fee->getType();

        return $component;
    }
}
