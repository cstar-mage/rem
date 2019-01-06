<?php

namespace ModernRetail\ShipmentExporter\Model;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class Cron extends \Magento\Framework\Model\AbstractModel
{
    protected $shipExporterFactory;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \ModernRetail\ShipmentExporter\Model\ShipExporter $shipExporterFactory
    ){
        $this->_eventManager = $eventManager;
        $this->shipExporterFactory = $shipExporterFactory;
    }

    public function run()
    {
        $this->shipExporterFactory->createShipments();

        return true;
    }
}