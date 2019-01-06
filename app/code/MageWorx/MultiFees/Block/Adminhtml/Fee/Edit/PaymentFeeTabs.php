<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit;

class PaymentFeeTabs extends Tabs
{
    /**
     * @return \MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\PaymentFeeTabs|\Magento\Backend\Block\Widget\Tabs
     * @throws \Exception
     */
    protected function _beforeToHtml()
    {
        $this->addTabAfter(
            'payment_methods',
            [
                'label'   => __('Payment Methods'),
                'title'   => __('Payment Methods'),
                'content' => $this->getChildHtml('payment_methods')
            ],
            'main_options'
        );

        return parent::_beforeToHtml();
    }
}
