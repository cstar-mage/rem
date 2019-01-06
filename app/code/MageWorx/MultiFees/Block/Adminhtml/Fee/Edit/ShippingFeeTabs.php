<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit;

class ShippingFeeTabs extends Tabs
{
    /**
     * @return \MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\ShippingFeeTabs|\Magento\Backend\Block\Widget\Tabs
     * @throws \Exception
     */
    protected function _beforeToHtml()
    {
        $this->addTabAfter(
            'shipping_methods_tab',
            [
                'label'   => __('Shipping Methods'),
                'title'   => __('Shipping Methods'),
                'content' => $this->getChildHtml('shipping_methods_tab')
            ],
            'main_options'
        );

        return parent::_beforeToHtml();
    }
}
