<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model\ResourceModel;

use Amasty\Label\Api\Data\LabelIndexInterface;
use \Amasty\Label\Api\Data\LabelInterface as LabelInterface;

class Index extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const AMASTY_LABEL_INDEX_TABLE = 'amasty_label_index';

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::AMASTY_LABEL_INDEX_TABLE, 'index_id');
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIdsFromIndex($productId, $storeId)
    {
        $query = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), LabelInterface::LABEL_ID)
            ->distinct()
            ->where( LabelIndexInterface::PRODUCT_ID . ' = ?', $productId)
            ->where( LabelIndexInterface::STORE_ID . ' = ?', $storeId);

        return $this->getConnection()->fetchAll($query);
    }


    /**
     * this method is not used now. Can be helpful in future
     * @param $labelIds
     * @param $entityId
     * @param $field
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkValidProductFromIndex($labelIds, $entityId, $field)
    {
        $query = $this->getConnection()
            ->select()
            ->from(
                $this->getMainTable(),
                LabelInterface::LABEL_ID
            )
            ->distinct()
            ->where(
                LabelInterface::LABEL_ID . ' IN (?)',
                $labelIds
            )
            ->where($field . ' = :'. $field);

        return $this->getConnection()->fetchAll($query, [$field => $entityId]);
    }

    /**
     * @param $labelsIds
     */
    public function cleanByLabelIds($labelsIds)
    {
        $query = $this->getConnection()->deleteFromSelect(
            $this->getConnection()
                ->select()
                ->from($this->getMainTable(), LabelInterface::LABEL_ID)
                ->where(LabelInterface::LABEL_ID . ' IN (?)', $labelsIds),
            $this->getMainTable()
        );

        $this->getConnection()->query($query);
    }

    /**
     * @param $productIds
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cleanByProductIds($productIds)
    {
        $query = $this->getConnection()->deleteFromSelect(
            $this->getConnection()
                ->select()
                ->from($this->getMainTable(), LabelInterface::LABEL_ID)
                ->where(LabelIndexInterface::PRODUCT_ID . ' IN (?)', $productIds),
            $this->getMainTable()
        );

        $this->getConnection()->query($query);
    }

    /**
     * @param $labelId
     * @return int|void
     */
    public function getCountLabelIndexes($labelId)
    {
        return $this->getCountIndex($labelId);
    }

    /**
     * @param int|string $labelId
     * @param bool $guest
     * @return int|void
     */
    public function getCountIndex($labelId)
    {
        $query = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), LabelInterface::LABEL_ID)
            ->where(LabelInterface::LABEL_ID . ' IN (?)', $labelId);

        return count($this->getConnection()->fetchAll($query));
    }

    /**
     * @return $this
     */
    public function cleanAllIndex()
    {
        $this->getConnection()->truncateTable($this->getMainTable());

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function insertIndexData(array $data)
    {
        $this->getConnection()->insertMultiple($this->getMainTable(), $data);

        return $this;
    }
}
