<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use MageWorx\MultiFees\Model\CartFeeFactory as FeeFactory;
use MageWorx\MultiFees\Api\CartFeeRepositoryInterface as FeeRepository;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

abstract class CartFee extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MageWorx_MultiFees::multifees';

    /**
     * Fee factory
     *
     * @var FeeFactory
     */
    protected $feeFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var FeeRepository
     */
    protected $feeRepository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Registry $registry
     * @param FeeFactory $feeFactory
     * @param FeeRepository $feeRepository
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        FeeFactory $feeFactory,
        FeeRepository $feeRepository,
        PageFactory $resultPageFactory,
        JsonFactory $jsonFactory,
        Context $context
    ) {
        $this->coreRegistry          = $registry;
        $this->feeFactory            = $feeFactory;
        $this->feeRepository         = $feeRepository;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultPageFactory     = $resultPageFactory;
        $this->jsonFactory           = $jsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \MageWorx\MultiFees\Model\CartFee
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initFee()
    {
        $feeId = $this->getRequest()->getParam('fee_id');
        if ($feeId) {
            $fee = $this->feeRepository->getById($feeId);
        } else {
            $fee = $this->feeFactory->create();
        }
        $this->coreRegistry->register('mageworx_multifees_fee', $fee);

        return $fee;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    protected function initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageWorx_MultiFees::multifees_cart');
        $resultPage->getConfig()->getTitle()->set((__('Cart Fee')));
        $resultPage->addBreadcrumb(__('MultiFees'), __('MultiFees'));
        $resultPage->addBreadcrumb(__('Cart Fee'), __('Cart Fee'));

        return $resultPage;
    }

    /**
     * filter data
     *
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
        return $data;
    }

    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_MultiFees::multifees');
    }
}
