<?php
namespace Emipro\Smsnotification\Block\System\Config\Form;
 
use Magento\Framework\App\Config\ScopeConfigInterface;
class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'system/button.phtml';
 
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    public function getAjaxCheckUrl()
    {
        return $this->getUrl('emipro_smsnotification/smssend/sendtest'); //hit controller by ajax call on button click.
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        //$originalData = $element->getOriginalData();
        $this->addData(
            [
                'id'        => 'send_sms',
                'button_label'     => _('send sms')
                //'onclick'   => 'javascript:check(); return false;'
            ]
        );
        return $this->_toHtml();
    }

}