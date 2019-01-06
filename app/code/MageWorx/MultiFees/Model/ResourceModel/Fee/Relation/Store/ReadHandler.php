<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel\Fee\Relation\Store;

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
            $stores = $entity->getResource()->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
        }

        return $entity;
    }
}
