<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bpshipping\Edit\Tab;

/**
 * Bpshipping edit form main tab
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
        $model = $this->_coreRegistry->registry('bpshipping');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

                    
        $fieldset->addField(
            'bpcode',
            'text',
            [
                'name' => 'bpcode',
                'label' => __('Code'),
                'title' => __('Code'),
                
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'bpid',
            'text',
            [
                'name' => 'bpid',
                'label' => __('Bp ID'),
                'title' => __('Bp ID'),
                'disabled' => $isElementDisabled
            ]
        );


        $fieldset->addField(
            'breaks',
            'text',
            [
                'name' => 'breaks',
                'label' => __('breaks'),
                'title' => __('breaks'),
                'disabled' => $isElementDisabled
            ]
        );


        $fieldset->addField(
            'method_type',
            'text',
            [
                'name' => 'method_type',
                'label' => __('Method Type'),
                'title' => __('Method Type'),
                'disabled' => $isElementDisabled
            ]
        );
        
          $fieldset->addField(
              'additional_information_required',
              'text',
              [
                'name' => 'additional_information_required',
                'label' => __('Additional Information Required'),
                'title' => __('Additional Information Required'),
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
