<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\Events\Model\Event\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */

class IsRecurrWeekly implements OptionSourceInterface
{
    protected $eventRecurr;
  
    public function __construct(\FME\Events\Model\Event $eventRecurr)
    {
        $this->eventRecurr = $eventRecurr;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->eventRecurr->getRecurrStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
