<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model\Indexer;

use Amasty\Label\Api\Data\LabelIndexInterface;
use Amasty\Label\Helper\Config;
use Amasty\Label\Model\Labels;
use Amasty\Label\Model\ResourceModel\Index;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Label\Model\ResourceModel\Labels\CollectionFactory;
use Amasty\Label\Model\ResourceModel\Labels\Collection as LabelCollection;
use Amasty\Label\Api\Data\LabelInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class IndexBuilder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Index
     */
    private $indexResource;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * IndexBuilder constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param Index $indexResource
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param Config $config
     * @param int $batchCount
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Index $indexResource,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        Config $config,
        $batchCount = 1000
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->indexResource = $indexResource;
        $this->productRepository = $productRepository;
        $this->batchCount = $batchCount;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->config = $config;
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     * @throws LocalizedException
     * @return void
     * @api
     */
    public function reindexByProductIds(array $ids)
    {
        try {
            $this->cleanByProductIds($ids);
            $this->doReindexByProductIds($ids);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __("Amasty label indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * Reindex by label ids
     *
     * @param array $ids
     * @throws LocalizedException
     * @return void
     * @api
     */
    public function reindexByLabelIds($ids)
    {
        try {
            $this->cleanByLabelIds($ids);
            $this->doReindexByLabelIds($ids);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __("Amasty label indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param $id
     * @throws LocalizedException
     */
    public function reindexByProductId($id)
    {
        $this->reindexByProductIds([$id]);
    }

    /**
     * @param $id
     * @throws LocalizedException
     */
    public function reindexByLabelId($id)
    {
        $this->reindexByLabelIds([$id]);
    }

    /**
     * @param $productIds
     * @throws LocalizedException
     */
    private function cleanByProductIds($productIds)
    {
        $this->indexResource->cleanByProductIds($productIds);
    }

    /**
     * @param $labelIds
     * @throws LocalizedException
     */
    private function cleanByLabelIds($labelIds)
    {
        $this->indexResource->cleanByLabelIds($labelIds);
    }

    /**
     * @param array $ids
     * @return $this
     */
    private function doReindexByProductIds($ids)
    {
        foreach ($this->getLabelCollection() as $label) {
            $this->reindexByLabelAndProductIds($label, $ids);
        }

        return $this;
    }

    /**
     * @param array $ids
     * @return $this
     */
    private function doReindexByLabelIds($ids)
    {
        foreach ($this->getLabelCollection($ids) as $label) {
            $this->reindexByLabelAndProductIds($label);
        }

        return $this;
    }

    /**
     * @param Labels $label
     * @param null $ids
     * @return $this
     */
    private function reindexByLabelAndProductIds(Labels $label, $ids = null)
    {
        if (!$this->config->isUseIndex()) {
            return $this;
        }

        $matchedProductIds = $label->getLabelMatchingProductIds();
        if ($ids == null) {
            if ($matchedProductIds !== null) {
                $ids = array_keys($matchedProductIds);
            } else {
                $ids = $this->getAllProductIds();
            }
        }
        $labelStoreIds = $label->getStoreIds();
        if (!$labelStoreIds || !is_array($labelStoreIds)) {
            return $this;
        }

        $rows = [];
        $count = 0;

        foreach ($ids as $productId) {
            $productId = (int)$productId;
            if ($matchedProductIds !== null && array_key_exists($productId, $matchedProductIds)) {
                $matchedStores = array_keys($matchedProductIds[$productId]);
                $stores = array_intersect($matchedStores, $labelStoreIds);
                if ($stores) {
                    $applied = $this->applyLabel($label, $productId, $stores);
                    $count += count($applied);
                    $rows = array_merge($rows, $applied);

                    if ($count >= $this->batchCount) {
                        $this->connection->insertMultiple(
                            $this->resource->getTableName(Index::AMASTY_LABEL_INDEX_TABLE),
                            $rows
                        );

                        $rows  = [];
                        $count = 0;
                    }

                }
            }
        }

        if (!empty($rows)) {
            $this->connection->insertMultiple(
                $this->resource->getTableName(Index::AMASTY_LABEL_INDEX_TABLE),
                $rows
            );
        }

        return $this;
    }

    /**
     * @param Labels $label
     * @param $productEntityId
     * @param $storeIds
     * @return array
     */
    private function applyLabel(Labels $label, $productEntityId, $storeIds)
    {
        $rows  = [];
        $productEntityId = (int)$productEntityId;
        if (!$productEntityId) {
            return $rows;
        }

        $validOnStores = [];

        foreach ($storeIds as $storeId) {
            try {
                $product = $this->productRepository->getById($productEntityId, false, $storeId);
            } catch (NoSuchEntityException $exception) {
                continue;
            }
            $label->init($product);
            if ($label->isApplicableForCustomRules()) {
                $validOnStores[$storeId] = true;
            }
        }

        if (!count($validOnStores)) {
            return $rows;
        }

        /* Note: Label can be for All Store View (store_ids = array(0 => '0')) */
        foreach (array_keys($validOnStores) as $storeId) {
            $rows[] = [
                LabelIndexInterface::PRODUCT_ID => $productEntityId,
                LabelIndexInterface::LABEL_ID => $label->getId(),
                LabelIndexInterface::STORE_ID => $storeId
            ];

        }

        return $rows;
    }

    /**
     * Full reindex
     *
     * @throws LocalizedException
     * @return void
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param null $labelIds
     * @return mixed
     */
    private function getLabelCollection($labelIds = null)
    {
        $collection = $this->collectionFactory->create()
            ->addActiveFilter();
        if ($labelIds) {
            $collection->addFieldToFilter(LabelInterface::LABEL_ID, ['in' => $labelIds]);
        }

        return $collection;
    }

    /**
     * @return $this
     */
    private function doReindexFull()
    {
        $this->indexResource->cleanAllIndex();

        foreach ($this->getLabelCollection() as $label) {
            $this->reindexByLabelAndProductIds($label);
        }

        return $this;
    }

    /**
     * @return array
     */
    private function getAllProductIds()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();

        return $collection->getAllIds();
    }

}
