<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

namespace Amasty\Acart\Cron;

use Magento\Framework\App\ResourceConnection;

class ClearHistory
{
    protected $_ruleQuoteCollectionFactory;
    protected $_dateTime;
    protected $_date;

    public function __construct(
        \Amasty\Acart\Model\ResourceModel\RuleQuote\CollectionFactory $ruleQuoteCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ){
        $this->_ruleQuoteCollectionFactory = $ruleQuoteCollectionFactory;
        $this->_dateTime = $dateTime;
        $this->_date = $date;
    }

    public function execute()
    {
        $formattedDate = $this->_dateTime->formatDate($this->_date->gmtTimestamp() - (60 * 60 * 24 * 90));

        $collection = $this->_ruleQuoteCollectionFactory->create()
            ->addFieldToFilter('created_at', ['lt' => $formattedDate])
            ->addFieldToFilter('status', \Amasty\Acart\Model\RuleQuote::STATUS_COMPLETE);

        foreach($collection as $coupon)
        {
            $coupon->delete();
        }
    }
}