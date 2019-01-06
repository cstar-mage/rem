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
namespace Emipro\Smsnotification\Model\ResourceModel;

class Smsevent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     * 
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    

    /**
     * constructor
     * 
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
        //\Emipro\Smsnotification\Model\SmsstoreFactory  $smsstoreFactory
    )
    {
        $this->_date = $date;
       // $this->smsstore=$smsstoreFactory;
        parent::__construct($context);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('emipro_smsnotification_smsevent', 'smsevent_id');
    }

    /**
     * Retrieves SMS Event SMS  Title from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getSmseventSms_titleById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'sms_title')
            ->where('smsevent_id = :smsevent_id');
        $binds = ['smsevent_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }
   
    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Emipro\Smsnotification\Model\Smsevent $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->_date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->_date->date());
        }
        return parent::_beforeSave($object);
    }
}
