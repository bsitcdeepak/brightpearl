<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bpcurrency\Edit\Tab;

/**
 * Bpcurrency edit form main tab
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
        $model = $this->_coreRegistry->registry('bpcurrency');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Code'),
                'title' => __('Code'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'symbol',
            'text',
            [
                'name' => 'symbol',
                'label' => __('Symbol'),
                'title' => __('Symbol'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'exchangeratevariancenominalcode',
            'text',
            [
                'name' => 'exchangeratevariancenominalcode',
                'label' => __('Exchange Rate'),
                'title' => __('Exchange Rate'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'isdefault',
            'text',
            [
                'name' => 'isdefault',
                'label' => __('isDefault'),
                'title' => __('isDefault'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'exchangeratevariancenominalcode',
            'text',
            [
                'name' => 'exchangeratevariancenominalcode',
                'label' => __('Exchange RateVarianceNominal Code'),
                'title' => __('Exchange RateVarianceNominal Code'),
				
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
                'label' => __('Updated At'),
                'title' => __('Updated At'),
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
