<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Fullstocksyn\Edit\Tab;

/**
 * Fullstocksyn edit form main tab
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
        $model = $this->_coreRegistry->registry('fullstocksyn');

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
            'location_id',
            'text',
            [
                'name' => 'location_id',
                'label' => __('Mgt Inventory Location'),
                'title' => __('Mgt Inventory Location'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'warehouse_id',
            'text',
            [
                'name' => 'warehouse_id',
                'label' => __('BP Warehouse'),
                'title' => __('BP Warehouse'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'bp_id',
            'text',
            [
                'name' => 'bp_id',
                'label' => __('BP Id'),
                'title' => __('BP Id'),
                
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
            'mgt_qty',
            'text',
            [
                'name' => 'mgt_qty',
                'label' => __('Pre. Qty'),
                'title' => __('Pre. Qty'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'bp_qty',
            'text',
            [
                'name' => 'bp_qty',
                'label' => __('Updated Qty'),
                'title' => __('Updated Qty'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'bp_ptype',
            'text',
            [
                'name' => 'bp_ptype',
                'label' => __('Bundle'),
                'title' => __('Bundle'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'updated_at',
            'text',
            [
                'name' => 'updated_at',
                'label' => __('Updated At'),
                'title' => __('Updated At'),
                
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
