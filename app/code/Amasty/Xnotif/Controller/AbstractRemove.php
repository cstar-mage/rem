<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Controller;

use Magento\Framework\App\RequestInterface;

abstract class AbstractRemove extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    private $stockFactory;

    /**
     * @var \Magento\ProductAlert\Model\PriceFactory
     */
    private $priceFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\ProductAlert\Model\StockFactory $stockFactory,
        \Magento\ProductAlert\Model\PriceFactory $priceFactory
    ) {
        parent::__construct($context);

        $this->customerSession = $customerSession;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->stockFactory = $stockFactory;
        $this->priceFactory = $priceFactory;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('item');

        if (static::TYPE == \Amasty\Xnotif\Controller\Stock\Remove::TYPE) {
            $model = $this->stockFactory->create();
        } else {
            $model = $this->priceFactory->create();
        }

        $item = $model->load($id);
        $currentCustomerId = $this->customerSession->getId();

        // check if not a guest subscription (cust. id is set) and is matching with logged in customer
        if ($item->getCustomerId() > 0
            && $item->getCustomerId() == $currentCustomerId
        ) {
            try {
                $item->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __(
                        'An error occurred while deleting the item from Subscriptions: %s',
                        $e->getMessage()
                    )
                );
            }
        }

        $redirect = $this->redirectFactory->create();
        $redirect->setPath($this->_url->getUrl('*/*'));

        return $redirect;
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }
}
