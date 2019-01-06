<?php

namespace Emipro\Smsnotification\Block\Adminhtml\Order\View\Tab;

class Sms extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Emipro_Smsnotification::send_order_view.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Send SMS');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Send SMS');
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    public function getOrderNumber()
    {
        return $this->getRequest()->getParam("order_id");
    }
    
    
    public function getPostformUrl() {
        return $this->_urlBuilder->getUrl("emipro_smsnotification/smssend/sendordersms");
    }
    
    public function getSmsData(){
        $orderId = $this->getOrderNumber();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $smslogValues = $objectManager->create("Emipro\Smsnotification\Model\Smslog")
                                      ->getCollection() 
                                      ->addFieldToFilter("order_id",$orderId)
                                      ->addFieldToFilter("message_type",3)
                                      ->setOrder('smslog_id','DESC');
        return $smslogValues->getData();
    }

}
