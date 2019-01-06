<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\ResourceModel\OptionSetting;

use Magento\Framework\DB\Select;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection protected constructor
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\ShopbyBase\Model\OptionSetting::class,
            \Amasty\ShopbyBase\Model\ResourceModel\OptionSetting::class
        );
        $this->_idFieldName = $this->getResource()->getIdFieldName();
    }

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return $this
     */
    public function addLoadParams($filterCode, $optionId, $storeId)
    {
        $listStores = [0];
        if ($storeId > 0) {
            $listStores[] = $storeId;
        }
        $this->addFieldToFilter('filter_code', $filterCode)
            ->addFieldToFilter('value', $optionId)
            ->addFieldToFilter('store_id', $listStores)
            ->addOrder('store_id', self::SORT_ORDER_DESC);
        return $this;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getHardcodedAliases($storeId)
    {
        $select = $this->getSelect();
        $select->reset(Select::COLUMNS);
        $select->columns('filter_code');
        $select->columns('value');
        if ($storeId === 0) {
            $select->columns('url_alias');
            $select->where('`url_alias` <> ""');
            $select->where('`store_id` = ' . $storeId);
        } else {
            $urlAlias = 'IFNULL(`current_table`.`url_alias`, `main_table`.`url_alias`)';
            $select->joinLeft(
                ['current_table' => $this->getMainTable()],
                '`current_table`.`value` = `main_table`.`value`'
                . " AND `current_table`.`store_id` = $storeId"
                . ' AND `current_table`.`url_alias` <> ""',
                ['url_alias' => $urlAlias]
            );
            $select->where('`main_table`.`store_id` = ?', 0);
            $select->where("$urlAlias  <> ?", '');
        }

        $data = $select->getConnection()->fetchAll($select);
        return $data;
    }

    //@todo remove in one release
//    /**
//     * for some reason returned null
//     */
//    public function getIdFieldName()
//    {
//        return 'option_setting_id';
//    }
}
