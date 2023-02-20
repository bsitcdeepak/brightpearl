<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bppaymentmap\Edit\Tab;

/**
 * Bppaymentmap edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    protected $_bppayment;

    protected $_mgtpayment;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Bsitc\Brightpearl\Model\Status $status,
        \Bsitc\Brightpearl\Model\Config\Source\Allmgtpayment $mgtpayment,
        \Bsitc\Brightpearl\Model\Config\Source\Allbppayment $bppayment,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_bppayment = $bppayment;
        $this->_mgtpayment = $mgtpayment;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Bsitc\Brightpearl\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('bppaymentmap');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }


        
         $fieldset->addField(
             'code',
             'select',
             [
                'name' => 'code',
                'label' => __('Magento Payment Method'),
                'title' => __('Magento Payment Method'),
                'type' => 'options',
                'required' => true,
                'options' => $this->_mgtpayment->toOptionArray(),
                'disabled' => $isElementDisabled
             ]
         );
        

         $fieldset->addField(
             'bpcode',
             'select',
             [
                'name' => 'bpcode',
                'label' => __('BP Payment Method'),
                'title' => __('BP Payment Method'),
                'type' => 'options',
                'required' => true,
                'options' => $this->_bppayment->toOptionArray(),
                'disabled' => $isElementDisabled
             ]
         );
        


        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    public function getTargetOptionArray()
    {
        return [
                    [
                    '_self' => "1200",
                    '_blank' => "1200 Bank Current Account",
                    ],

                    [
                    '_self' => "1210",
                    '_blank' => "1210 Bank Deposit Account",
                    ],
                                        
                    [
                    '_self' => "1220",
                    '_blank' => "1220 Credit Card Account",
                    ],

                    
                    [
                    '_self' => "1230",
                    '_blank' => "1230 Petty Cash",
                    ],
                    
                    
                    [
                    '_self' => "1240",
                    '_blank' => "1240 Paypal Account",
                    ],
                    


                    ];
    }
}
