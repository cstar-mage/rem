<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel\Fee\Relation\CustomerGroup;

/**
 * Class ReadHandler
 */
class ReadHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
{
    /**
     * @param object|\MageWorx\MultiFees\Model\AbstractFee $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $customerGroupIds = $entity->getResource()->lookupCustomerGroupIds((int)$entity->getId());
            $entity->setData('customer_group_ids', $customerGroupIds);
        }

        return $entity;
    }
}
