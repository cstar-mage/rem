<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel\Fee\Relation\Language;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{


    /**
     * @param object|\MageWorx\MultiFees\Model\AbstractFee $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $resource       = $entity->getResource();
        $entityMetadata = $resource->getCorrespondingMetaData();
        $linkField      = $entityMetadata->getLinkField();
        $connection     = $entityMetadata->getEntityConnection();

        $oldData = $resource->lookupTranslatedStrings((int)$entity->getId());

        $newData    = [];
        $newData[0] = [
            'title'                  => $entity->getTitle(),
            'description'            => $entity->getDescription(),
            'customer_message_title' => $entity->getCustomerMessageTitle(),
            'date_field_title'       => $entity->getDateFieldTitle()
        ];

        $languageArguments = [
            'store_fee_names',
            'store_fee_descriptions',
            'store_fee_customer_message_titles',
            'store_fee_date_field_titles'
        ];

        foreach ($languageArguments as $argument) {
            if ($entity->getData($argument)) {
                foreach ($entity->getData($argument) as $storeId => $value) {
                    $dbField                     = $this->getArgument($argument);
                    $newData[$storeId][$dbField] = $value;
                }
            }
        }

        $deleteStoreIds = array_keys($oldData);
        $insertStoreIds = array_keys($newData);
        $table          = $resource->getTable('mageworx_multifees_fee_language');

        if ($deleteStoreIds) {
            $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'store_id IN (?)'   => $deleteStoreIds,
            ];
            $connection->delete($table, $where);
        }

        if ($insertStoreIds) {
            $data = [];
            foreach ($insertStoreIds as $storeId) {
                $data[] = [
                    $linkField               => (int)$entity->getData($linkField),
                    'store_id'               => (int)$storeId,
                    'title'                  => $newData[$storeId]['title'],
                    'description'            => $newData[$storeId]['description'],
                    'customer_message_title' => $newData[$storeId]['customer_message_title'],
                    'date_field_title'       => $newData[$storeId]['date_field_title'],
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }

    /**
     * @param $value
     * @param bool $isDbKey
     * @return false|int|mixed|string
     */
    protected function getArgument($value, $isDbKey = true)
    {
        $compare = [
            'title'                  => 'store_fee_names',
            'description'            => 'store_fee_descriptions',
            'customer_message_title' => 'store_fee_customer_message_titles',
            'date_field_title'       => 'store_fee_date_field_titles'
        ];

        if ($isDbKey) {
            return array_search($value, $compare) ? array_search($value, $compare) : $value;
        }

        return isset($compare[$value]) ? $compare[$value] : $value;
    }
}
