<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model;

class UrlHash
{
    const SALT = 'qprugn1234njd';

    public function getHash($productId, $email)
    {
        return md5($productId . $email . self::SALT);
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    public function check(\Magento\Framework\App\Request\Http $request)
    {
        $hash = $request->getParam('hash');
        $productId = $request->getParam('product_id');
        $email = $request->getParam('email');

        if (empty($hash) || empty($productId) || empty($email)) {
            return false;
        }

        $real = $this->getHash($productId, $email);

        return $hash == $real;
    }
}
