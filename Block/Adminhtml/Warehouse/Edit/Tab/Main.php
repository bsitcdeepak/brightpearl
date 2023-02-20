<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Warehouse\Edit\Tab;

/**
 * Warehouse edit form main tab
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
        \Bsitc\Brightpearl\Model\Status $status,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $bpwarehouse,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
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
        $model = $this->_coreRegistry->registry('warehouse');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        /*
         $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        */
        
        $fieldset->addField(
            'mgt_location',
            'select',
            [
                'name' => 'mgt_location',
                'label' => __('Magento Inventory Locotion'),
                'title' => __('Magento Inventory Locotion'),
                'type' => 'options',
                'required' => true,
                'options' => $this->_bpwarehouse->tolocationArray(),
                'disabled' => $isElementDisabled
            ]
        );
                                        
        /*$fieldset->addField(
            'bp_warehouse',
            'multiselect',
            [
                'label' => __('Warehouse'),
                'title' => __('Warehouse'),
                'name' => 'bp_warehouse',

                'options' => \Bsitc\Brightpearl\Block\Adminhtml\Warehouse\Grid::getValueArray1(),
                'disabled' => $isElementDisabled
            ]
        );*/
        
        
         /*$fieldset->addField(
            'bp_warehouse',
            'multiselect',
            [
                'label' => __('BrightPearl Warehouse'),
                'title' => __('BrightPearl Warehouse'),
                'name' => 'bp_warehouse',
                'required' => true,
                 'values' => $this->_bpwarehouse->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );*/
        
          $fieldset->addField(
              'bp_warehouse',
              'select',
              [
                'name' => 'bp_warehouse',
                'label' => __('BrightPearl Warehouse'),
                'title' => __('BrightPearl Warehouse'),
                'required' => true,
                'values' => $this->_bpwarehouse->toOptionArray(),
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
