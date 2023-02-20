<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Inventoryintransit\Edit\Tab;

/**
 * Inventoryintransit edit form main tab
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
        $model = $this->_coreRegistry->registry('inventoryintransit');

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
                'label' => __('ID'),
                'title' => __('ID'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'source_warehouse_id',
            'text',
            [
                'name' => 'source_warehouse_id',
                'label' => __('Source Warehouse'),
                'title' => __('Source Warehouse'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'target_warehouse_id',
            'text',
            [
                'name' => 'target_warehouse_id',
                'label' => __('Target Warehouse'),
                'title' => __('Target Warehouse'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'stock_transfer_id',
            'text',
            [
                'name' => 'stock_transfer_id',
                'label' => __('Stock Transfer Id'),
                'title' => __('Stock Transfer Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'product_id',
            'text',
            [
                'name' => 'product_id',
                'label' => __('Product Id'),
                'title' => __('Product Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'quantity',
            'text',
            [
                'name' => 'quantity',
                'label' => __('Quantity'),
                'title' => __('Quantity'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'transfer',
            'text',
            [
                'name' => 'transfer',
                'label' => __('Transfer Status'),
                'title' => __('Transfer Status'),
                
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
