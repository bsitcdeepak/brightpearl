<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bppurchaseorders\Edit\Tab;

/**
 * Bppurchaseorders edit form main tab
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
        $model = $this->_coreRegistry->registry('bppurchaseorders');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        
        $fieldset->addField(
            'po_id',
            'text',
            [
                'name' => 'po_id',
                'label' => __('PO Id'),
                'title' => __('PO Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'orgdeliverydate',
            'text',
            [
                'name' => 'orgdeliverydate',
                'label' => __('Delivery Date'),
                'title' => __('Delivery Date'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'deliverydate',
            'text',
            [
                'name' => 'deliverydate',
                'label' => __('Latest Delivery Date'),
                'title' => __('Latest Delivery Date'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'supplier_id',
            'text',
            [
                'name' => 'supplier_id',
                'label' => __('Supplier Id'),
                'title' => __('Supplier Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'leadtime',
            'text',
            [
                'name' => 'leadtime',
                'label' => __('Delivery Buffer'),
                'title' => __('Delivery Buffer'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'productid',
            'text',
            [
                'name' => 'productid',
                'label' => __('Product Id'),
                'title' => __('Product Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'productname',
            'text',
            [
                'name' => 'productname',
                'label' => __('Product Name'),
                'title' => __('Product Name'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'productsku',
            'text',
            [
                'name' => 'productsku',
                'label' => __('Product SKU'),
                'title' => __('Product SKU'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'quantity',
            'text',
            [
                'name' => 'quantity',
                'label' => __('Remainder'),
                'title' => __('Remainder'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'on_order_qty',
            'text',
            [
                'name' => 'on_order_qty',
                'label' => __('On Order Quantity'),
                'title' => __('On Order Quantity'),
                
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
            'createdon',
            'date',
            [
                'name' => 'createdon',
                'label' => __('Create At'),
                'title' => __('Create At'),
                    'date_format' => $dateFormat,
                    //'time_format' => $timeFormat,
                
                'disabled' => $isElementDisabled
            ]
        );
                        
                        
                        



        $fieldset->addField(
            'updatedon',
            'date',
            [
                'name' => 'updatedon',
                'label' => __('Updated On'),
                'title' => __('Updated On'),
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
