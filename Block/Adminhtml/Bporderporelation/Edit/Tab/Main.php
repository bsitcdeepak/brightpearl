<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bporderporelation\Edit\Tab;

/**
 * Bporderporelation edit form main tab
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
        $model = $this->_coreRegistry->registry('bporderporelation');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        
        $fieldset->addField(
            'order_id',
            'text',
            [
                'name' => 'order_id',
                'label' => __('Mgt Order'),
                'title' => __('Mgt Order'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'po_id',
            'text',
            [
                'name' => 'po_id',
                'label' => __('PO ID'),
                'title' => __('PO ID'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('SKU'),
                'title' => __('SKU'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('Qty'),
                'title' => __('Qty'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'orgdeliverydate',
            'text',
            [
                'name' => 'orgdeliverydate',
                'label' => __('Order Delivery Date'),
                'title' => __('Order Delivery Date'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'deliverydate',
            'text',
            [
                'name' => 'deliverydate',
                'label' => __('Latest PO Delivery Date'),
                'title' => __('Latest PO Delivery Date'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'bp_order_id',
            'text',
            [
                'name' => 'bp_order_id',
                'label' => __('Bp Order Id'),
                'title' => __('Bp Order Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'created_at',
            'text',
            [
                'name' => 'created_at',
                'label' => __('Create At'),
                'title' => __('Create At'),
                
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
