<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset;

class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    const SCOPE_LABEL = '[STORE VIEW]';

    /**
     * @var string
     */
    protected $_template = 'form/renderer/fieldset/element.phtml';

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getScopeLabel()
    {
        return __(self::SCOPE_LABEL);
    }

    /**
     * @return bool
     */
    public function usedDefault()
    {
        return (bool)$this->getDataObject()->getData($this->getElement()->getName().'_use_default');
    }

    /**
     * @return $this
     */
    public function checkFieldDisable()
    {
        if ($this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }

    /**
     * @return \Amasty\ShopbyBase\Model\OptionSetting
     */
    public function getDataObject()
    {
        return $this->getElement()->getForm()->getDataObject();
    }

    /**
     * @return bool
     */
    public function canDisplayUseDefault()
    {
        return (bool)$this->getDataObject()->getCurrentStoreId();
    }
}
