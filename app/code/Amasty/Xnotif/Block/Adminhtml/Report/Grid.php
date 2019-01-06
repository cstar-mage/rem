<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Adminhtml\Report;

/**
 * Class Grid
 * @package Amasty\Xnotif\Block\Adminhtml\Report
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    protected function _prepareCollection()
    {
        $entityTable = $this->resource->getTableName(
            'catalog_product_entity'
        );
        $customerTable = $this->resource->getTableName(
            'customer_entity'
        );

        $collection = $this->collectionFactory->create();
        $select = $collection->getSelect();

        $select->joinInner(
            ['ent' => $entityTable],
            'main_table.product_id = ent.entity_id',
            [
                'first_d' => 'main_table.add_date',
                'sku'
            ]
        );

        $select->joinLeft(
            ['cust' => $customerTable],
            'main_table.customer_id = cust.entity_id',
            [
                'final_email' => 'CONCAT(COALESCE(`main_table`.`email`,""), COALESCE(`cust`.`email`,""))'
            ]
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('EMAIL'),
                'index' => 'final_email',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'first_d',
            [
                'header' => __('Subscription Date'),
                'index' => 'first_d',
                'type' => 'datetime',
                'width' => '150px',
                'gmtoffset' => true,
                'default' => ' ---- ',
                'filter' => false,
            ]
        );

        return parent::_prepareColumns();
    }
}
