<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const AMASTY_LABEL_MEDIA_PATH = 'amasty/amlabel/';
    const AMASTY_LABEL_CONFIG_PATH = 'amasty_label/';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Image constructor.
     * @param Context $context
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            self::AMASTY_LABEL_CONFIG_PATH . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * return url with magento path
     * @param string $name
     * @return string
     */
    public function getImageUrl($name)
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::AMASTY_LABEL_MEDIA_PATH
        );

        if ($name != "" && $this->ioFile->fileExists($path . $name)) {
            $path = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $path . self::AMASTY_LABEL_MEDIA_PATH . $name;
        }

        return '';
    }

    /**
     * @param  string $name
     * @return string
     */
    public function getImagePath($name)
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::AMASTY_LABEL_MEDIA_PATH
        );

        if ($this->ioFile->fileExists($path . $name) && $name != "") {
            return $path . $name;
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isUseIndex()
    {
        return (bool) $this->getModuleConfig('display/use_index');
    }
}
