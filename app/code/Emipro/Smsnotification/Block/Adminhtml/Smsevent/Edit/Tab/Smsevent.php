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

namespace Emipro\Smsnotification\Block\Adminhtml\Smsevent\Edit\Tab;

class Smsevent extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm() {
        /** @var \Emipro\Smsnotification\Model\Smsevent $smsevent */
        $smsevent = $this->_coreRegistry->registry('emipro_smsnotification_smsevent');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('smsevent_');
        $form->setFieldNameSuffix('smsevent');
        $fieldset = $form->addFieldset(
                'base_fieldset', [
            'legend' => __('SMS Notification Information'),
            'class' => 'fieldset-wide'
                ]
        );

        if ($smsevent->getId()) {
            $fieldset->addField(
                    'smsevent_id', 'hidden', ['name' => 'smsevent_id']
            );
        }

        if ($smsevent->getMainEntityId()) {
            $fieldset->addField(
                    'main_entity_id', 'hidden', ['name' => 'main_entity_id']
            );
        }
        
        
        $fieldset->addField(
                'sms_title_hidden', 'hidden', ['name' => 'sms_title_hidden']
        );
        

        if ($this->showDefaultCheckbox()) {
            if ($smsevent->getUseDefault() == 1 || (empty($smsevent->getMainEntityId()) && $this->getStoreparam() != 0)) {
                $fieldset->addField(
                        'use_default', 'checkbox', [
                    'name' => 'use_default',
                    'value' => 1,
                    'checked' => true,
                    'label' => __('Use Default'),
                    'title' => __('Use Default'),
                        ]
                );
            } else {
                $fieldset->addField(
                        'use_default', 'checkbox', [
                    'name' => 'use_default',
                    'value' => "1",
                    'label' => __('Use Default'),
                    'title' => __('Use Default'),
                        ]
                );
            }
        }
        $fieldset->addField(
                'sms_title', 'text', [
            'name' => 'sms_title',
            'label' => __('SMS  Title'),
            'title' => __('SMS  Title'),
                ]
        );

        $fieldset->addField(
                'sms_events', 'hidden', [
            'name' => 'sms_events',
                ]
        );
        $fieldset->addField(
                'sms_content', 'textarea', [
            'name' => 'sms_content',
            'label' => __('SMS Content'),
            'title' => __('SMS Content'),
            'required' => true,
                ]
        );

        $fieldset->addField(
                'is_active', 'select', [
            'label' => __('Status'),
            'title' => __('Status'),
            'name' => 'is_active',
            'required' => true,
            'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
                ]
        )->setAfterElementHtml(
                "<script>require([
    'jquery',
], function($){
    var title_text=$('#smsevent_sms_title').val();
    var content=$('#smsevent_sms_title').parent();
    $('#smsevent_sms_title_hidden').val(title_text);
    $(content).html('');
    $(content).html('<h2>'+title_text+'</h2>');

    //$('#smsevent_sms_title').parent().html('<h2>'+title_text+'</h2>');
    //$('#smsevent_sms_title').prop('disabled', true);
    check(); 
    $('#smsevent_use_default').change(function() {
        check();
    });
    


    function check()
    {
        if($('#smsevent_use_default').is(':checked')) {
            $('#smsevent_sms_content').prop('disabled', true);
            $('#smsevent_is_active').prop('disabled', true);
        }
        else
        {
            $('#smsevent_sms_content').prop('disabled', false);
            $('#smsevent_is_active').prop('disabled', false);
        }
    }
});</script>"
        );


        $smseventData = $this->_session->getData('emipro_smsnotification_smsevent_data', true);
        if ($smseventData) {
            $smsevent->addData($smseventData);
        } else {
            if (!$smsevent->getId()) {
                $smsevent->addData($smsevent->getDefaultValues());
            }
        }
        $form->addValues($smsevent->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return __('SMS Notification Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
        return false;
    }

    /*
     * this method will decide wether to display "use default" checkbox or not
     */
            
    public function showDefaultCheckbox() {
        $flag = $this->getRequest()->getParam("store", 0);
        if ($flag == 0) {
            return false;
        } else {
            return true;
        }
    }
    public function getStoreparam()
    {
        return $this->getRequest()->getParam("store", 0);
    }

}
