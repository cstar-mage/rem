<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Labels;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Amasty\Label\Model\ResourceModel\Labels\CollectionFactory;

/**
 * Class MassDelete
 */
abstract class MassActionAbstract extends \Magento\Backend\App\Action
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\Label\Model\Indexer\LabelIndexer
     */
    private $labelIndexer;

    /**
     * MassActionAbstract constructor.
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param \Amasty\Label\Model\Indexer\LabelIndexer $labelIndexer
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Amasty\Label\Model\Indexer\LabelIndexer $labelIndexer
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->labelIndexer = $labelIndexer;
    }

    abstract public function execute();

    /**
     * @return \Amasty\Label\Model\ResourceModel\Labels\Collection
     */
    protected function getCollection()
    {
        $ids = $this->getRequest()->getParam('label_ids');
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('label_id', $ids);

        return $collection;
    }

    /**
     * invalidate amasty label index
     */
    protected function invalidateIndex()
    {
        $this->labelIndexer->invalidateIndex();
    }
}
