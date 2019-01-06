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

namespace Emipro\Smsnotification\Controller\Adminhtml\Smsevent;

class Save extends \Emipro\Smsnotification\Controller\Adminhtml\Smsevent {

    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Emipro\Smsnotification\Model\SmseventFactory $smseventFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Emipro\Smsnotification\Model\SmseventFactory $smseventFactory, 
        \Magento\Framework\Registry $registry, 
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_backendSession = $context->getSession();
        parent::__construct($smseventFactory, $registry, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute() {
        $alldata = $this->getRequest()->getParams();
        $store_id = (int) $this->getRequest()->getParam("store", 0);
        $smsdata = $this->getRequest()->getParam("smsevent");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $resultRedirect = $this->_resultRedirectFactory->create();
        if ($smsdata) {
            if (!empty($store_id) && isset($smsdata["main_entity_id"])) {
                $storeevent = $objectManager->create('Emipro\Smsnotification\Model\Smsstore');
                $storeevent->load($smsdata["smsevent_id"]);
                if ($storeevent->getId()) {
                    if (isset($smsdata["use_default"])) {
                        $storeevent->setUseDefault(1);
                    } else {
                        $storeevent->setUseDefault(0);
                        $storeevent->setSmsContent($smsdata["sms_content"]);
                        $storeevent->setIsActive($smsdata["is_active"]);
                    }
                    $this->_eventManager->dispatch(
                            'emipro_smsnotification_smsevent_prepare_save', [
                        'smsevent' => $storeevent,
                        'request' => $this->getRequest()
                            ]
                    );
                    try {
                        $storeevent->setId($smsdata["smsevent_id"])->save();
                        $this->messageManager->addSuccess(__('The SMS Event has been saved.'));
                        $this->_backendSession->setEmiproSmsnotificationSmseventData(false);
                        if ($this->getRequest()->getParam('back')) {

                            $resultRedirect->setPath(
                                    'emipro_smsnotification/*/edit', [
                                'smsevent_id' => $storeevent->getMainEntityId(),
                                '_current' => true
                                    ]
                            );
                            return $resultRedirect;
                        }
                        $resultRedirect->setPath('emipro_smsnotification/*/');
                        return $resultRedirect;
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addException($e, __('Something went wrong while saving the SMS Event.'));
                    }
                }
            } else {
                if (!isset($smsdata["use_default"]) && $store_id != 0) {
                    $storeevent = $objectManager->create('Emipro\Smsnotification\Model\Smsstore');
                    $storeevent->setUseDefault(0);
                    $storeevent->setMainEntityId($smsdata["smsevent_id"]);
                    $storeevent->setSmsTitle($smsdata["sms_title_hidden"]);
                    $storeevent->setSmsEvents($smsdata["sms_events"]);
                    $storeevent->setStoreId($store_id);
                    $storeevent->setSmsContent($smsdata["sms_content"]);
                    $storeevent->setIsActive($smsdata["is_active"]);
                    $this->_eventManager->dispatch(
                            'emipro_smsnotification_smsevent_prepare_save', [
                        'smsevent' => $storeevent,
                        'request' => $this->getRequest()
                            ]
                    );
                    try {
                        $storeevent->save();
                        $this->messageManager->addSuccess(__('The SMS Event has been saved.'));
                        $this->_backendSession->setEmiproSmsnotificationSmseventData(false);
                        if ($this->getRequest()->getParam('back')) {

                            $resultRedirect->setPath(
                                    'emipro_smsnotification/*/edit', [
                                'smsevent_id' => $smsdata["smsevent_id"],
                                '_current' => true
                                    ]
                            );
                            return $resultRedirect;
                        }
                        $resultRedirect->setPath('emipro_smsnotification/*/');
                        return $resultRedirect;
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addException($e, __('Something went wrong while saving the SMS Event.'));
                    }
                } elseif (isset($smsdata["use_default"]) && $store_id != 0 && isset($smsdata["main_entity_id"])) {
                    //update  use default status only 
                    $storeevent = $objectManager->create('Emipro\Smsnotification\Model\Smsstore');
                    $storeevent->load($smsdata["smsevent_id"]);
                    if ($storeevent->getId()) {
                        if (isset($smsdata["use_default"])) {
                            $storeevent->setUseDefault(1);
                        }
                        $this->_eventManager->dispatch(
                                'emipro_smsnotification_smsevent_prepare_save', [
                            'smsevent' => $storeevent,
                            'request' => $this->getRequest()
                                ]
                        );
                        try {
                            $storeevent->setId($smsdata["smsevent_id"])->save();
                            $this->messageManager->addSuccess(__('The SMS Event has been saved.'));
                            $this->_backendSession->setEmiproSmsnotificationSmseventData(false);
                            if ($this->getRequest()->getParam('back')) {

                                $resultRedirect->setPath(
                                        'emipro_smsnotification/*/edit', [
                                    'smsevent_id' => $storeevent->getMainEntityId(),
                                    '_current' => true
                                        ]
                                );
                                return $resultRedirect;
                            }
                            $resultRedirect->setPath('emipro_smsnotification/*/');
                            return $resultRedirect;
                        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                            $this->messageManager->addError($e->getMessage());
                        } catch (\RuntimeException $e) {
                            $this->messageManager->addError($e->getMessage());
                        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                            $this->messageManager->addException($e, __('Something went wrong while saving the SMS Event.'));
                        }
                    }
                } else {
                    // update default store record
                    $storeevent = $objectManager->create('Emipro\Smsnotification\Model\Smsevent');
                    $storeevent->load($smsdata["smsevent_id"]);
                    if ($storeevent->getId()) {
                        if (isset($smsdata["sms_content"]))
                            $storeevent->setSmsContent($smsdata["sms_content"]);
                        if (isset($smsdata["is_active"]))
                            $storeevent->setIsActive($smsdata["is_active"]);
                    }
                    $this->_eventManager->dispatch(
                            'emipro_smsnotification_smsevent_prepare_save', [
                        'smsevent' => $storeevent->getId(),
                        'request' => $this->getRequest()
                            ]
                    );
                    try {
                        $storeevent->save();
                        $this->messageManager->addSuccess(__('The SMS Event has been saved.'));
                        $this->_backendSession->setEmiproSmsnotificationSmseventData(false);
                        if ($this->getRequest()->getParam('back')) {

                            $resultRedirect->setPath(
                                    'emipro_smsnotification/*/edit', [
                                'smsevent_id' => $storeevent->getId(),
                                '_current' => true
                                    ]
                            );
                            return $resultRedirect;
                        }
                        $resultRedirect->setPath('emipro_smsnotification/*/');
                        return $resultRedirect;
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addException($e, __('Something went wrong while saving the SMS Event.'));
                    }
                }
            }


            $this->_getSession()->setEmiproSmsnotificationSmseventData($storeevent);
            $resultRedirect->setPath(
                    'emipro_smsnotification/*/edit', [
                'smsevent_id' => $storeevent->getMainEntityId(),
                '_current' => true
                    ]
            );
            return $resultRedirect;
        }
    }
}
