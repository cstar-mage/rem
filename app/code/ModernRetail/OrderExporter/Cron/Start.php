<?php

namespace ModernRetail\OrderExporter\Cron;

class Start {
 
    public function execute() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->get('ModernRetail\OrderExporter\Model\Cron')->run();
    }
    
}



