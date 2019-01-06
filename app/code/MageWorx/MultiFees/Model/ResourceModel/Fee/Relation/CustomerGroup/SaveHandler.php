<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel\Fee\Relation\CustomerGroup;

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

        $oldCustomerGroups = $resource->lookupCustomerGroupIds((int)$entity->getId());
        $newCustomerGroups = (array)$entity->getCustomerGroupIds();
        if (empty($newCustomerGroups)) {
            $newCustomerGroups = (array)$entity->getCustomerGroupId();
        }

        $table  = $resource->getTable('mageworx_multifees_fee_customer_group');
        $delete = array_diff($oldCustomerGroups, $newCustomerGroups);
        if ($delete) {
            $where = [
                $linkField . ' = ?'        => (int)$entity->getData($linkField),
                'customer_group_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newCustomerGroups, $oldCustomerGroups);
        if ($insert) {
            $data = [];
            foreach ($insert as $customerGroupId) {
                $data[] = [
                    $linkField          => (int)$entity->getData($linkField),
                    'customer_group_id' => (int)$customerGroupId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
