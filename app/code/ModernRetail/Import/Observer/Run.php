<?php
namespace ModernRetail\Import\Observer;

use Magento\Backend\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class Run  implements ObserverInterface{

    public $scopeConfig;
    public $import;
    public $session ;
    public $resourceConfig;
    public $helper;
    public $cacheListType;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Session $session,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $dataHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \ModernRetail\Import\Helper\Monitor $monitorHelper

    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->helper = $dataHelper;
        $this->import = $import;
        $this->resourceConfig = $resourceConfig;
        $this->cacheListType = $cacheTypeList;
        $this->monitorHelper = $monitorHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
		  $bucket = $observer->getEvent()->getBucket();
		  $file = $observer->getEvent()->getFile();
		  
		  $debug = false;
		  
		  if ($observer->getEvent()->getDebug()){
		  	$debug = true;
		  }
		  
		  $this->import->setPath($this->helper->getPath());
	      $this->import->setBucket($bucket);
	      $this->import->setXmlFile($file);
		  $this->import->setDebug($debug);
		  
	      @touch($this->helper->getPath().DS.$bucket.DS.$file.".lock");
	      @unlink($this->helper->getPath().DS.$bucket.DS.$file.".done");

        /**
         * Send info that job started
         */
        $this->monitorHelper->sendJob(["job_id"=>$file,"state"=>"processing","log"=>"Job start to process"]);

        try {
            $return = $this->import->proccess();
            if ($return===true){
                /**
                 * send about job completion
                 */
                $this->monitorHelper->sendJob(["job_id"=>$file,"state"=>"completed","log"=>file_get_contents($this->import->getLogFile())]);

                @unlink($this->helper->getPath().DS.$bucket.DS.$file.".lock");
                @touch($this->helper->getPath().DS.$bucket.DS.$file.".done");

                die('DONE');
            }else {
                $this->monitorHelper->sendJob(["job_id"=>$file,"state"=>"failed","log"=>file_get_contents($this->import->getLogFile())]);
                die("ERROR");
            }
        }catch(\Exception $ex){
            /**
             * send about job completion
             */
            $this->monitorHelper->sendJob(["job_id"=>$file,"state"=>"failed","log"=>file_get_contents($this->import->getLogFile())."\n".$ex->getMessage()]);
        }
        die("ERROR");
	}

}