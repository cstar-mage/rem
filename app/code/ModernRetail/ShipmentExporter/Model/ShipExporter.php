<?php

namespace ModernRetail\ShipmentExporter\Model;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShipExporter extends \Magento\Framework\Model\AbstractModel
{
    protected $scopeConfig;
    protected $encryptor;
    protected $storeManager;
    protected $helperOrderExporter;
    private $login;
    private $password;
    private $apiUrl;
    private $connection;
    private $order;
    private $convertOrder;
    private $track;
    private $shipmentNotifier;
    private $shipmentCollection;
    private $configLoader;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ModernRetail\OrderExporter\Helper\Data $helperOrderExporter,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollection,
        \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader,
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        $this->helperOrderExporter = $helperOrderExporter;
        $this->order = $order;
        $this->convertOrder = $convertOrder;
        $this->track = $trackFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->shipmentCollection = $shipmentCollection;

        $this->login = $this->scopeConfig->getValue('order_exporter/api/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);;
        $this->password = $this->encryptor->decrypt($this->scopeConfig->getValue('order_exporter/api/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));;
        $this->apiUrl = $this->scopeConfig->getValue('order_exporter/api/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->connection = $this->objectManager->create('\Magento\Framework\App\ResourceConnection')->getConnection();
        $this->state = $this->objectManager->get('\Magento\Framework\App\State');

        $this->configLoader = $configLoader;
    }

    public static function writeLog($error = null)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ship_export.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($error, true));
    }

    public function createShipments()
    {
//        $order = $this->order->loadByIncrementID('000000268');
//        $orderShipments = $this->getShipmentsByOrderId($order->getId());



//        if (count($orderShipments)) {
//            $shippedQty = 0;
//            foreach ($orderShipments as $orderShipment) {
//                $shippedQty += $orderShipment->getTotalQty();
//            }
//
//            var_dump($shippedQty);
//            var_dump($order->getTotalQtyOrdered());
//            die();
//
//            if ($shippedQty == $order->getTotalQtyOrdered()) {
//                $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE, true)->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE, true);
//                $order->save();
//            }
//        }
//
//
//        die('-----------------------------------------------------');


        $result = [];

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $this->apiUrl . '/getShipments');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                [
                    'login' => $this->login,
                    'password' => $this->password,
                ]));

            $out = json_decode(curl_exec($curl), true);
            curl_close($curl);


//            if ($out && isset($out['shipments'])) {
//                if (count($out['shipments'])) {
//                    foreach ($out['shipments'] as $shipmentPOS) {
//                        dd($shipmentPOS['magOrderIncrementId']);
//                    }
//                }
//                }
//
//            d('--');
//
//            die();

            if ($out && isset($out['shipments'])) {
                if (count($out['shipments'])) {


                    $this->objectManager->configure($this->configLoader->load('adminhtml'));

                    foreach ($out['shipments'] as $shipmentPOS) {

//                        dd($shipmentPOS['magOrderIncrementId']);

                        try {
                            $order = $this->order->loadByIncrementID($shipmentPOS['magOrderIncrementId']);

                            $existShipment = $this->existShipment($order->getId(), $shipmentPOS['shipmentID']);

                            if ($existShipment) {
                                $result[$shipmentPOS['shipmentID']] = $existShipment->getincrementId();
                                continue;
                            }

                            if (!$order->canShip()) {
                                self::writeLog('error - shiping, text: You can\'t create an shipment. increment id: ' . $shipmentPOS['magOrderIncrementId'] . ' order id:' . $order->getId());
                                continue;
                            }

                            // Initialize the order shipment object
                            $shipment = $this->convertOrder->toShipment($order);
                            // Loop through order items

                            $addedItems = [];

                            if (!count($shipmentPOS['items'])) {
                                self::writeLog('error - shiping, text: You can\'t create an shipment. increment id: ' . $shipmentPOS['magOrderIncrementId'] . ' .Shipping items does not exist.');
                                continue;
                            }

                            foreach ($order->getAllItems() as $orderItem) {
                                // Check if order item is virtual or has quantity to ship
//                                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
//                                    continue;
//                                }

                                foreach ($shipmentPOS['items'] as $shipItem) {
                                    if ($orderItem->getSku() === trim($shipItem['sku'])) {

                                        if ($orderItem->getParentItem()) {
                                            $parItem = $orderItem->getParentItem();

                                            if (count($addedItems) && in_array($parItem->getId(), $addedItems)) continue;

                                            $parentQtyShipped = $parItem->getQtyOrdered();
                                            // Create shipment item with qty
                                            $parentShipmentItem = $this->convertOrder->itemToShipmentItem($parItem)->setQty($parentQtyShipped);
                                            // Add shipment item to shipment
                                            $shipment->addItem($parentShipmentItem);

                                            $addedItems[] = $parItem->getId();
                                        }

                                        if (count($addedItems) && in_array($orderItem->getId(), $addedItems)) continue;

                                        $qtyShipped = $shipItem['quantity'];
                                        // Create shipment item with qty
                                        $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                                        // Add shipment item to shipment
                                        $shipment->addItem($shipmentItem);

                                        $addedItems[] = $orderItem->getId();
                                    }
                                }
                            }

                            // Register shipment
                            $shipment->register();
                            $data = array(
                                'carrier_code' => 'ups',
                                'title' => $shipmentPOS['shipMethod'],
                                'number' => $shipmentPOS['trackingNumber'],
                            );

                            /**
                             * Save created shipment and order
                             */
                            $track = $this->track->create()->addData($data);
                            $shipment->addTrack($track)->save();
                            $shipment->setMiddlwareNumber($shipmentPOS['shipmentID']);
                            $shipment->save();

                            // Send email
                            $this->shipmentNotifier->notify($shipment);
                            $shipment->save();

                            /**
                             * Change Order Status
                             */
                            $orderShipments = $this->getShipmentsByOrderId($order->getId());

                            if (count($orderShipments)) {
                                $shippedQty = 0;
                                foreach ($orderShipments as $orderShipment) {
                                    $shippedQty += $orderShipment->getTotalQty();
                                }

                                if ($shippedQty == $order->getTotalQtyOrdered()) {
                                    $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE, true)->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE, true);
                                    $order->save();
                                }
                            }
                            $result[$shipmentPOS['shipmentID']] = $shipment->getincrementId();

                        } catch (\Exception $e) {
                            self::writeLog('error - shiping, text:' . $shipmentPOS['magOrderIncrementId'] . ' ' . $e->getMessage());

                        }
                    }
                }
            } else {
                self::writeLog('error - shiping, text: empty response');
            }
        }

        if (count($result)) {
            $this->updateShipmentsIds($result);
        }

        return true;
    }

    public function updateShipmentsIds($ids)
    {
        try {
            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL, $this->apiUrl . '/updateShipmentsIds');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                    [
                        'login' => $this->login,
                        'password' => $this->password,
                        'ids' => $ids
                    ]));

                $out = json_decode(curl_exec($curl), true);

                curl_close($curl);

                if (isset($out['error']) && $out['error'] != 'false' || !isset($out['error'])) {
                    $text = '';
                    if (isset($out['text']) && !empty($out['text'])) {
                        $text = $out['text'];
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($text)
                    );
                }
            }
        } catch (\Exception $ex) {
            self::writeLog('error - shiping update ids, text:' . $ex->getMessage());
        }

        return true;
    }

    public function getShipmentsByOrderId($order_id)
    {
        $shipments = $this->shipmentCollection->create()
            ->addFieldToFilter('order_id', $order_id)
            ->setOrder('entity_id', 'DESC');
        return $shipments;
    }

    public function existShipment($order_id, $middlewareShipId)
    {
        $shipments = $this->shipmentCollection->create()
            ->addFieldToFilter('order_id', $order_id)
            ->addFieldToFilter('middlware_number', $middlewareShipId)
            ->setOrder('entity_id', 'DESC');

        if (count($shipments) > 0) {
            return $shipments->getFirstItem();
        }
        return null;
    }
}
