<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Category\Edit\Tab;

/**
 * Bporderstatus edit form main tab
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
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
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
        $model = $this->_coreRegistry->registry('bpproducts');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        
        $fieldset->addField(
            'id',
            'text',
            [
                'name' => 'id',
                'label' => __('Id'),
                'title' => __('Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'product_id',
            'text',
            [
                'name' => 'product_id',
                'label' => __('Product ID'),
                'title' => __('Product ID'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'brand_id',
            'text',
            [
                'name' => 'brand_id',
                'label' => __('Brand Id'),
                'title' => __('Brand Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('Sku'),
                'title' => __('Sku'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        

        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('Sku'),
                'title' => __('Sku'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        
        $fieldset->addField(
            'ean',
            'text',
            [
                'name' => 'ean',
                'label' => __('Ean'),
                'title' => __('Ean'),
                
                'disabled' => $isElementDisabled
            ]
        );

        
        $fieldset->addField(
            'barcode',
            'text',
            [
                'name' => 'barcode',
                'label' => __('Barcode'),
                'title' => __('Barcode'),
                
                'disabled' => $isElementDisabled
            ]
        );


        $fieldset->addField(
            'product_group_id',
            'text',
            [
                'name' => 'product_group_id',
                'label' => __('product_group_id'),
                'title' => __('product_group_id'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        
        $fieldset->addField(
            'dimension',
            'text',
            [
                'name' => 'dimension',
                'label' => __('dimension'),
                'title' => __('dimension'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset->addField(
            'dimension',
            'text',
            [
                'name' => 'taxcode_id',
                'label' => __('taxcode_id'),
                'title' => __('taxcode_id'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        
        $fieldset->addField(
            'taxcode_code',
            'text',
            [
                'name' => 'taxcode_code',
                'label' => __('taxcode_code'),
                'title' => __('taxcode_code'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        
        $fieldset->addField(
            'sales_channel_name',
            'text',
            [
                'name' => 'sales_channel_name',
                'label' => __('sales_channel_name'),
                'title' => __('sales_channel_name'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        
        $fieldset->addField(
            'product_name',
            'text',
            [
                'name' => 'product_name',
                'label' => __('product_name'),
                'title' => __('product_name'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        
        
            $fieldset->addField(
                'product_condition',
                'text',
                [
                'name' => 'product_condition',
                'label' => __('product_condition'),
                'title' => __('product_condition'),
                
                'disabled' => $isElementDisabled
                ]
            );
        
        $fieldset->addField(
            'categories',
            'text',
            [
                'name' => 'categories',
                'label' => __('categories'),
                'title' => __('categories'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset->addField(
            'description',
            'text',
            [
                'name' => 'description',
                'label' => __('description'),
                'title' => __('description'),
                
                'disabled' => $isElementDisabled
            ]
        );
              

        $fieldset->addField(
            'short_description',
            'text',
            [
                'name' => 'short_description',
                'label' => __('short_description'),
                'title' => __('short_description'),
                
                'disabled' => $isElementDisabled
            ]
        );
                            
              
        $fieldset->addField(
            'warehouse',
            'text',
            [
                'name' => 'warehouse',
                'label' => __('warehouse'),
                'title' => __('warehouse'),
                
                'disabled' => $isElementDisabled
            ]
        );
            
            
            $fieldset->addField(
                'nominal_purchase_stock',
                'text',
                [
                'name' => 'nominal_purchase_stock',
                'label' => __('nominal_purchase_stock'),
                'title' => __('nominal_purchase_stock'),
                
                'disabled' => $isElementDisabled
                ]
            );
        
        $fieldset->addField(
            'nominal_purchase_purchase',
            'text',
            [
                'name' => 'nominal_purchase_purchase',
                'label' => __('nominal_purchase_purchase'),
                'title' => __('nominal_purchase_purchase'),
                
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'nominal_purchase_sales',
            'text',
            [
                'name' => 'nominal_purchase_sales',
                'label' => __('nominal_purchase_sales'),
                'title' => __('nominal_purchase_sales'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
            
        $fieldset->addField(
            'status',
            'text',
            [
                'name' => 'status',
                'label' => __('status'),
                'title' => __('status'),
                
                'disabled' => $isElementDisabled
            ]
        );
        

        $fieldset->addField(
            'syc_status',
            'text',
            [
                'name' => 'syc_status',
                'label' => __('syc_status'),
                'title' => __('syc_status'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            \IntlDateFormatter::MEDIUM
        );

        $fieldset->addField(
            'updated_at',
            'date',
            [
                'name' => 'updated_at',
                'label' => __('Updated At'),
                'title' => __('Updated At'),
                    'date_format' => $dateFormat,
                    //'time_format' => $timeFormat,
                
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
