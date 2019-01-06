<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Block\Adminhtml\Smslog;

class Edit extends \Magento\Backend\Block\Widget\Form\Container {

    protected $_coreRegistry = null;

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct() {
        $this->_objectId = 'smslog_id';
        $this->_blockGroup = 'Emipro_smsnotification';
        $this->_controller = 'adminhtml_smslog';

        parent::_construct();

    }
}
