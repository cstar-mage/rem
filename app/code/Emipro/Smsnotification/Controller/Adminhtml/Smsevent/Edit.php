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

class Edit extends \Emipro\Smsnotification\Controller\Adminhtml\Smsevent
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    
    protected $_smsresource;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Emipro\Smsnotification\Model\SmseventFactory $smseventFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Emipro\Smsnotification\Model\SmseventFactory $smseventFactory,
        \Emipro\Smsnotification\Model\ResourceModel\Smsevent $smsresource,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession    = $context->getSession();
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_smsresource=$smsresource;
        parent::__construct($smseventFactory, $registry, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Emipro_Smsnotification::smsevent');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('smsevent_id');
        /** @var \Emipro\Smsnotification\Model\Smsevent $smsevent */
        $smsevent = $this->_initSmsevent();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Emipro_Smsnotification::smsevent');
        $resultPage->getConfig()->getTitle()->set(__('SMS Events'));
        if ($id) {
            if (!$smsevent->getId()) {
                $this->messageManager->addError(__('This SMS Event no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'emipro_smsnotification/*/edit',
                    [
                        'smsevent_id' => $id,
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $smsevent->getId() ? $smsevent->getSms_title() : __('New SMS Event');
        $resultPage->getConfig()->getTitle()->prepend("Edit SMS Notification '".$title."'");
        $data = $this->_backendSession->getData('emipro_smsnotification_smsevent_data', true);
        if (!empty($data)) {
            $smsevent->setData($data);
        }
        return $resultPage;
    }
}
