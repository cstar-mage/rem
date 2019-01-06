<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab\Payment;

use MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab\Main as OriginalMain;
use MageWorx\MultiFees\Api\Data\FeeInterface;

class Main extends OriginalMain
{
    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \MageWorx\MultiFees\Model\PaymentFee $fee */
        $fee = $this->_coreRegistry->registry('mageworx_multifees_fee');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('fee_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Fee Info'),
                'class'  => 'fieldset-wide'
            ]
        );

        $this->addCommonFieldsForAllEntities($fieldset, $fee);

        $form->addValues($fee->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Payment Fee Information');
    }
}
