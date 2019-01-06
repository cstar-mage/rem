<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Config\Model\Config\Source\Yesno as BooleanOptions;
use MageWorx\MultiFees\Model\Fee\Source\Status as StatusOptions;
use MageWorx\MultiFees\Model\Fee\Source\Type as TypeOptions;
use Magento\Tax\Model\TaxClass\Source\Product as ProductTaxClassSource;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Model\Config\Source\Shipping\Methods as ShippingMethodsConfig;
use MageWorx\MultiFees\Model\Config\Source\Payment\Methods as PaymentMethodsConfig;

class Main extends GenericForm implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var BooleanOptions
     */
    protected $booleanOptions;

    /**
     * @var TypeOptions
     */
    protected $typeOptions;

    /**
     * @var InputTypeOptions
     */
    protected $inputTypeOptions;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ObjectConverter
     */
    protected $objectConverter;

    /**
     * @var StatusOptions
     */
    protected $statusOptions;

    /**
     * @var ProductTaxClassSource
     */
    protected $productTaxClassSource;

    /**
     * @var ShippingMethodsConfig
     */
    protected $shippingConfig;

    /**
     * @var \MageWorx\MultiFees\Model\Config\Source\Payment\Methods
     */
    protected $paymentMethodsConfig;

    /**
     * Main constructor.
     *
     * @param Store $systemStore
     * @param BooleanOptions $booleanOptions
     * @param StatusOptions $statusOptions
     * @param TypeOptions $typeOptions
     * @param ProductTaxClassSource $productTaxClassSource
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param \MageWorx\MultiFees\Model\Config\Source\Shipping\Methods|\MageWorx\ShippingRules\Model\Config\Source\Shipping\Methods $shippingMethodsConfig
     * @param \MageWorx\MultiFees\Model\Config\Source\Payment\Methods $paymentMethodsConfig
     * @param array $data
     */
    public function __construct(
        Store $systemStore,
        BooleanOptions $booleanOptions,
        StatusOptions $statusOptions,
        TypeOptions $typeOptions,
        ProductTaxClassSource $productTaxClassSource,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ObjectConverter $objectConverter,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ShippingMethodsConfig $shippingMethodsConfig,
        PaymentMethodsConfig $paymentMethodsConfig,
        array $data = []
    ) {
        $this->systemStore           = $systemStore;
        $this->booleanOptions        = $booleanOptions;
        $this->typeOptions           = $typeOptions;
        $this->productTaxClassSource = $productTaxClassSource;
        $this->groupRepository       = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter       = $objectConverter;
        $this->statusOptions         = $statusOptions;
        $this->shippingConfig        = $shippingMethodsConfig;
        $this->paymentMethodsConfig  = $paymentMethodsConfig;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return \Magento\Backend\Block\Widget\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \MageWorx\MultiFees\Model\Fees $fee */
        $fee = $this->_coreRegistry->registry('mageworx_multifees_fee');
        /** @var \Magento\Framework\Data\Form $form */
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
        $form->addValues($fee->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add main fields to main fieldset. Common for all fee types.
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param FeeInterface $fee
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addCommonFieldsForAllEntities(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        \MageWorx\MultiFees\Api\Data\FeeInterface $fee
    ) {
        if ($fee->getId()) {
            $fieldset->addField(
                'fee_id',
                'hidden',
                ['name' => 'fee_id']
            );
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name'     => 'title',
                'label'    => __('Name'),
                'title'    => __('Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name'     => 'description',
                'label'    => __('Description'),
                'title'    => __('Description'),
                'required' => false,
            ]
        );

        $fieldset->addField(
            'type',
            'hidden',
            [
                'label'    => __('Fee Type'),
                'title'    => __('Fee Type'),
                'name'     => 'type',
                'required' => false,
                //                'options'  => $this->typeOptions->toArray(), // @refactoring
            ]
        );

        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $fieldset->addField(
            'customer_group_ids',
            'MageWorx\MultiFees\Data\Form\Element\FlexibleMultiselect',
            [
                'name'     => 'customer_group_ids[]',
                'label'    => __('Customer Groups'),
                'title'    => __('Customer Groups'),
                'required' => true,
                'values'   => $this->objectConverter->toOptionArray($groups, 'id', 'code')
            ]
        );

        $fieldset->addField(
            'required',
            'select',
            [
                'label'    => __('Required'),
                'name'     => 'required',
                'index'    => 'required',
                'required' => false,
                'options'  => $this->booleanOptions->toArray(),
                'note'     => __(
                    'Note: If the "Required" field is set to "Yes", select at least one "Is default" option.'
                )
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label'   => __('Status'),
                'title'   => __('Status'),
                'name'    => 'status',
                'options' => $this->statusOptions->toArray()
            ]
        );

        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name'  => 'stores[]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                ]
            );
            $fee->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name'     => 'stores[]',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->systemStore->getStoreValuesForForm(false, true),
                ]
            );
        }

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'label'    => __('Sort Order'),
                'name'     => 'sort_order',
                'index'    => 'sort_order',
                'class'    => 'not-negative-amount integer',
                'required' => false,
            ]
        );

        $fieldset->addField(
            'tax_class_id',
            'select',
            [
                'label'  => __('Tax Class'),
                'name'   => 'tax_class_id',
                'values' => $this->productTaxClassSource->toOptionArray()
            ]
        );

        return $fieldset;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Fee Information');
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
     * Return all payment methods as option array
     *
     * @return array
     */
    protected function getPaymentMethods()
    {
        return $this->paymentMethodsConfig->toOptionArray();
    }
}
