<?php

namespace ModernRetail\OrderExporter\Plugin;

class PluginBeforeView
{
    protected $scopeConfig;

    protected $storeManager;

    protected $helperOrderExporter;

    protected $modelOrderExporter;

    protected $request;

    protected $appState;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ModernRetail\OrderExporter\Helper\Data $helperOrderExporter,
        \ModernRetail\OrderExporter\Model\OrderExporter $modelOrderExporter,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ){
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->helperOrderExporter = $helperOrderExporter;
        $this->modelOrderExporter = $modelOrderExporter;
        $this->request = $request;
        $this->appState = $appState;
    }

    public function beforeGetOrderId(\Magento\Sales\Block\Adminhtml\Order\View $subject){

        $enable =  $this->scopeConfig->getValue('order_exporter/api/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $orderId= $this->request->getParam('order_id');

        if ($enable && !$this->modelOrderExporter->isOrderExported($orderId)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $urlBuilder = $objectManager->get('\Magento\Backend\Model\UrlInterface');
            $url = $urlBuilder->getUrl('order_export/export/index');

            $subject->addButton(
                'force_order_sync',
                ['label' => __('Force Order Sync'), 'onclick' => "setLocation('$url')", 'class' => 'reset'],
                -1
            );
            return null;
        }
    }

}