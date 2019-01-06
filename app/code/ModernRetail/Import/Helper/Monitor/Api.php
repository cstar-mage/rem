<?php
namespace ModernRetail\Import\Helper\Monitor;

use Magento\Framework\App\Helper\AbstractHelper;

class Api  extends AbstractHelper{

    const XML_CONFIG_API_LOGIN = "modernretail_import/credentials/login";
    const XML_CONFIG_API_PASSWORD = "modernretail_import/credentials/password";

    //const API_BASE_URl = "https://api.modernretail.com/monitor";
    const API_BASE_URl = "http://api.oleg.modernretail.com/monitor/";

    public function __construct(   \Magento\Framework\App\Helper\Context $context,\ModernRetail\Import\Helper\Monitor\Logger $logger,        \Magento\Framework\Encryption\EncryptorInterface $encryptor)
    {
        $this->monitorApiLogger = $logger;
        $this->context = $context;
        $this->encryptor = $encryptor;
       // d($this->_getPassword());
    }

    private function _getLogin(){
        return $this->context->getScopeConfig()->getValue(self::XML_CONFIG_API_LOGIN,   \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    private  function _getPassword(){
        $password =  $this->context->getScopeConfig()->getValue(self::XML_CONFIG_API_PASSWORD,   \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $password = $this->encryptor->decrypt($password);
        return $password;
    }


    public function call($method = "GET",$endpoint,$jsonData = null){


        if ($endpoint["0"]=="/"){
            $endpoint = substr($endpoint,1 );
        }

        $fullUrl = self::API_BASE_URl."/".$endpoint;

        $ch = curl_init();

        $timeout = 15;
        $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
        curl_setopt ($ch, CURLOPT_URL, $fullUrl);
        curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch,CURLOPT_CONNECTTIMEOUT,120);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method=="POST"){
           // curl_setopt($ch,  CURLOPT_POST, true);
        }

        if ($jsonData){
            $json = json_encode($jsonData);
         
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($ch,CURLOPT_TIMEOUT,10);
        curl_setopt ($ch,CURLOPT_MAXREDIRS,10);


        /**
         * adding auth
         */
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getLogin() . ":" . $this->_getPassword());




        $data = curl_exec($ch);
        curl_close($ch);




        $data = json_decode($data,true);


        if (array_key_exists('status',$data ) && ($data['status']=='Error' || $data['status']=='ERROR')){
            $this->monitorApiLogger->info($data['message'],$jsonData);
            return false;
            //throw new \Exception($data['message']);
        }
        return $data;


    }

    public  function apiGET($endpoint){
        return $this->call("GET",$endpoint,null);
    }
    public  function apiPOST($endpoint,$data = null){
        return $this->call("POST",$endpoint,$data);
    }
    public  function apiPUT($endpoint,$data = null){
        return $this->call("PUT",$endpoint,$data);
    }
}