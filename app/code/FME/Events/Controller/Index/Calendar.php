<?php
/**
 * Default index action (with 404 Not Found headers)
 * Used if default page don't configure or available
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\Events\Controller\Index;

class Calendar extends \Magento\Framework\App\Action\Action
{


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }
    public function execute()
    {
        
         $this->_view->loadLayout();
         $this->_view->renderLayout();
    }
}
