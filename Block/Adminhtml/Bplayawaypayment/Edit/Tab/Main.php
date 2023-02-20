<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bplayawaypayment\Edit\Tab;

/**
 * Bplayawaypayment edit form main tab
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
        \Bsitc\Brightpearl\Model\Status $status,
        \Bsitc\Brightpearl\Model\Resultstatus $resultstatus,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
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
        $model = $this->_coreRegistry->registry('bplayawaypayment');

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
                'label' => __('MGT Order'),
                'title' => __('MGT Order'),
                'disabled' => $isElementDisabled
            ]
        );
                    
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
            'bp_payment_id',
            'text',
            [
                'name' => 'bp_payment_id',
                'label' => __('BP Order Payment Id'),
                'title' => __('BP Order Payment Id'),
                'disabled' => $isElementDisabled
            ]
        );
                        
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('BP Payment Status'),
                'title' => __('BP Payment Status'),
                'name' => 'status',
                'options' => $this->_resultstatus->getOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::MEDIUM);
        $fieldset->addField(
            'created_at',
            'date',
            [
                'name' => 'created_at',
                'label' => __('created_at'),
                'title' => __('created_at'),
                'date_format' => $dateFormat,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'updated_at',
            'date',
            [
                'name' => 'updated_at',
                'label' => __('updated_at'),
                'title' => __('updated_at'),
                'date_format' => $dateFormat,
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
        return [ '_self' => "Self", '_blank' => "New Page", ];
    }
}
