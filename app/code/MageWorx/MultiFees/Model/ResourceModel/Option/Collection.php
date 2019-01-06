<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel\Option;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $coreResource;

    /**
     * @var string
     */
    protected $optionLanguageTable = 'mageworx_multifees_fee_option_language';

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->coreResource = $resourceConnection;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('MageWorx\MultiFees\Model\Option', 'MageWorx\MultiFees\Model\ResourceModel\Option');
    }

    /**
     * @var string
     */
    protected $_idFieldName = 'fee_option_id';

    /**
     * @param string $sort
     * @return $this
     */
    public function sortByPosition($sort = 'DESC')
    {
        $this->getSelect()->order('main_table.position ' . $sort)->order('main_table.fee_option_id ' . $sort);

        return $this;
    }

    /**
     * @param $feeId
     * @return $this
     */
    public function addFeeFilter($feeId)
    {
        $this->getSelect()->where('main_table.fee_id = ?', $feeId);

        return $this;
    }

    /**
     * @param $feeOptionId
     * @return $this
     */
    public function addFeeOptionFilter($feeOptionId)
    {
        $this->getSelect()->where('main_table.fee_option_id = ?', $feeOptionId);

        return $this;
    }

    /**
     * Add store filter to collection
     *
     * @param int $storeId
     * @param bool $useDefaultValue
     * @return $this
     */
    public function addStoreLanguage($storeId = null, $useDefaultValue = true)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $connection    = $this->getConnection();
        $joinCondition = $connection->quoteInto(
            'fosl.fee_option_id = main_table.fee_option_id AND fosl.store_id = ?',
            $storeId
        );

        if ($useDefaultValue) {
            $this->getSelect()->join(
                ['fodl' => $this->getTable($this->optionLanguageTable)],
                'fodl.fee_option_id = main_table.fee_option_id',
                ['default_title' => 'title']
            )->joinLeft(
                ['fosl' => $this->getTable($this->optionLanguageTable)],
                $joinCondition,
                [
                    'store_default_title' => 'title',
                    'title'               => $connection->getCheckSql(
                        'fosl.fee_option_lang_id > 0',
                        'fosl.title',
                        'fodl.title'
                    )
                ]
            )->where(
                'fodl.store_id = ?',
                0
            );
        } else {
            $this->getSelect()->joinLeft(
                ['fosl' => $this->getTable($this->optionLanguageTable)],
                $joinCondition,
                'title'
            )->where(
                'fosl.store_id = ?',
                $storeId
            );
        }

        $this->setOrder('title', self::SORT_ORDER_ASC);

        return $this;
    }
}
