<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model;

class Option extends \Magento\Framework\Model\AbstractModel
{
    const VALUE_IS_DEFAULT     = 1;
    const VALUE_IS_NOT_DEFAULT = 0;

    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageworx_multifees_fee_option';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $cacheTag = 'mageworx_multifees_fee_option';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageworx_multifees_fee_option';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MageWorx\MultiFees\Model\ResourceModel\Option');
        $this->setIdFieldName('fee_option_id');
    }
}
