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

class IsRepeatInterval implements OptionSourceInterface
{
    protected $eventRecurrInterval;
  
    public function __construct(\FME\Events\Model\Event $eventRecurrInterval)
    {
        $this->eventRecurrInterval = $eventRecurrInterval;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->eventRecurrInterval->getRecurrIntervals();
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
