<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Shipping;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\MultiFees\Api\ShippingFeeRepositoryInterface as FeeRepository;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Controller\Adminhtml\Fee\ShippingFee as FeeAbstractController;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\MultiFees\Model\ShippingFeeFactory as FeeFactory;

class Save extends FeeAbstractController
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * MassAction constructor.
     *
     * @param Registry $registry
     * @param FeeFactory $feeFactory
     * @param FeeRepository $feeRepository
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $jsonFactory
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Registry $registry,
        FeeFactory $feeFactory,
        FeeRepository $feeRepository,
        PageFactory $resultPageFactory,
        JsonFactory $jsonFactory,
        Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor
    ) {

        parent::__construct($registry, $feeFactory, $feeRepository, $resultPageFactory, $jsonFactory, $context);
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            try {
                // @important Add hardcoded fee type
                $data[FeeInterface::TYPE] = FeeInterface::SHIPPING_TYPE;
                // then process a data from the form
                $data  = $this->dataProcessor->filter($data);
                $model = $this->initFee();
                $model->addData($data);
                $model->loadPost($data);

                $this->_eventManager->dispatch(
                    'mageworx_multifees_fee_prepare_save',
                    ['fee' => $model, 'request' => $this->getRequest()]
                );

                if (!$this->dataProcessor->validate($data)) {
                    return $resultRedirect->setPath('*/*/edit', ['fee_id' => $model->getId(), '_current' => true]);
                }

                $this->feeRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the fee.'));
                $this->dataPersistor->clear('mageworx_multifees_fee');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['fee_id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the fee.'));
            }

            $this->dataPersistor->set('mageworx_multifees_fee', $data);

            return $resultRedirect->setPath('*/*/edit', ['fee_id' => $this->getRequest()->getParam('fee_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
