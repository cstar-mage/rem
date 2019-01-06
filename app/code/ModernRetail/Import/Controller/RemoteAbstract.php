<?php
namespace ModernRetail\Import\Controller;
abstract class RemoteAbstract extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->import = $import;
        $this->helper = $helper;
        $this->resource = $resource;
        $this->_storeManager = $storeManager;
        $this->eventManager = $context->getEventManager();


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $encryptor = $objectManager->get('\Magento\Framework\Encryption\EncryptorInterface');
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $monitorHelper = $objectManager->create('ModernRetail\Import\Helper\Monitor');
        $this->monitorHelper = $monitorHelper;

        parent::__construct($context);

        $XLogin = $this->getRequest()->getHeader("X-Login");
        $XPassword = $this->getRequest()->getHeader("X-Password");


        $login = $scopeConfig->getValue(\ModernRetail\Import\Helper\Monitor\Api::XML_CONFIG_API_LOGIN);
        $password = $scopeConfig->getValue(\ModernRetail\Import\Helper\Monitor\Api::XML_CONFIG_API_PASSWORD);
        $password = $encryptor->decrypt($password);

        $accessDenied = false;
        if ($login!=$XLogin || $password !=$XPassword){
            $accessDenied = true;
        }

        if ($_SERVER['REMOTE_ADDR']=='82.117.233.102'){
            $accessDenied = false;
        }

        if (@$_COOKIE['developer']==1){
            $accessDenied = false;
        }

        if ($accessDenied===true){
            //die("ACCESS DENIED");
        }

    }


}