<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ProductAlert\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Unsubscribe
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Xnotif\Model\UrlHash
     */
    private $urlHash;

    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Unsubscribe\AlertProvider
     */
    private $alertProvider;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Xnotif\Model\UrlHash $urlHash,
        \Amasty\Xnotif\Model\ResourceModel\Unsubscribe\AlertProvider $alertProvider,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->request = $request;
        $this->urlHash = $urlHash;
        $this->alertProvider = $alertProvider;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
    }

    public function aroundDispatch(
        $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        //remove alerts for guests
        if (!$this->urlHash->check($this->request)) {
            return $proceed($request);
        }

        $productId = $this->request->getParam('product_id');
        $email = $this->request->getParam('email');
        $type = $this->request->getParam('type');

        try {
            $collection = $this->alertProvider->getAlertModel($type, $productId, $email);
            if ($collection->getSize()) {
                $collection->walk('delete');

                $this->messageManager->addSuccessMessage(
                    __('You will no longer receive stock alerts for this product.')
                );
            }
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage(__('The product was not found.'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl('/');

        return $resultRedirect;
    }
}
