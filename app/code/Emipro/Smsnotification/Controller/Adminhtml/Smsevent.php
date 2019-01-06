<?php

/**
 * {{Emipro}}_{{Smsnotification}} extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Emipro
 *                     @package   Emipro_Smsnotification
 *                     @copyright Copyright (c) 2015
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */

namespace Emipro\Smsnotification\Controller\Adminhtml;

abstract class Smsevent extends \Magento\Backend\App\Action {

    /**
     * SMS Event Factory
     * 
     * @var \Emipro\Smsnotification\Model\SmseventFactory
     */
    protected $_smseventFactory;

    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Result redirect factory
     * 
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * constructor
     * 
     * @param \Emipro\Smsnotification\Model\SmseventFactory $smseventFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Emipro\Smsnotification\Model\SmseventFactory $smseventFactory, 
        \Magento\Framework\Registry $coreRegistry, 
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_smseventFactory = $smseventFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($context);
    }

    /**
     * Init SMS Event
     *
     * @return \Emipro\Smsnotification\Model\Smsevent
     */
    protected function _initSmsevent() {
        $smseventId = (int) $this->getRequest()->getParam('smsevent_id');
        $store = (int) $this->getRequest()->getParam('store');
        if (!empty($store) && $store != 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeevent = $objectManager->create('Emipro\Smsnotification\Model\ResourceModel\Smsstore\Collection');
            $storeevent->addFieldToFilter("main_entity_id", $smseventId);
            $storeevent->addFieldToFilter("store_id", $store);
            $storeevent->addFieldToFilter("use_default", 0);
            if ($storeevent->getSize() == 0) {
                $smsevent = $this->loadDefaultstore($smseventId);
                return $smsevent;
            } else {
                $this->_coreRegistry->register('emipro_smsnotification_smsevent', $storeevent->getFirstItem());
                return $storeevent->getFirstItem();
            }
        } else {
            /** @var \Emipro\Smsnotification\Model\Smsevent $smsevent */
            $smsevent = $this->loadDefaultstore($smseventId);
            return $smsevent;
        }
    }

    protected function loadDefaultstore($smseventId) {
        $smsevent = $this->_smseventFactory->create();
        if ($smseventId) {
            $smsevent->load($smseventId);
        }
        $this->_coreRegistry->register('emipro_smsnotification_smsevent', $smsevent);
        return $smsevent;
    }

}
