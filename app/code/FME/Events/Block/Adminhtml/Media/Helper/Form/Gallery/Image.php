<?php

namespace FME\Events\Block\Adminhtml\Media\Helper\Form\Gallery;

use Magento\Backend\Block\Media\Uploader;
use Magento\Framework\View\Element\AbstractBlock;

class Image extends \Magento\Backend\Block\Widget
{

    /**
     * @var string
     */
    protected $_template = 'catalog/product/helper/gallery.phtml';

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    
    public $objectMgr;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \FME\Events\Model\Media\ConfigEevent $mediaConfig,
        \Magento\Framework\Registry $coreRegister,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {

        $this->_jsonEncoder = $jsonEncoder;
        $this->_mediaConfig = $mediaConfig;
        $this->_coreRegister = $coreRegister;
        $this->objectMgr = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild('uploader', 'Magento\Backend\Block\Media\Uploader');

        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->addSessionParam()->getUrl('events/media/eventupload')
        )->setFileField(
            'image'
        )->setFilters(
            [
                    'images' => [
                        'label' => __('Images (.gif, .jpg, .png)'),
                        'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                    ],
                ]
        );
        $this->_eventManager->dispatch('photogallery_prepare_layout', ['block' => $this]);

        return parent::_prepareLayout();
    }

    public function images()
    {
        $images = $this->_coreRegister->registry('photogallery_img');
        $img_data = $images->getData();
        return $img_data;
    }

    /**
     * Retrieve uploader block
     *
     * @return Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    /**
     * Retrieve media attributes
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        return $this->getElement()->getDataObject()->getMediaAttributes();
    }

    /**
     * @return string
     */
    public function getImagesJson()
    {
        $value['images'] = $this->images();
        if (is_array($value['images']) && count($value['images']) > 0) {
            foreach ($value['images'] as &$image) {
                $image['url'] = $this->_mediaConfig->getEventMediaUrl($image['file']);
                $image['file'] = $image['file'];
                $image['label'] = $image['label'];
                $image['media_id'] = $image['media_id'];
            }
            return $this->_jsonEncoder->encode($value['images']);
        }

        return '[]';
    }

    /**
     * @return string
     */
    public function getImagesValuesJson()
    {
        $values = [];
        return $this->_jsonEncoder->encode($values);
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];
        foreach ($this->images() as $attribute) {
            $imageTypes['image'] = [
                'code' => 'image',
                'value' => $attribute['file'],
                'label' => $attribute['label'],
                'scope' => 'Store View',
                'name' => 'gallery[image]',
            ];
        }
        return $imageTypes;
    }

    /**
     * @return string
     */
    public function getImageTypesJson()
    {
        return $this->_jsonEncoder->encode($this->getImageTypes());
    }
    // private function getImageHelper()
    // {
    //     if ($this->imageHelper === null) {
    //         $this->imageHelper = \Magento\Framework\App\ObjectManager::getInstance()
    //             ->get(\Magento\Catalog\Helper\Image::class);
    //     }
    //     return $this->imageHelper;
    // }

    // public function hasUseDefault()
    // {
    //     foreach ($this->getMediaAttributes() as $attribute) {
    //         if ($this->getElement()->canDisplayUseDefault($attribute)) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }
    // public function getAddImagesButton()
    // {
    //     return $this->getButtonHtml(
    //         __('Add New Images'),
    //         $this->getJsObjectName() . '.showUploader()',
    //         'add',
    //         $this->getHtmlId() . '_add_images_button'
    //     );
    // }
}
