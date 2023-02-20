<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Builderstock\Edit\Tab;

/**
 * Builderstock edit form main tab
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
    protected $_allAttibutesSets;
    protected $_allwarehouse;

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
		\Bsitc\Brightpearl\Model\Config\Source\AllAttibutesSets $allAttibutesSets,
		\Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $allwarehouse,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_allAttibutesSets = $allAttibutesSets;
        $this->_allwarehouse = $allwarehouse;
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
        $model = $this->_coreRegistry->registry('builderstock');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField('parent_id','text',[
                'name' => 'parent_id',
                'label' => __('Parent Product Id'),
                'title' => __('Parent Product Id'),
                'disabled' => $isElementDisabled
            ]
        );
		
        $fieldset->addField('parent_sku','text',[
                'name' => 'parent_sku',
                'label' => __('Parent Product Sku'),
                'title' => __('Parent Product Sku'),
                'disabled' => $isElementDisabled
            ]
        );
		
        $fieldset->addField('parent_name','text',[
                'name' => 'parent_name',
                'label' => __('Parent Product Name'),
                'title' => __('Parent Product Name'),
                'disabled' => $isElementDisabled
            ]
        );
		
        $fieldset->addField('parent_attribute_set_id','select',[
                'name' => 'parent_attribute_set_id',
                'label' => __('Parent Product Collection'),
                'title' => __('Parent Product Collection'),
                'type' => 'options',
                'required' => true,
                'options' => $this->_allAttibutesSets->getCollectionAttribute(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('product_id','text',[
                'name' => 'product_id',
                'label' => __('Child Product Id'),
                'title' => __('Child Product Id'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('sku','text',[
                'name' => 'sku',
                'label' => __('Child Product Sku'),
                'title' => __('Child Product Sku'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('name','text',[
                'name' => 'name',
                'label' => __('Child Product Name'),
                'title' => __('Child Product Name'),
                'disabled' => $isElementDisabled
            ]
        );
		
        $fieldset->addField('attribute_set_id','select',[
                'name' => 'attribute_set_id',
                'label' => __('Child Product Collection'),
                'title' => __('Child Product Collection'),
                'type' => 'options',
                'required' => true,
                'options' => $this->_allAttibutesSets->getCollectionAttribute(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('bpid','text',[
                'name' => 'bpid',
                'label' => __('BP Product Id'),
                'title' => __('BP Product Id'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('warehouse','select',[
                'name' => 'warehouse',
                'label' => __('BP Warehouse'),
                'title' => __('BP Warehouse'),
                'type' => 'options',
                'required' => true,
                'options' => $this->_allwarehouse->toArray(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('qty','text',[
                'name' => 'qty',
                'label' => __('BP Qty'),
                'title' => __('BP Qty'),
                'disabled' => $isElementDisabled
            ]
        );
					
		/*
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::MEDIUM);
        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::MEDIUM);

        $fieldset->addField('created_at','date',[
                'name' => 'created_at',
                'label' => __('Created At'),
                'title' => __('Created At'),
                'date_format' => $dateFormat,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('updated_at','date',[
                'name' => 'updated_at',
                'label' => __('Updated At'),
                'title' => __('Updated At'),
                'date_format' => $dateFormat,
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

    public function getTargetOptionArray(){
    	return array('_self' => "Self",'_blank' => "New Page",);
    }
}
