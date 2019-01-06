<?php

namespace Vsourz\Ageverification\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */

    private $blockFactory;

    /**

     * @var \Magento\Cms\Model\BlockRepository
     */
    protected $blockRepository;


    /**
     * Construct
     *
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Cms\Model\BlockRepository $blockRepository
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\BlockRepository $blockRepository
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $blocks = $this->blockFactory;

        $optionsBlock = $blocks->create()->getCollection()->getData();

        $blockIdentifierList = [];
        $blockExist;

        foreach ($optionsBlock as $optinIdfier) {
            $blockIdentifierList[] = $optinIdfier['identifier'];
        }

        if (in_array('vsourz-age-gate', $blockIdentifierList) || in_array('vsourz-age-gate-disagree', $blockIdentifierList)) {
            $blockExist = true;
        } else {
            $blockExist = false;
        }


        if (version_compare($context->getVersion(), '0.0.1') < 0 && $blockExist === false) {
             $testBlock = array(
                            'title' => 'Age Gate',
                            'identifier' => 'vsourz-age-gate',
                            'stores' => array(0),
                            'is_active' => 1,
                            'content' => '
                            <div class="popup-logo">LOGO</div>
                            <p class="small">This Website requires you to be 18 years or older <br />to enter.</p>
'
                        );

             $testBlock2 = array (
                            'title' => 'Age Gate Disagree',
                            'identifier' => 'vsourz-age-gate-disagree',
                            'stores' => array(0),
                            'is_active' => 1,
                            'content' => '<div class="notverify"><div class="popup-logo">LOGO</div>
<div class="notverify-txt">
<div class="cancel-icon">&nbsp;</div>
<p class="notverify-text-desc">We&#39;re Sorry !</p>
</div>
<p class="notverify-desc">You must be 18 years of age or older to enter this site.</p>

</div>'
                        );
            $this->blockFactory->create()->setData($testBlock)->save();
            $this->blockFactory->create()->setData($testBlock2)->save();
        }

        $setup->endSetup();
    }
}