<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel\Fee\Relation\Language;

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
            $data = $entity->getResource()->lookupTranslatedStrings((int)$entity->getId());

            if ($data) {
                $namesArray                 = [];
                $descriptionsArray          = [];
                $customerMessageTitlesArray = [];
                $dateFieldTitlesArray       = [];
                foreach ($data as $row) {
                    if ($row['store_id'] == 0) {
                        $entity->setTitle($row['title'])
                               ->setDescription($row['description'])
                               ->setCustomerMessageTitle($row['customer_message_title'])
                               ->setDateFieldTitle($row['date_field_title']);
                    } else {
                        $namesArray[$row['store_id']]                 = $row['title'];
                        $descriptionsArray[$row['store_id']]          = $row['description'];
                        $customerMessageTitlesArray[$row['store_id']] = $row['customer_message_title'];
                        $dateFieldTitlesArray[$row['store_id']]       = $row['date_field_title'];
                    }
                }
                $entity->setStoreNames($namesArray)
                       ->setStoreDescriptions($descriptionsArray)
                       ->setStoreCustomerMessageTitles($customerMessageTitlesArray)
                       ->setStoreDateFieldTitles($dateFieldTitlesArray);
            }
        }

        return $entity;
    }
}
