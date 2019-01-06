<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Block\Adminhtml\Smslog\Grid\Renderer;

class Customeremail extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    public function __construct(\Magento\Backend\Block\Context $context, array $data = []) {
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    public function render(\Magento\Framework\DataObject $row) {
        $customer = $this->_getValue($row);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer_object = $objectManager->create('Magento\Customer\Model\Customer')->load($customer);
        return '<a href="'.$this->_urlBuilder->getUrl("customer/index/edit",array("id"=>$customer_object->getId())).'" target="_blank">' . $customer_object->getEmail() . '</a>';
    }

}
