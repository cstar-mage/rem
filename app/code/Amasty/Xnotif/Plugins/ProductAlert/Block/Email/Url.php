<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ProductAlert\Block\Email;

class Url
{
    private $data;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Model\UrlHash
     */
    private $urlHash;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\Xnotif\Model\UrlHash $urlHash
    ) {
        $this->registry = $registry;
        $this->urlHash = $urlHash;
        $this->setData($registry->registry('amxnotif_data'));
    }

    /**
     * @param $subject
     * @return null|string
     */
    protected function getType($subject)
    {
        $type = null;
        if ($subject instanceof \Magento\ProductAlert\Block\Email\Price) {
            $type = 'price';
        }
        if ($subject instanceof \Magento\ProductAlert\Block\Email\Stock) {
            $type = 'stock';
        }

        return $type;
    }

    /**
     * @param $subject
     * @param string $route
     * @param array $params
     * @return array
     */
    public function beforeGetUrl($subject, $route = '', $params = [])
    {
        if (isset($this->data['guest']) && isset($this->data['email'])) {
            if ($type = $this->getType($subject)) {
                $hash = $this->urlHash->getHash(
                    $this->productId,
                    $this->data['email']
                );
                $params['product_id'] = $this->getProductId();
                $params['email'] = urlencode($this->data['email']);
                $params['hash'] = urlencode($hash);
                $params['type'] = $type;
            }
        }

        return [$route, $params];
    }

    /**
     * @param $subject
     * @param $productId
     */
    public function beforeGetProductUnsubscribeUrl($subject, $productId)
    {
        $this->setProductId($productId);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
