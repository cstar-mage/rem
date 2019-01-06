<?php

namespace Emipro\Smsnotification\Block\Adminhtml\Smslog;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    protected $moduleManager;
    protected $_smslogFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Backend\Helper\Data $backendHelper, 
    \Emipro\Smsnotification\Model\SmslogFactory $smslogFactory,
    \Magento\Framework\Module\Manager $moduleManager, 
    array $data = []
    ) {
        $this->_smslogFactory = $smslogFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->setId('smslog_grid');
        $this->setDefaultSort('smslog_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() 
    {
        $collection = $this->_smslogFactory->create()->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn(
                'smslog_id', [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'smslog_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );
        $this->addColumn(
                'order_id', [
            'header' => __('Order Id'),
            'index' => 'order_id',
            'renderer'  => '\Emipro\Smsnotification\Block\Adminhtml\Smslog\Grid\Renderer\Orderid',
                ]
        );
        $this->addColumn(
                'customer_id', [
            'header' => __('Customer Email'),
            'index' => 'customer_id',
            'renderer'  => '\Emipro\Smsnotification\Block\Adminhtml\Smslog\Grid\Renderer\Customeremail',
            'filter_condition_callback' => array($this, '_myCustomFilter'),
                ]
        );
        $this->addColumn(
                'contact_number', [
            'header' => __('Mobile Number'),
            'index' => 'contact_number',
                ]
        );  
        $this->addColumn(
                'sms_content', [
            'header' => __('Message Text'),
            'index' => 'sms_content',
                ]
        );
        $this->addColumn(
                'message_type', [
            'header' => __('Message Type'),
            'index' => 'message_type',
            'type'=>'options',
            'options'=>array(0=>"Test Sms",
                             1=>"Customer Notification SMS",
                             4=>"Customer Mass Notification SMS",
                             2=>"Order Notification",
                             3=>"Order Comment",
                             ),
                ]
        );
        $this->addColumn(
                'api_result', [
            'header' => __('SMS Gateway Response'),
            'index' => 'api_result',
                ]
        );
        $this->addColumn(
                'updated_at', [
            'header' => __('Sent Time'),
            'index' => 'updated_at',
            'type'=>'datetime',
            'style'=>'width:50px'                
            ]
        );
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('smslog_id');
        $this->getMassactionBlock()->setFormFieldName('smslog_id');
 
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
 
        return $this;
    }

    public function getGridUrl() {
        return $this->getUrl('emipro_smsnotification/smslog/grid', ['_current' => true]);
    }

    protected function _myCustomFilter($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cst = $objectManager->create("Magento\Customer\Model\Customer")->getCollection();
        $cst->addFieldToFilter('email', array('like' => '%' . $value . '%'));
        $ids = array();
        foreach ($cst as $item) {
            $ids[] = $item->getId();
        }
        $this->getCollection()->addFieldToFilter('customer_id', array('in' => $ids));
        return $this;
    }

}
