<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Block\Adminhtml\Smslog\Grid\Renderer;

class Orderid extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    public function __construct(\Magento\Backend\Block\Context $context, array $data = []) {
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    public function render(\Magento\Framework\DataObject $row) 
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$order_arr = $objectManager->create("\Magento\Sales\Model\Order")->load($this->_getValue($row));
        $increment_id = $order_arr->getIncrementId();
        
        return '<a href="'.$this->_urlBuilder->getUrl("sales/order/view",array("order_id"=>$this->_getValue($row))).'" target="_blank">' . $increment_id . '</a>';
    }

}
