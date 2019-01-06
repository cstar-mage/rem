<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Labels;

use Magento\Framework\App\Filesystem\DirectoryList;

class Duplicate extends \Amasty\Label\Controller\Adminhtml\Labels
{

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->labelsFactory->create();
                $model->load($id);
                $model->setId(null);
                $model->setStatus(0);

                $path = $this->filesystem->getDirectoryRead(
                    DirectoryList::MEDIA
                )->getAbsolutePath(
                    'amasty/amlabel/'
                );

                /* create new images*/
                $imagesTypes = ['prod', 'cat'];
                foreach ($imagesTypes as $type) {
                    $field = $type . '_img';
                    $oldName = $newName = $model->getData($field);
                    $i = 0;
                    while ($this->ioFile->fileExists($path . $newName)) {
                        $newName = (++$i) . $newName;
                    }
                    $this->ioFile->cp($path . $oldName, $path . $newName);
                    $model->setData($field, $newName);
                }

                $model->save();
                $this->messageManager->addSuccessMessage(__('You have duplicated the label.'));
                $this->_redirect('amasty_label/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t duplicate item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('amasty_label/*/edit', ['id' =>  $id]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a item to duplicate.'));
        $this->_redirect('amasty_label/*/');
    }
}
