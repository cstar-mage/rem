<?php

namespace FME\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
 
class Edit extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'FME_Events::manage_event';

    protected $_coreRegistry;
    protected $resultPageFactory;
    protected $model;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \FME\Events\Model\Event $model,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->model = $model;
        parent::__construct($context);
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('FME_Events::events_event')
            ->addBreadcrumb(__('EVENTS'), __('EVENTS'))
            ->addBreadcrumb(__('Manage Events'), __('Manage Events'));
        return $resultPage;
    }
        
    public function execute()
    {

        $id = $this->getRequest()->getParam('event_id');
        $photogallery = $this->_objectManager->create('FME\Events\Model\MediaFactory');
        $collection = $photogallery->create()->getCollection()->addFieldToFilter('event_id', $id);
        if ($id) {
            $this->model->load($id);
            if (!$this->model->getId()) {
                $this->messageManager
                ->addError(__('This event no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_objectManager->get('Magento\Framework\Registry')->register('photogallery_img', $collection);

        $this->_coreRegistry->register('events_event', $this->model);

        $resultPage = $this->_initAction();

        $resultPage->addBreadcrumb(
            $id ? __('Edit Event') : __('New Event'),
            $id ? __('Edit Event') : __('New Event')
        );
        
        $resultPage->getConfig()->getTitle()->prepend(__('Events'));
        $resultPage->getConfig()->getTitle()
            ->prepend($this->model->getId() ? $this->model->getEventName() : __('New Events'));

        return $resultPage;
    }
}
