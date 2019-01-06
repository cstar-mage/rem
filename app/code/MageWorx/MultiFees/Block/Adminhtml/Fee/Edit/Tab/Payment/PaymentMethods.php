<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab\Payment;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Model\Config\Source\Payment\Methods as PaymentMethodsConfig;

class PaymentMethods extends GenericForm implements TabInterface
{
    /**
     * @var \MageWorx\MultiFees\Model\Config\Source\Payment\Methods
     */
    protected $paymentMethodsConfig;

    /**
     * Main constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param PaymentMethodsConfig $paymentMethodsConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        PaymentMethodsConfig $paymentMethodsConfig,
        array $data = []
    ) {
        $this->paymentMethodsConfig = $paymentMethodsConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \MageWorx\MultiFees\Model\PaymentFee $fee */
        $fee = $this->_coreRegistry->registry('mageworx_multifees_fee');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('fee_');

        $fieldset = $form->addFieldset(
            'payment_methods_fieldset',
            [
                'legend' => __('Payment Methods'),
                'class'  => 'fieldset-wide'
            ]
        );

        $paymentMethods = $this->getPaymentMethods();
        $fieldset->addField(
            FeeInterface::PAYMENT_METHODS,
            'multiselect',
            [
                'name'   => FeeInterface::PAYMENT_METHODS . '[]',
                'label'  => __('Payment Methods'),
                'title'  => __('Payment Methods'),
                'values' => $paymentMethods,
            ]
        );

        $form->addValues($fee->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Return all payment methods as option array
     *
     * @return array
     */
    protected function getPaymentMethods()
    {
        return $this->paymentMethodsConfig->toOptionArray();
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Payment Methods');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Payment Methods');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }
}
