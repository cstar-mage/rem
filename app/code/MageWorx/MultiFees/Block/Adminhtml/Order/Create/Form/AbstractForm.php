<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Order\Create\Form;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * MageWorx Fee Create Form Abstract Block
 *
 */
abstract class AbstractForm extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Form factory
     *
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * Data Form object
     *
     * @var \Magento\Framework\Data\Form
     */
    protected $form;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Prepare global layout
     * Add renderers to \Magento\Framework\Data\Form
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Element',
                $this->getNameInLayout() . '_element'
            )
        );
        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Fieldset',
                $this->getNameInLayout() . '_fieldset'
            )
        );
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return $this;
    }

    /**
     * Return Form object
     *
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        if ($this->form === null) {
            $this->form = $this->formFactory->create();
            $this->prepareForm();
        }

        return $this->form;
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    abstract protected function prepareForm();

    /**
     * Return array of additional form element types by type
     *
     * @example ['image' => 'Magento\Customer\Block\Adminhtml\Form\Element\Image']
     * @return array
     */
    protected function getAdditionalFormElementTypes()
    {
        return [];
    }

    /**
     * Return array of additional form element renderers by element id
     *
     * @example ['region' => $this->getLayout()->createBlock('Magento\Customer\Block\Adminhtml\Edit\Renderer\Region')]
     * @return array
     */
    protected function getAdditionalFormElementRenderers()
    {
        return [];
    }

    /**
     * Add additional data to form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return $this
     */
    protected function addAdditionalFormElementData(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this;
    }

    /**
     * Add rendering Fee options data to Form element
     *
     * @param $attributes
     * @param \Magento\Framework\Data\Form\AbstractForm $form
     * @return $this
     */
    protected function addAttributesToForm($attributes, \Magento\Framework\Data\Form\AbstractForm $form)
    {
        // add additional form types
        $types = $this->getAdditionalFormElementTypes();
        foreach ($types as $type => $className) {
            $form->addType($type, $className);
        }
        $renderers = $this->getAdditionalFormElementRenderers();

        foreach ($attributes as $attribute) {
            $inputType = $attribute['frontend_input'];
            if ($inputType) {
                $element = $form->addField(
                    $attribute['attribute_code'],
                    $inputType,
                    [
                        'name'     => $attribute['name'],
                        'label'    => $attribute['store_label'],
                        'class'    => $attribute['frontend_class'],
                        'required' => $attribute['is_required'],
                        'note'     => !empty($attribute['note']) ? $this->escapeHtml($attribute['note']) : '',
                    ]
                );

                $element->setEntityAttribute($attribute);
                $this->addAdditionalFormElementData($element);

                if (!empty($renderers[$attribute['attribute_code']])) {
                    $element->setRenderer($renderers[$attribute['attribute_code']]);
                }

                if (in_array($inputType, ['select', 'multiselect', 'radios', 'checkboxes'])) {
                    $options = isset($attribute['options']) ? $attribute['options'] : [];
                    $element->setValues($options);
                    $element->setValue($attribute['value']);
                } elseif ($inputType == 'date') {
                    $format = $this->_localeDate->getDateFormat(
                        \IntlDateFormatter::SHORT
                    );
                    $element->setDateFormat($format);
                }

                $element->setValue($attribute['value']);
            }
        }

        return $this;
    }
}
