<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container;

class Smslog extends Container {

    protected $_template = 'view.phtml';

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        $this->_controller = 'adminhtml_smslog';
        $this->_blockGroup = 'Emipro_Smsnotification';
        $this->_headerText = __('SMS Log');
        $this->_addButtonLabel = __('Create New SMS Event');
        parent::_construct();
    }

    protected function _prepareLayout() {

        $this->setChild(
                'grid', $this->getLayout()->createBlock('Emipro\Smsnotification\Block\Adminhtml\Smslog\Grid', 'smslog.view.grid')
        );
        return parent::_prepareLayout();
    }

    protected function _getAddButtonOptions() {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }

    protected function _getCreateUrl() {
        return $this->getUrl(
                        'smslog/*/new'
        );
    }
    protected function getGridUrl() {
        return $this->getUrl(
                        'smslog/smslog/new'
        );
    }

    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }

}
