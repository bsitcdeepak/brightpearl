<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Fulfilment\Edit\Tab;

/**
 * Fulfilment edit form main tab
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
    protected $_queuestatus;
    protected $_resultstatus;
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
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Bsitc\Brightpearl\Model\Resultstatus $resultstatus,
        \Bsitc\Brightpearl\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_queuestatus = $queuestatus;
        $this->_resultstatus = $resultstatus;
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
        $model = $this->_coreRegistry->registry('fulfilment');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        
        $fieldset->addField(
            'gon_id',
            'text',
            [
                'name' => 'gon_id',
                'label' => __('gon_id'),
                'title' => __('gon_id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'mgt_order_id',
            'text',
            [
                'name' => 'mgt_order_id',
                'label' => __('mgt_order_id'),
                'title' => __('mgt_order_id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'mgt_shipment_id',
            'text',
            [
                'name' => 'mgt_shipment_id',
                'label' => __('mgt_shipment_id'),
                'title' => __('mgt_shipment_id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                                    
                        
        $fieldset->addField(
            'mgt_shipment_status',
            'select',
            [
                'label' => __('mgt_shipment_status'),
                'title' => __('mgt_shipment_status'),
                'name' => 'mgt_shipment_status',
                
                'options' => $this->_resultstatus->getOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );
                        
                                        
                        
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('status'),
                'title' => __('status'),
                'name' => 'status',
                
                'options' => $this->_queuestatus->getOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );
                        
                        
        $fieldset->addField(
            'json',
            'textarea',
            [
                'name' => 'json',
                'label' => __('json'),
                'title' => __('json'),
                
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
            'created_at',
            'date',
            [
                'name' => 'created_at',
                'label' => __('created_at'),
                'title' => __('created_at'),
                    'date_format' => $dateFormat,
                    //'time_format' => $timeFormat,
                
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
                'label' => __('updated_at'),
                'title' => __('updated_at'),
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
