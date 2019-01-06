<?php
namespace Emipro\Smsnotification\Block\Adminhtml\Smsevent\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Widget;
class Help extends Generic implements TabInterface
{
    protected $_systemStore;

    protected $_template = 'help.phtml';

    protected $_wysiwygConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    public function getSmseventId()
    {
        return $this->getRequest()->getParam('smsevent_id');
    }

    public function getTabLabel()
    {
        return __('Help');
    }

    public function getTabTitle()
    {
        return __('Sms Notification Information');
    }
 
    public function canShowTab()
    {
        return true;
    }
 
    public function isHidden()
    {
        return false;
    }
}