<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Swedish\Edit\Tab;

/**
 * Swedish edit form main tab
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
	
    protected $_allcountryoptions;
	
    protected $_allbptax;

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
		\Bsitc\Brightpearl\Model\Config\Source\Allcountryoptions $allcountryoptions,
		\Bsitc\Brightpearl\Model\Config\Source\Allbptax $allbptax,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
		$this->_allcountryoptions = $allcountryoptions;
		$this->_allbptax = $allbptax;
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
        $model = $this->_coreRegistry->registry('swedish');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
		
        $fieldset->addField('tax_percent','text',[
                'name' => 'tax_percent',
                'label' => __('Tax breakdown percent'),
                'title' => __('Tax breakdown percent'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField('tax_class','select',[
                'name' => 'tax_class',
                'label' => __('Tax Class'),
                'title' => __('Tax Class'),
				'required' => true,
                'options' => $this->_allbptax->toBpTaxArray(),
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField('product_category','text',[
                'name' => 'product_category',
                'label' => __('Category for VAT breakdown'),
                'title' => __('Category for VAT breakdown'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
			
        $fieldset->addField('exclude_products','textarea',[
                'name' => 'exclude_products',
                'label' => __('Exclude Products'),
                'title' => __('Exclude Products'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('shipping_country','multiselect',[
                'name' => 'shipping_country',
                'label' => __('Shipping country for VAT breakdown'),
                'title' => __('Shipping country for VAT breakdown'),
				'required' => true,
                'values' => $this->_allcountryoptions->toCountryValueArray(),
                'disabled' => $isElementDisabled
            ]
        );


        $fieldset->addField('status','select',[
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'options' => $this->_status->getOptionArray(),
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

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
	
	
}
