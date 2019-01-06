<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\FeeFormInput;

class Radio extends AbstractInput
{
    /**
     * Render form input component for the fee
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render()
    {
        $isApplyOnClick                  = $this->helper->isApplyOnClick();
        $scope                           = $this->scope;
        $component                       = [];
        $component['component']          = 'MageWorx_MultiFees/js/form/element/checkbox-set';
        $component['config']             = [
            'customScope' => $scope,
            'template'    => 'MageWorx_MultiFees/form/field',
            'elementTmpl' => 'MageWorx_MultiFees/form/element/checkbox-set'
        ];
        $component['dataScope']          = $scope . '.fee[' . $this->fee->getId() . '][options][]';
        $component['label']              = $this->fee->getTitle();
        $component['provider']           = 'checkoutProvider';
        $component['visible']            = true;
        $component['validation']         = [];
        $component['applyOnClick']       = $isApplyOnClick;
        $component['isVisibleInputType'] = static::VISIBLE_TYPE;

        $selectedOption = null;

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
                $selectedOption = $option->getId();
            }

            $optionTitle = $option->getTitle() . ' - '
                . $this->helperPrice->getOptionFormatPrice($option, $this->fee);
            $options[]   =
                [
                    'label' => $optionTitle,
                    'value' => $option->getId(),
                ];

            if ($option->getIsDefault()) {
                $defaultOption = $option;
            }
        }

        if (empty($selectedOption)) {
            if (isset($defaultOption)) {
                $selectedOption = $defaultOption->getId();
            } else {
                $selectedOption = !empty($options[0]['value']) ? $options[0]['value'] : null;
            }
        }

        $component['notice']    = $this->fee->getDescription();
        $component['options']   = $options;
        $component['sortOrder'] = $this->fee->getSortOrder();
        $component['value']     = $selectedOption;
        $component['feeType']   = $this->fee->getType();

        return $component;
    }
}
