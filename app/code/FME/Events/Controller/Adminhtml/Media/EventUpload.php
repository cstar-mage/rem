<?php

namespace FME\Events\Controller\Adminhtml\Media;

use Magento\Framework\App\Filesystem\DirectoryList;

class EventUpload extends \Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload
{

    const ADMIN_RESOURCE = 'FME_Events::manage_event';    

     public function execute()
     {
         try {
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'image']
            );            
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
            $config = $this->_objectManager->get('FME\Events\Model\Media\ConfigEevent');
            $result = $uploader->save($mediaDirectory->getAbsolutePath($config->getEventBaseTmpMediaPath()));

            
            $result['url'] = $this->_objectManager->get('FME\Events\Model\Media\ConfigEevent')
                ->getEventTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
         } catch (\Exception $e) {
             $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
         }
             $response = $this->resultRawFactory->create();
             $response->setHeader('Content-type', 'text/plain');
             $response->setContents(json_encode($result));
             return $response;
     }
}
