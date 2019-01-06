<?php

namespace ModernRetail\OrderExporter\Model;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class OrderExporter extends \Magento\Framework\Model\AbstractModel
{

    protected $scopeConfig;

    protected $encryptor;

    protected $storeManager;

    protected $helperOrderExporter;

    protected $appState;

    private $login;

    private $password;

    private $apiUrl;

    private $connection;

    private $timezone;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ModernRetail\OrderExporter\Helper\Data $helperOrderExporter,
        \Magento\Framework\App\State $appState,
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        $this->helperOrderExporter = $helperOrderExporter;
        $this->appState = $appState;
        $this->login = $this->scopeConfig->getValue('order_exporter/api/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);;
        $this->password = $this->encryptor->decrypt($this->scopeConfig->getValue('order_exporter/api/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));;
        $this->apiUrl = $this->scopeConfig->getValue('order_exporter/api/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->connection = $this->objectManager->create('\Magento\Framework\App\ResourceConnection')->getConnection();
        $this->timezone = $this->objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
    }

    public static function writeLog($error = null)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/order_export.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($error, true));
    }

    public function _getOrderItems($items)
    {
        $result = [];

        foreach ($items as $item) {
            if ($item->getProductType() == "simple" && $item->getParentItem()) {
                $parentItem = $item->getParentItem();

                if ('bundle' !== $parentItem->getProductType()) {
                    continue;
                }
            }

            if ($item->getProduct()) {
                $attribute = $item->getProduct()->getResource()
                    ->getAttribute('small_image');
                $imageUrl = $attribute->getFrontend()
                    ->getUrl($item->getProduct());
                $item->setImage($imageUrl);
            }
            $result[] = $item->getData();
        }

        return $result;
    }

    public function createOrder($order)
    {

        $billingAddress = null;

        if ($order->getBillingAddress()) {
            $billingAddress = $order->getBillingAddress()->getData();
        }

        $shippingAddress = null;
        if ($order->getShippingAddress()) {
            $shippingAddress = $order->getShippingAddress()->getData();
        }

        $data = $order->getData();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $data['payment_method'] = $method->getTitle();
        $data['cc_type'] = $payment->getCcType();
        $data['additional_information'] = $payment->getAdditionalInformation();

        $items = $this->_getOrderItems($order->getAllItems());
/*
        $items = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == "simple" && $item->getParentItem()) continue;

            if ($item->getProduct()) {
                $attribute = $item->getProduct()->getResource()
                    ->getAttribute('small_image');
                $imageUrl = $attribute->getFrontend()
                    ->getUrl($item->getProduct());
                $item->setImage($imageUrl);
            }
            $items[] = $item->getData();
        }
*/
        $data['items'] = $items;


        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $this->apiUrl . '/createOrder');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                [
                    'login' => $this->login,
                    'password' => $this->password,
                    'order' => [
                        'data' => $data,
                        // 'items' => $items,
                        'billingAddress' => $billingAddress,
                        'shippingAddress' => $shippingAddress
                    ]
                ]));
            $out = json_decode(curl_exec($curl), true);
            // d($out);
            curl_close($curl);

            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug($out['error']);

            if (isset($out['error']) && $out['error'] != 'false' || !isset($out['error'])) {
                $text = '';
                if (isset($out['text']) && !empty($out['text'])) $text = $out['text'];
                self::writeLog('error - order:' . $order->getIncrementId() . ' 
                text:' . $text);
            } else {
                $text = '';
                if (isset($out['text']) && !empty($out['text'])) $text = $out['text'];
                self::writeLog('success - order:' . $order->getIncrementId() . ' text:' . $text);

                $id = $order->getId();
                $sql = "UPDATE `sales_order` SET is_sent_to_middlware = 1 WHERE entity_id = $id";
                try {
                    if ($id) {
                        $run = $this->connection->query($sql);
                    }
                } catch (\Exception $ex) {
                    self::writeLog('error - order:' . $order->getIncrementId() . ' text:' . $ex->getMessage());
                }
            }
        }
    }

    public function createInvoice($invoice)
    {

        $order_id = $invoice->getOrderId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order')->load($order_id);

        $orderData = $order->getData();
        $invoiceData = $invoice->getData();

        $ids = [];

        $orderItems = $this->_getOrderItems($order->getAllItems());
        foreach ($orderItems as $item) {
            $orderData['items'][$item['item_id']] = $item;
            $ids[] = $item['item_id'];
        }

        $items = [];
        foreach ($invoice->getAllItems() as $item) {
            if (!in_array($item->getOrderItemId(), $ids)) continue;
            $items[] = $item->getData();
        }

        $invoiceData['items'] = $items;

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $this->apiUrl . '/createInvoice');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                [
                    'login' => $this->login,
                    'password' => $this->password,
                    'invoice' => [
                        'data' => $invoiceData,
                        // 'items' => $items,
                        'order' => $orderData
                    ]
                ]));
            $out = json_decode(curl_exec($curl), true);
            // d($out);
            curl_close($curl);

            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug($out['error']);

            if (isset($out['error']) && $out['error'] != 'false' || !isset($out['error'])) {
                $text = '';
                if (isset($out['text']) && !empty($out['text'])) $text = $out['text'];
                self::writeLog('error - invoice:' . $invoice->getIncrementId() . ' 
                text:' . $text);
            } else {
                $text = '';
                if (isset($out['text']) && !empty($out['text'])) $text = $out['text'];
                self::writeLog('success - invoice:' . $invoice->getIncrementId() . ' text:' . $text);

                $id = $invoice->getId();
                $sql = "UPDATE `sales_invoice` SET is_sent_to_middlware = 1 WHERE entity_id = $id";
                try {
                    if ($id) {
                        $run = $this->connection->query($sql);
                    }
                } catch (\Exception $ex) {
                    self::writeLog('error - invoice:' . $invoice->getIncrementId() . ' text:' . $ex->getMessage());
                }
            }
        }
    }

    public function isOrderExported($order_id)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order')->load($order_id);

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $this->apiUrl . 'isOrderExported');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(
                [
                    'login' => $this->login,
                    'password' => $this->password,
                    'orderIncrementId' => $order->getIncrementId()
                ]));
            $out = json_decode(curl_exec($curl), true);
            curl_close($curl);

            if (isset($out['isExist'])) {
                return $out['isExist'];
            }
            return false;
        }
    }

    public function createCreditmemo($creditmemo)
    {

        $order_id = $creditmemo->getOrderId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order')->load($order_id);

        $orderData = $order->getData();
        $creditmemoData = $creditmemo->getData();

        $ids = [];

        $orderItems = $this->_getOrderItems($order->getAllItems());
        foreach ($orderItems as $item) {
            $orderData['items'][$item['item_id']] = $item;
            $ids[] = $item['item_id'];
        }

        $items = [];
        foreach ($creditmemo->getAllItems() as $item) {
            if (!in_array($item->getOrderItemId(), $ids)) continue;
            $items[] = $item->getData();
        }

        $creditmemoData['items'] = $items;

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $this->apiUrl . '/createCreditmemo');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                [
                    'login' => $this->login,
                    'password' => $this->password,
                    'creditmemo' => [
                        'data' => $creditmemoData,
                        'order' => $orderData
                    ]
                ]));
            $out = json_decode(curl_exec($curl), true);
//             d($out);
            curl_close($curl);

            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug($out['error']);

            if (isset($out['error']) && $out['error'] != 'false' || !isset($out['error'])) {
                $text = '';
                if (isset($out['text']) && !empty($out['text'])) $text = $out['text'];
                self::writeLog('error - creditmemo:' . $creditmemo->getIncrementId() . ' 
                text:' . $text);
            } else {
                $text = '';
                if (isset($out['text']) && !empty($out['text'])) $text = $out['text'];
                self::writeLog('success - creditmemo:' . $creditmemo->getIncrementId() . ' text:' . $text);

                $id = $creditmemo->getId();
                $sql = "UPDATE `sales_creditmemo` SET is_sent_to_middlware = 1 WHERE entity_id = $id";
                try {
                    if ($id) {
                        $run = $this->connection->query($sql);
                    }
                } catch (\Exception $ex) {
                    sself::writeLog('success - creditmemo:' . $creditmemo->getIncrementId() . ' text:' . $ex->getMessage());
                }
            }
        }
    }
}
