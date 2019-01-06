<?php

namespace ModernRetail\OrderExporter\Model\Config\Source;

class Stores implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $systemStore = $objectManager->get('\Magento\Store\Model\System\Store');

//        $helper = $objectManager->create('ModernRetail\OrderExporter\Helper\Data');
//        $allowedGroups =  $helper->getAllowedGroups();
//
//        if (!$allowedGroups) {
//            return;
//        }

        $result = [];

        foreach ($systemStore->getGroupCollection() as $group) {
//            $group_id = $group->getId();
//            if (!in_array($group_id, $allowedGroups)) {
//                continue;
//            }

            $group_name = $group->getName();
            foreach ($group->getStores() as $store) {
                $result[] = ['value' => $store['store_id'], 'label' => $store['name']."($group_name)"];
            }
        }

        return $result;
    }
}