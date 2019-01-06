<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use FME\Events\Model\Event;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'FME_Events::manage_event';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    protected $model;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        Event $model,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->model = $model;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        
        $data = $this->getRequest()->getPostValue();

        
                
        if (isset($data["related_products"])) {
             $cat_array = json_decode($data['related_products'], true);

              $pro_array = array_values($cat_array);
                $c=0;
            foreach ($cat_array as $key => $value) {
                $pro_array[$c] = $key;
                $c++;
            }
              unset($data['related_products']);
              $data['entity_id'] = $pro_array;
        }
              
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filterEvent($data);
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Event::STATUS_ENABLED;
            }
            if (empty($data['event_id'])) {
                $data['event_id'] = null;
            }

            $id = $this->getRequest()->getParam('event_id');
            if ($id) {
                $this->model->load($id);
            }

            $this->model->setData($data);

            $this->_eventManager->dispatch(
                'events_event_prepare_save',
                ['Event' => $this->model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validateEvent($data)) {
                return $resultRedirect->setPath('*/*/edit', ['event_id' => $this->model->getId(), '_current' => true]);
            }

            try {
                $this->model->save();
                $this->messageManager->addSuccess(__('You saved the event.'));
                $this->dataPersistor->clear('events');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['event_id' => $this->model->getId(),
                         '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the event.'));
            }

            $this->dataPersistor->set('events', $data);
            return $resultRedirect->setPath('*/*/edit', ['event_id' => $this->getRequest()->getParam('event_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
