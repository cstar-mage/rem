<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace FME\Events\Block\Adminhtml\Event\Edit;

class AssignMap extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'FME_Events::event/mapconfig.phtml';

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    protected $_productFactory;
    protected $_eventFactory;
    public $_storeAdminHelper;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \FME\Events\Helper\Data $helper,
        \Magento\Backend\Block\Template\Context $context,
        \FME\Events\Model\Event $eventFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        
        $this->_eventFactory = $eventFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_storeAdminHelper = $helper;
        parent::__construct($context);
    }
    
    public function getBlockGrid()
    {
        $id = $this->getRequest()->getParam('event_id');
        $mediaobj = $this->_eventFactory->getStores($id);
        return $mediaobj;
    }
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }
}
