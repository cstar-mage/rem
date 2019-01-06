<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ControllerFrontSendResponseBefore implements ObserverInterface
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        UrlHelper $urlHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->urlHelper = $urlHelper;
        $this->coreRegistry = $coreRegistry;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->jsonEncoder = $jsonEncoder;
    }
    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getRequest();

        if (strpos($request->getPathInfo(), "/checkout/cart/add/") !== false) {
            $params = $request->getPost();
            $params = $params->toArray();
            $response = $observer->getResponse();
            $content = $response->getContent();

            if (array_key_exists('in_cart', $params) && strpos($content, "checkout") !== false) {
                $result = [];
                $content = $this->jsonEncoder->encode($result);
                $response->setContent($content);
            }
        }
    }
}
