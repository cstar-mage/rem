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
namespace Emipro\Smsnotification\Controller\Adminhtml\Smsevent;
use Magento\Framework\Controller\ResultFactory;
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Mass Action Filter
     * 
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * Collection Factory
     * 
     * @var \Emipro\Smsnotification\Model\ResourceModel\Smsevent\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * constructor
     * 
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Emipro\Smsnotification\Model\ResourceModel\Smsevent\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Emipro\Smsnotification\Model\ResourceModel\Smsevent\CollectionFactory $collectionFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }


    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

    public function _validateMassStatus(array $productIds, $status)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        
            foreach ($productIds as $value) {
                if (!$objectManager->create('Emipro\Smsnotification\Model\Smsevent')->load($value)->getData()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Please make sure to define values for all processed Smsevent.')
                    );
                }
                else
                {
                    $smsevent = $objectManager->create('Emipro\Smsnotification\Model\Smsevent')->load($value);
                    $smsevent->setIsActive($status)->save();
                }
            }
        
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());

        $productIds = $collection->getAllIds();
        $status = (int) $this->getRequest()->getParam('status');
        try 
        {
            $this->_validateMassStatus($productIds, $status);
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', count($productIds)));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_getSession()->addException($e, __('Something went wrong while updating the product(s) status.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
