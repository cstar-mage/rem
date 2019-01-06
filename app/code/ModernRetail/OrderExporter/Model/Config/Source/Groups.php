<?php

namespace ModernRetail\OrderExporter\Model\Config\Source;

class Groups implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $systemStore = $objectManager->get('\Magento\Store\Model\System\Store');

//        return $systemStore->getWebsiteValuesForForm(false, false);


        $result = [];

        foreach ($systemStore->getGroupCollection() as $group) {
            $result[] = ['value' => $group->getId(), 'label' => $group->getName()];
        }

        return $result;
    }
}