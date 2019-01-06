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

class Delete extends \Emipro\Smsnotification\Controller\Adminhtml\Smsevent
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('smsevent_id');
        if ($id) {
            $sms_title = "";
            try {
                /** @var \Emipro\Smsnotification\Model\Smsevent $smsevent */
                $smsevent = $this->_smseventFactory->create();
                $smsevent->load($id);
                $sms_title = $smsevent->getSms_title();
                $smsevent->delete();
                $this->messageManager->addSuccess(__('The SMS Event has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_emipro_smsnotification_smsevent_on_delete',
                    ['sms_title' => $sms_title, 'status' => 'success']
                );
                $resultRedirect->setPath('emipro_smsnotification/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_emipro_smsnotification_smsevent_on_delete',
                    ['sms_title' => $sms_title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('emipro_smsnotification/*/edit', ['smsevent_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('SMS Event to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('emipro_smsnotification/*/');
        return $resultRedirect;
    }
}
