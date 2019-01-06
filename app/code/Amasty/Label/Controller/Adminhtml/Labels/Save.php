<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Labels;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Amasty\Label\Controller\Adminhtml\Labels
{
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $id = (int)$this->getRequest()->getParam('label_id');
        if ($data) {
            try {
                $model = $this->labelsFactory->create();
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getLabelId()) {
                        throw new LocalizedException(__('The wrong label is specified.'));
                    }
                }

                $data = $this->validateLabelData($data);
                $data = $this->_saveColorSize($data);
                $isIndexDataChanged = $this->checkIndexDataChange($data, $model);
                $model->setData($data);

                $this->_session->setPageData($model->getData());
                $this->_prepareForSave($model);
                $model->save();

                $this->messageManager->addSuccessMessage(__('You saved the label.'));
                if ($this->config->isUseIndex()) {
                    if ($this->labelIndexer->isIndexerScheduled()) {
                        $this->labelIndexer->invalidateIndex();
                    } else {
                        if ($isIndexDataChanged) {
                            $this->labelIndexer->executeByLabelId($model->getId());
                        }
                    }
                }

                $this->_session->setPageData(false);
                $this->appCache->invalidate('full_page');

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_label/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('amasty_label/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_session->setPageData($data);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_session->setPageData($data);
            }
            if (!empty($id)) {
                $this->_redirect('amasty_label/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('amasty_label/*/new');
            }
            return;
        }
        $this->_redirect('amasty_label/*/');
    }

    /**
     * @param $data
     * @return mixed
     * @throws LocalizedException
     */
    private function validateLabelData($data)
    {
        if (!empty($data['stock_higher'])
            && !empty($data['stock_less'])
            && $data['stock_higher'] > $data['stock_less']
        ) {
            throw new LocalizedException(
                __('Please set field value \'Display if stock is higher than\' less than'
                    . ' field value \'Display if stock is lower than\'')
            );
        }

        if (isset($data['customer_group_ids'])) {
            $data['customer_group_ids'] = $this->serializer->serialize($data['customer_group_ids']);
        }

        /*if only one store exists*/
        if (isset($data['stores']) && !$data['stores']) {
            $data['stores'] = 1;
        }
        if (is_array($data['stores'])) {
            $data['stores'] = implode(',', $data['stores']);
        }

        if (isset($data['rule']) && isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];

            unset($data['rule']);

            $rule = $this->ruleFactory->create();
            $rule->loadPost($data);

            $data['cond_serialize'] = $this->serializer->serialize($rule->getConditions()->asArray());
            unset($data['conditions']);
        }

        if (!empty($data['to_time'])) {
            $data['to_date'] = $data['to_date'] . ' ' . $data['to_time'];
        }

        if (!empty($data['from_time'])) {
            $data['from_date'] = $data['from_date'] . ' ' . $data['from_time'];
        }
        if (empty($data['stock_less'])) {
            $data['stock_less'] = null;
        }
        if (empty($data['stock_higher'])) {
            $data['stock_higher'] = null;
        }

        return $data;
    }

    protected function _saveColorSize($data)
    {
        $catStyles = $data['cat_style'];
        if (array_key_exists('cat_size', $data) && $data['cat_size']) {
            $size = 'font-size: ' . $data['cat_size'] . ';';
            if (strpos($catStyles, 'font-size') !== false) {
                $catStyles = preg_replace("@font-size(.*?);@s", $size, $catStyles);
            } else {
                $catStyles .= $size;
            }
        }
        if (array_key_exists('cat_color', $data) && $data['cat_color']) {
            $color = 'color: ' . $data['cat_color'] . ';';
            if (strpos($catStyles, 'color:') !== false) {
                $catStyles = preg_replace("@color(.*?);@s", $color, $catStyles);
            } else {
                $catStyles .= $color;
            }
        }

        $catStyles = str_replace(";;", ";", $catStyles);
        $data['cat_style'] = $catStyles;

        $prodStyles = $data['prod_style'];
        if (array_key_exists('prod_size', $data) && $data['prod_size']) {
            $size = 'font-size: ' . $data['prod_size'] . ';';
            if (strpos($prodStyles, 'font-size') !== false) {
                $prodStyles = preg_replace("@font-size(.*?);@s", $size, $prodStyles);
            } else {
                $prodStyles .= $size;
            }
        }
        if (array_key_exists('prod_color', $data) && $data['prod_color']) {
            $color = 'color: ' . $data['prod_color'] . ';';
            if (strpos($prodStyles, 'color:') !== false) {
                $prodStyles = preg_replace("@color(.*?);@s", $color, $prodStyles);
            } else {
                $prodStyles .= $color;
            }
        }

        $prodStyles = str_replace(";;", ";", $prodStyles);
        $data['prod_style'] = $prodStyles;

        return $data;
    }

    protected function _prepareForSave($model)
    {
        //upload images
        $data = $this->getRequest()->getPost();
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'amasty/amlabel/'
        );

        $this->ioFile->checkAndCreateFolder($path);

        $imagesTypes = ['prod', 'cat'];
        foreach ($imagesTypes as $type) {
            $field = $type . '_img';
            $radioField = 'label_typelabels_' . $type . '_img';

            if ($data[$radioField] == 'downloadlabels_' . $type . '_img') {
                $files = $this->getRequest()->getFiles();
                $isRemove = array_key_exists('remove_labels_' . $field, $data);
                $hasNew = !empty($files[$field]['name']);

                try {
                    // remove the old file
                    if ($isRemove || $hasNew) {
                        $oldName = isset($data['old_labels_' . $field]) ? $data['old_labels_' . $field] : '';
                        if ($oldName) {
                            $this->ioFile->rm($path . $oldName);
                            $model->setData($field, '');
                        }
                    }

                    // upload a new if any
                    if (!$isRemove && $hasNew) {
                        //find the first available name
                        $newName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $files[$field]['name']);
                        if (substr($newName, 0, 1) == '.') { // all non-english symbols
                            $newName = 'label' . $newName;
                        }
                        $i = 0;
                        while ($this->ioFile->fileExists($path . $newName)) {
                            $newName = (++$i) . $newName;
                        }

                        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                        $uploader = $this->fileUploaderFactory->create(['fileId' => $field]);
                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->save($path, $newName);

                        $model->setData($field, $newName);
                    }
                } catch (\Exception $e) {
                    if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                }
            } else {
                $shapeField = 'shape_typelabels_' . $type . '_img';
                if (array_key_exists($shapeField, $data) && $data[$shapeField]) {
                    $shape = $data[$shapeField];
                    $color = $type . '_label_color';
                    $color = $data[$color];
                    $fileName = $this->shapeHelper->generateNewLabel($shape, $color);
                    $field = $type . '_img';
                    $model->setData($field, $fileName);
                }
            }
        }

        return true;
    }

    protected function checkIndexDataChange($data, $model)
    {
        $result = false;
        $origData = $model->getData();
        $indexFields = ['stores', 'is_new', 'is_sale', 'special_price_only', 'stock_less', 'stock_status', 'from_price',
           'to_price', 'by_price', 'price_range_enabled', 'cond_serialize', 'product_stock_enabled', 'stock_higher'];
        foreach ($origData as $key => $value) {
            if (in_array($key, $indexFields)
                && (array_key_exists($key, $data) && $value != $data[$key])
            ) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}
