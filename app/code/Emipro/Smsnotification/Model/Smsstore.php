<?php
/**
 * {{Emipro}}_{{Smsnotification}} extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Emipro
 *                     @package   Emipro_Smsnotification
 *                     @copyright Copyright (c) 2015
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Emipro\Smsnotification\Model;

/**
 * @method Smsevent setSmsTitle($smsTitle)
 * @method Smsevent setSmsEvents($smsEvents)
 * @method Smsevent setSmsContent($smsContent)
 * @method mixed getSmsTitle()
 * @method mixed getSmsEvents()
 * @method mixed getSmsContent()
 * @method Smsevent setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Smsevent setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class Smsstore extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'emipro_smsnotification_smsstore';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'emipro_smsnotification_smsstore';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'emipro_smsnotification_smsstore';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Emipro\Smsnotification\Model\ResourceModel\Smsstore');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
    
}
