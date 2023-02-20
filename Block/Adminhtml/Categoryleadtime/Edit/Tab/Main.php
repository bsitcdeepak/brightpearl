<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Categoryleadtime\Edit\Tab;

/**
 * Categoryleadtime edit form main tab
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
        $model = $this->_coreRegistry->registry('categoryleadtime');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        
        $fieldset->addField('category_id', 'text', [
                'name' => 'category_id',
                'label' => __('Category'),
                'title' => __('Category'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]);
                     
        $fieldset->addField('leadtime', 'text', [
                'name' => 'leadtime',
                'label' => __('Lead Time'),
                'title' => __('Lead Time'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]);
        
        $fieldset->addField('leattime_msg', 'text', [
                'name' => 'leattime_msg',
                'label' => __('Lead Time Message'),
                'title' => __('Lead Time Message'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]);
        /*
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::MEDIUM);
        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::MEDIUM);

        $fieldset->addField('created_at','date',[
                'name' => 'created_at',
                'label' => __('Created At'),
                'title' => __('Created At'),
                'date_format' => $dateFormat,
                //'time_format' => $timeFormat,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('updated_at','date',[
                'name' => 'updated_at',
                'label' => __('Updated At'),
                'title' => __('Updated At'),
                'date_format' => $dateFormat,
                //'time_format' => $timeFormat,
                'disabled' => $isElementDisabled
            ]
        );


        $fieldset->addField('store_id','text',[
                'name' => 'store_id',
                'label' => __('Store'),
                'title' => __('Store'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('status','text',[
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'disabled' => $isElementDisabled
            ]
        );
        */

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
