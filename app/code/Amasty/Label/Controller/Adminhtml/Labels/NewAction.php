<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Labels;

class NewAction extends \Amasty\Label\Controller\Adminhtml\Labels
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
