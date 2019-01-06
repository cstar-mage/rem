<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab\Shipping;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Model\Config\Source\Shipping\Methods as ShippingMethodsConfig;

class ShippingMethods extends GenericForm implements TabInterface
{
    /**
     * @var ShippingMethodsConfig
     */
    protected $shippingConfig;

    /**
     * Main constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param \MageWorx\MultiFees\Model\Config\Source\Shipping\Methods|\MageWorx\ShippingRules\Model\Config\Source\Shipping\Methods $shippingMethodsConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ShippingMethodsConfig $shippingMethodsConfig,
        array $data = []
    ) {
        $this->shippingConfig = $shippingMethodsConfig;

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
        /** @var \MageWorx\MultiFees\Model\ShippingFee $fee */
        $fee = $this->_coreRegistry->registry('mageworx_multifees_fee');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('fee_');

        $fieldset = $form->addFieldset(
            'shipping_methods_fieldset',
            [
                'legend' => __('Shipping Methods'),
                'class'  => 'fieldset-wide'
            ]
        );

        $shippingMethods = $this->getShippingMethods();
        $fieldset->addField(
            FeeInterface::SHIPPING_METHODS,
            'multiselect',
            [
                'name'   => FeeInterface::SHIPPING_METHODS . '[]',
                'label'  => __('Shipping Methods'),
                'title'  => __('Shipping Methods'),
                'values' => $shippingMethods,
            ]
        );

        $form->addValues($fee->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Return all shipping methods as option array
     *
     * @return array
     */
    protected function getShippingMethods()
    {
        return $this->shippingConfig->toOptionArray();
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Shipping Methods');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Shipping Methods');
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
