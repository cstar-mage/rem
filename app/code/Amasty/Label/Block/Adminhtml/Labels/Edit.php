<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Label\Block\Adminhtml\Labels;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_labels';
        $this->_blockGroup = 'Amasty_Label';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );

        $this->addButton(
            'update_button',
            [
                'label' => __('Duplicate'),
                'class' => 'save',
                'on_click' => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'UpdateEdit', 'target' => '#edit_form'],
                    ],
                ]
            ]
        );

        $this->addButton(
            'reindex_button',
            [
                'label' => __('Re-index Current Label'),
                'class' => 'save',
                'on_click' => 'setLocation(\'' . $this->getReindexUrl() . '\')',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'UpdateEdit', 'target' => '#edit_form'],
                    ],
                ]
            ]
        );
    }

    public function getDuplicateUrl()
    {
        return $this->getUrl(
            'amasty_label/labels/duplicate',
            ['_current' => true, 'back' => null, 'product_tab' => $this->getRequest()->getParam('product_tab')]
        );
    }

    public function getReindexUrl()
    {
        return $this->getUrl(
            'amasty_label/labels/reindex',
            ['_current' => true, 'back' => null, 'product_tab' => $this->getRequest()->getParam('product_tab')]
        );
    }
}
