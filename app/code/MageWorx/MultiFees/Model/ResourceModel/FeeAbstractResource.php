<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

abstract class FeeAbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const FEE_ENTITY_TYPE = null;

    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var bool
     */
    protected $issetCheckboxOptionDefault = false;

    /**
     * @var bool
     */
    protected $missNextIsDefaultOptionValue = false;

    /**
     * Fee constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->dateTime      = $dateTime;
        $this->entityManager = $entityManager;
        $this->metadataPool  = $metadataPool;
    }

    /**
     * Get meta data of corresponding entity
     *
     * @return EntityMetadataInterface
     * @throws \Exception
     */
    abstract public function getCorrespondingMetaData();

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_multifees_fee', 'fee_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->getCorrespondingMetaData()->getEntityConnection();
    }

    /**
     * We find fee only by ID
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if ($value) {
            $this->entityManager->load($object, $value);
            $this->afterLoad($object);
        }

        return $this;
    }

    /**
     * Perform actions after entity load
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function afterLoad(\Magento\Framework\DataObject $object)
    {
        /** @var \MageWorx\MultiFees\Model\AbstractFee $object */
        parent::afterLoad($object);
//        $object->afterLoad();
    }

    /**
     * Perform actions before entity save
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave(\Magento\Framework\DataObject $object)
    {
        /** @var \MageWorx\MultiFees\Model\AbstractFee $object */
        $object->beforeSave();
        parent::beforeSave($object);
    }

    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {
        $this->beforeSave($object);
        $this->entityManager->save($object);
        $this->afterSave($object);

        return $this;
    }

    /**
     * Perform actions after entity save
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function afterSave(\Magento\Framework\DataObject $object)
    {
        /** @var \MageWorx\MultiFees\Model\AbstractFee $object */
        $object->afterSave();
        parent::afterSave($object);
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->beforeDelete($object);
        $this->entityManager->delete($object);
        $this->afterDelete($object);

        return $this;
    }

    /**
     * Save additional fee data after save fee
     *
     * @param \MageWorx\MultiFees\Model\AbstractFee|AbstractModel $object
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb|$this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _afterSave(AbstractModel $object)
    {
        $this->saveOption($object);

        return parent::_afterSave($object);
    }

    /**
     * @param $feeId
     * @return array
     * @throws \Exception
     */
    public function lookupTranslatedStrings($feeId)
    {
        $connection     = $this->getConnection();
        $entityMetadata = $this->getCorrespondingMetaData();

        $select = $connection->select()
                             ->from($this->getTable('mageworx_multifees_fee_language'))
                             ->where($entityMetadata->getIdentifierField() . ' = :fee_id');

        return $connection->fetchAll($select, ['fee_id' => (int)$feeId]);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $feeId
     * @return array
     * @throws \Exception
     */
    public function lookupStoreIds($feeId)
    {
        $connection     = $this->getConnection();
        $entityMetadata = $this->getCorrespondingMetaData();

        $select = $connection->select()
                             ->from($this->getTable('mageworx_multifees_fee_store'), 'store_id')
                             ->where($entityMetadata->getIdentifierField() . ' = :fee_id');

        return $connection->fetchCol($select, ['fee_id' => (int)$feeId]);
    }

    /**
     * Retrieve customer group ids of specified item is assigned
     *
     * @param int $feeId
     * @return array
     * @throws \Exception
     */
    public function lookupCustomerGroupIds($feeId)
    {
        $connection     = $this->getConnection();
        $entityMetadata = $this->getCorrespondingMetaData();

        $select = $this->getConnection()->select()
                       ->from($this->getTable('mageworx_multifees_fee_customer_group'), ['customer_group_id'])
                       ->where($entityMetadata->getIdentifierField() . ' = :fee_id');

        return $connection->fetchCol($select, ['fee_id' => (int)$feeId]);
    }

    /**
     * Save fee options
     *
     * @param \MageWorx\MultiFees\Model\AbstractFee|AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveOption(\MageWorx\MultiFees\Model\AbstractFee $object)
    {
        $option = $object->getOption();

        if (!is_array($option)) {
            return $this;
        }

        if (isset($option['value'])) {
            $this->_processAttributeOptions($object, $option);
        }

        return $this;
    }

    /**
     * Save changes of fee options, return obtained default value
     *
     * @param EntityAttribute|AbstractModel $object
     * @param array $option
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processAttributeOptions($object, $option)
    {
        foreach ($option['value'] as $optionId => $values) {
            if ($this->_deleteAttributeOption($object, $optionId, $option)) {
                unset($option['value'][$optionId]);
            }
        }

        $i   = 0;
        $len = count($option['value']);

        foreach ($option['value'] as $optionId => $values) {
            $isLastOption = ($i == $len - 1);
            $intOptionId  = $this->_updateAttributeOption($object, $optionId, $option, $isLastOption);
            $this->_checkDefaultOptionValue($values);
            $this->_updateAttributeOptionValues($intOptionId, $values);

            $i++;
        }
    }

    /**
     *
     * @param EntityAttribute|AbstractModel $object
     * @param string $optionId
     * @param array $option
     * @return boolean
     */
    protected function _deleteAttributeOption($object, $optionId, $option)
    {
        $connection  = $this->getConnection();
        $table       = $this->getTable('mageworx_multifees_fee_option');
        $intOptionId = (int)$optionId;

        if (!empty($option['delete'][$optionId])) {
            if ($intOptionId) {
                $connection->delete($table, ['fee_option_id = ?' => $intOptionId]);
            }

            return true;
        }

        return false;
    }

    /**
     * Save option records
     *
     * @param \MageWorx\MultiFees\Model\AbstractFee $object
     * @param int $optionId
     * @param array $option
     * @param boolean $isLastOption
     * @return int|bool
     */
    protected function _updateAttributeOption($object, $optionId, $option, $isLastOption)
    {
        $connection  = $this->getConnection();
        $table       = $this->getTable('mageworx_multifees_fee_option');
        $intOptionId = (int)$optionId;

        $price     = empty($option['price'][$optionId]) ? 0 : $option['price'][$optionId];
        $priceType = empty($option['price_type'][$optionId]) ? null : $option['price_type'][$optionId];
        $position  = empty($option['position'][$optionId]) ? 0 : $option['position'][$optionId];

        if (in_array($object->getInputType(), [$object::FEE_INPUT_TYPE_RADIO, $object::FEE_INPUT_TYPE_DROP_DOWN])) {
            if (empty($option['is_default'][$optionId]) && $object->getRequired()) {
                if ($isLastOption && !$this->missNextIsDefaultOptionValue) {
                    $isDefault = 1;
                } else {
                    $isDefault = 0;
                }
            } else {
                if ($this->missNextIsDefaultOptionValue) {
                    $isDefault = 0;
                } else {
                    $isDefault                          = $option['is_default'][$optionId];
                    $this->missNextIsDefaultOptionValue = $isDefault;
                }
            }
        } else {
            if (isset($option['is_default'][$optionId])) {
                $isDefault = $option['is_default'][$optionId];
            } else {
                $isDefault = 0;
            }

            if ($isDefault) {
                $this->issetCheckboxOptionDefault = true;
            } else {
                if ($isLastOption && !$this->issetCheckboxOptionDefault && $object->getRequired()) {
                    $isDefault = 1;
                }
            }
        }

        $data = [
            'fee_id'     => $object->getId(),
            'price'      => $price,
            'price_type' => $priceType,
            'position'   => $position,
            'is_default' => $isDefault
        ];

        if (!$intOptionId) {
            $data['fee_id'] = $object->getId();
            $connection->insert($table, $data);
            $intOptionId = $connection->lastInsertId($table);
        } else {
            $data = [
                'price'      => $price,
                'price_type' => $priceType,
                'position'   => $position,
                'is_default' => $isDefault
            ];

            $where = ['fee_option_id = ?' => $intOptionId];
            $connection->update($table, $data, $where);
        }

        return $intOptionId;
    }

    /**
     * Update attribute default value
     *
     * @param EntityAttribute|AbstractModel $object
     * @param int|string $optionId
     * @param int $intOptionId
     * @param array $defaultValue
     * @return void
     */
    protected function _updateDefaultValue($object, $optionId, $intOptionId, &$defaultValue)
    {
        if (in_array($optionId, $object->getDefault())) {
            $frontendInput = $object->getFrontendInput();
            $frontendInput = 'select';

            if ($frontendInput === 'multiselect') {
                $defaultValue[] = $intOptionId;
            } elseif ($frontendInput === 'select') {
                $defaultValue = [$intOptionId];
            }
        }
    }

    /**
     * Check default option value presence
     *
     * @param array $values
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _checkDefaultOptionValue($values)
    {
        if (!isset($values[0])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Default option value is not defined'));
        }
    }

    /**
     * Save option values records per store
     *
     * @param int $optionId
     * @param array $values
     * @return void
     */
    protected function _updateAttributeOptionValues($optionId, $values)
    {
        $connection = $this->getConnection();
        $table      = $this->getTable('mageworx_multifees_fee_option_language');

        $connection->delete($table, ['fee_option_id = ?' => $optionId]);

        $stores = $this->_storeManager->getStores(true);
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if (!empty($values[$storeId]) || isset($values[$storeId]) && $values[$storeId] == '0') {
                $data = ['fee_option_id' => $optionId, 'store_id' => $storeId, 'title' => $values[$storeId]];
                $connection->insert($table, $data);
            }
        }
    }
}
