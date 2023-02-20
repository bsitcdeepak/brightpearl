<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bpsalesorder\Edit\Tab;

/**
 * Bpsalesorder edit form main tab
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
    protected $_allsalesstatus;

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
        \Bsitc\Brightpearl\Model\Config\Source\Allsalesstatus $allsalesstatus,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_allsalesstatus = $allsalesstatus;
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
        $model = $this->_coreRegistry->registry('bpsalesorder');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
 
                    
        $fieldset->addField(
            'so_order_id',
            'text',
            [
                'name' => 'so_order_id',
                'label' => __('BP Order Id'),
                'title' => __('BP Order Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'mgt_order_id',
            'text',
            [
                'name' => 'mgt_order_id',
                'label' => __('MGT Order Id'),
                'title' => __('MGT Order Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'mgt_creditmemo_id',
            'text',
            [
                'name' => 'mgt_creditmemo_id',
                'label' => __('MGT Creditmemo Id'),
                'title' => __('MGT Creditmemo Id'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'state',
            'select',
            [
                'name' => 'state',
                'label' => __('State'),
                'title' => __('State'),
                'options' => \Bsitc\Brightpearl\Block\Adminhtml\Bpsalescredit\Grid::getOptionArray11(),
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('BP Order Status'),
                'title' => __('BP Order Status'),
                'options' =>  $this->_allsalesstatus->toArray() ,
                'disabled' => $isElementDisabled
            ]
        );
                    
        $fieldset->addField(
            'order_type',
            'text',
            [
                'name' => 'order_type',
                'label' => __('BP Order Type'),
                'title' => __('BP Order Type'),
                
                'disabled' => $isElementDisabled
            ]
        );
        
        /*

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
        */
                        
                        
        $fieldset->addField(
            'json',
            'textarea',
            [
                'name' => 'json',
                'label' => __('Json'),
                'title' => __('Json'),
                
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
