<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bpcustomerreport\Edit\Tab;

/**
 * Bpcustomerreport edit form main tab
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
        $model = $this->_coreRegistry->registry('bpcustomerreport');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		
        $fieldset->addField(
            'customer_id',
            'text',
            [
                'name' => 'customer_id',
                'label' => __('Mgt Customer ID'),
                'title' => __('Mgt Customer ID'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'bp_customer_id',
            'text',
            [
                'name' => 'bp_customer_id',
                'label' => __('BP Customer Id'),
                'title' => __('BP Customer Id'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'group',
            'text',
            [
                'name' => 'group',
                'label' => __('Group'),
                'title' => __('Group'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'account_status',
            'text',
            [
                'name' => 'account_status',
                'label' => __('Account Status'),
                'title' => __('Account Status'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'is_subscribe',
            'text',
            [
                'name' => 'is_subscribe',
                'label' => __('Is Subscribe'),
                'title' => __('Is Subscribe'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'update_at',
            'text',
            [
                'name' => 'update_at',
                'label' => __('update_at'),
                'title' => __('update_at'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'status',
            'text',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
				
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
