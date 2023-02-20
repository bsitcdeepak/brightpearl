<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bpshippingmap\Edit\Tab;

/**
 * Bpshippingmap edit form main tab
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
    
    protected $_bpshipping;

    protected $_allcountryoptions;
	
    protected $_bpwarehouse;
	

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
        \Bsitc\Brightpearl\Model\Bpshipping $bpshipping,
		\Bsitc\Brightpearl\Model\Config\Source\Allcountryoptions $allcountryoptions,
        \Magento\Store\Model\StoreManagerInterface $storeManager,	
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $bpwarehouse,
        \Bsitc\Brightpearl\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_bpshipping = $bpshipping;
		$this->_allcountryoptions = $allcountryoptions;
		$this->_storeManager  = $storeManager;
        $this->_bpwarehouse = $bpwarehouse;		
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
        $model = $this->_coreRegistry->registry('bpshippingmap');

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
                'label' => __('Mgt Shipping Method'),
                'title' => __('Mgt Shipping Method'),
                'type' => 'options',
                'required' => true,
                'options' => $this->_bpshipping->getMgtShippinngOptionCustomArray(),
                //'options' => \Bsitc\Brightpearl\Block\Adminhtml\Bpshippingmap\Grid::getOptionArray2(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'bpcode',
            'select',
            [
                'name' => 'bpcode',
                'label' => __('BP Shipping Method'),
                'title' => __('BP Shipping Method'),
                'type' => 'options',
                'required' => true,
                 'options' => $this->_bpshipping->getBpShippinngOptionArray(),
                //'options' => \Bsitc\Brightpearl\Block\Adminhtml\Bpshippingmap\Grid::getOptionArray4(),
                'disabled' => $isElementDisabled
            ]
        );
     
		$fieldset->addField('country','select',
            [
                'name' => 'country',
                'label' => __('Country'),
                'title' => __('Country'),
                'type' => 'options',
                'required' => true,
				'options' => $this->_allcountryoptions->toCountryArray(),
                'disabled' => $isElementDisabled
            ]
        );
 					
        $fieldset->addField('deliveryday','text',
            [
                'name' => 'deliveryday',
                'label' => __('Delivery in Days'),
                'title' => __('Delivery in Days'),
                'disabled' => $isElementDisabled
            ]
        );
		
		

        $fieldset->addField('store_id','select',
            [
                'name' => 'store_id',
                'label' => __('Shop'),
                'title' => __('Shop'),
                'type' => 'options',
                'required' => true,
                'options' => $this->_bpwarehouse->toStoreArray(),
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
                    '_self' => "Self",
                    '_blank' => "New Page",
                    ];
    }
}
