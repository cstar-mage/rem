<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab;

use MageWorx\MultiFees\Model\ResourceModel\FeeAbstractResource;

/**
 * Fee add/edit form options tab
 */
class MainOptions extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Fee instance
     *
     * @var FeeAbstractResource
     */
    protected $fee = null;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesnoFactory;

    /**
     * @var \MageWorx\MultiFees\Model\Fee\Source\InputType
     */
    protected $inputTypeOptions;

    /**
     * @var \MageWorx\MultiFees\Model\Fee\Source\AppliedTotals
     */
    protected $appliedTotalsOptions;

    /**
     * MainOptions constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \MageWorx\MultiFees\Model\Fee\Source\InputType $inputTypeOptions
     * @param \MageWorx\MultiFees\Model\Fee\Source\AppliedTotals $appliedTotalsOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \MageWorx\MultiFees\Model\Fee\Source\InputType $inputTypeOptions,
        \MageWorx\MultiFees\Model\Fee\Source\AppliedTotals $appliedTotalsOptions,
        array $data = []
    ) {
        $this->yesnoFactory         = $yesnoFactory;
        $this->inputTypeOptions     = $inputTypeOptions;
        $this->appliedTotalsOptions = $appliedTotalsOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set attribute object
     *
     * @param Attribute $attribute
     * @return $this
     * @codeCoverageIgnore
     */
    public function setFeeObject($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Return attribute object
     *
     * @return Attribute
     */
    public function getFeeObject()
    {
        if (null === $this->fee) {
            return $this->_coreRegistry->registry('mageworx_multifees_fee');
        }

        return $this->fee;
    }

    /**
     * Preparing default form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $feeObject = $this->getFeeObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('apply_fieldset', ['legend' => __('Fee Options')]);

        $fieldset->addField(
            'input_type',
            'select',
            [
                'name'   => 'input_type',
                'label'  => __('Input Type'),
                'title'  => __('Input Type'),
                'values' => $this->inputTypeOptions->toOptionArray()
            ]
        );

        $fieldset->addField(
            'is_onetime',
            'select',
            [
                'name'   => 'is_onetime',
                'label'  => __('One-time'),
                'values' => $this->yesnoFactory->create()->toArray(),
                'value'  => '1'
            ]
        );

        $fieldset->addField(
            'applied_totals',
            'MageWorx\MultiFees\Data\Form\Element\FlexibleMultiselect',
            [
                'name'   => 'applied_totals[]',
                'label'  => __('Apply Fee To'),
                'title'  => __('Apply Fee To'),
                'values' => $this->appliedTotalsOptions->toOptionArray(),
                'note'   => 'For percent price type only'
            ]
        );

        $form->addValues($feeObject->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Processing block html after rendering
     * Adding js block to the end of this block
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $jsScripts = $this->getLayout()->createBlock('Magento\Eav\Block\Adminhtml\Attribute\Edit\Js')->toHtml();

        return $html . $jsScripts;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Options');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
