<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Salesorderreport\Edit\Tab;

/**
 * Salesorderreport edit form main tab
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
        $model = $this->_coreRegistry->registry('salesorderreport');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

                    
        $fieldset->addField(
            'mgt_order_id',
            'text',
            [
                'name' => 'mgt_order_id',
                'label' => __('Mgt Order Id'),
                'title' => __('Mgt Order Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'mgt_customer_id',
            'text',
            [
                'name' => 'mgt_customer_id',
                'label' => __('Mgt Customer Id'),
                'title' => __('Mgt Customer Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'bp_customer_id',
            'text',
            [
                'name' => 'bp_customer_id',
                'label' => __('Bp Customer Id'),
                'title' => __('Bp Customer Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'bp_customer_status',
            'text',
            [
                'name' => 'bp_customer_status',
                'label' => __('Bp Customer Status'),
                'title' => __('Bp Customer Status'),
                
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
            'bp_order_status',
            'text',
            [
                'name' => 'bp_order_status',
                'label' => __('Bp Order Status'),
                'title' => __('Bp Order Status'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'bp_inventory_status',
            'text',
            [
                'name' => 'bp_inventory_status',
                'label' => __('Bp Inventory Status'),
                'title' => __('Bp Inventory Status'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'bp_payment_status',
            'text',
            [
                'name' => 'bp_payment_status',
                'label' => __('Payment Status'),
                'title' => __('Payment Status'),
                
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
