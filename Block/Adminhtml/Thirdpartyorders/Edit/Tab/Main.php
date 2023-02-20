<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Thirdpartyorders\Edit\Tab;

/**
 * Thirdpartyorders edit form main tab
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
        $model = $this->_coreRegistry->registry('thirdpartyorders');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		
        $fieldset->addField(
            'tp_order_id',
            'text',
            [
                'name' => 'tp_order_id',
                'label' => __('Order Id'),
                'title' => __('Order Id'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'bp_order_id',
            'text',
            [
                'name' => 'bp_order_id',
                'label' => __('BP Order Id'),
                'title' => __('BP Order Id'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
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
					

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            \IntlDateFormatter::MEDIUM
        );

        $fieldset->addField(
            'received_at',
            'date',
            [
                'name' => 'received_at',
                'label' => __('Received At'),
                'title' => __('Received At'),
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

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
