<?php
/**
 * {{Emipro}}_{{Smsnotification}} extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Emipro
 *                     @package   Emipro_Smsnotification
 *                     @copyright Copyright (c) 2015
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Emipro\Smsnotification\Block\Adminhtml\Smsevent;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize SMS Event edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'smsevent_id';
        $this->_blockGroup = 'Emipro_Smsnotification';
        $this->_controller = 'adminhtml_smsevent';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save SMS Event'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->remove("delete");
    }
    /**
     * Retrieve text for header element depending on loaded SMS Event
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Emipro\Smsnotification\Model\Smsevent $smsevent */
        $smsevent = $this->_coreRegistry->registry('emipro_smsnotification_smsevent');
        if ($smsevent->getId()) {
            return __("Edit SMS Event '%1'", $this->escapeHtml($smsevent->getSms_title()));
        }
        return __('New SMS Event');
    }
}
