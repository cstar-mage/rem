<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * @method Tabs setTitle(\string $title)
 */
class Tabs extends WidgetTabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('fee_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Fee Information'));
    }

    /**
     * @return \MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tabs|\Magento\Backend\Block\Widget\Tabs
     * @throws \Exception
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main',
            [
                'label'   => __('Main'),
                'title'   => __('Main'),
                'content' => $this->getChildHtml('main'),
                'active'  => true
            ]
        );
        $this->addTab(
            'main_options',
            [
                'label'   => __('Manage Options'),
                'title'   => __('Properties'),
                'content' => $this->getChildHtml('main_options'),
            ]
        );
        $this->addTab(
            'front',
            [
                'label'   => __('Conditions'),
                'title'   => __('Conditions'),
                'content' => $this->getChildHtml('conditions')
            ]
        );
        $this->addTab(
            'labels',
            [
                'label'   => __('Manage Labels'),
                'title'   => __('Manage Labels'),
                'content' => $this->getChildHtml('labels')
            ]
        );

        return parent::_beforeToHtml();
    }
}
