<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab\Cart;

use MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab\Main as OriginalMain;

class Main extends OriginalMain
{
    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \MageWorx\MultiFees\Model\CartFee $fee */
        $fee = $this->_coreRegistry->registry('mageworx_multifees_fee');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('fee_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Fee Info'),
                'class'  => 'fieldset-wide'
            ]
        );

        $this->addCommonFieldsForAllEntities($fieldset, $fee);

        $fieldset->addField(
            'enable_customer_message',
            'select',
            [
                'label'  => __('Enable Customer Message'),
                'name'   => 'enable_customer_message',
                'values' => $this->booleanOptions->toOptionArray()
            ]
        );

        $fieldset->addField(
            'customer_message_title',
            'text',
            [
                'label' => __('Customer Message Title'),
                'name'  => 'customer_message_title',
            ]
        );

        $fieldset->addField(
            'enable_date_field',
            'select',
            [
                'label'  => __('Enable Date Field'),
                'name'   => 'enable_date_field',
                'values' => $this->booleanOptions->toOptionArray()
            ]
        );

        $fieldset->addField(
            'date_field_title',
            'text',
            [
                'label' => __('Date Field Title'),
                'name'  => 'date_field_title',
            ]
        );

        $form->addValues($fee->getData());

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                'fee_enable_customer_message',
                'enable_customer_message'
            )->addFieldMap(
                'fee_customer_message_title',
                'customer_message_title'
            )->addFieldDependence(
                'customer_message_title',
                'enable_customer_message',
                '1'
            )->addFieldMap(
                'fee_enable_date_field',
                'enable_date_field'
            )->addFieldMap(
                'fee_date_field_title',
                'date_field_title'
            )->addFieldDependence(
                'date_field_title',
                'enable_date_field',
                '1'
            )
        );

        $this->setForm($form);

        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Cart Fee Information');
    }
}
