<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bppricelistmap\Edit\Tab;

/**
 * Bppricelistmap edit form main tab
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

    protected $_bppricelist;
    
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
        \Bsitc\Brightpearl\Model\Config\Source\Allpricelist $bppricelist,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $bpwarehouse,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_bppricelist = $bppricelist;
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
        $model = $this->_coreRegistry->registry('bppricelistmap');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        
        $fieldset->addField(
            'store_id',
            'select',
            [
                'name'         => 'store_id',
                'label'     => __('Magento Store'),
                'title'     => __('Magento Store'),
                'type'         => 'options',
                'required'     => true,
                'options'     => $this->_bpwarehouse->toStoreArray(),
                'disabled'     => $isElementDisabled
            ]
        );
            
        $fieldset->addField(
            'bp_price',
            'select',
            [
                'name'         => 'bp_price',
                'label'     => __('Bp Price'),
                'title'     => __('Bp Price'),
                'type'         => 'options',
                'required'     => true,
                'options'     => $this->_bppricelist->getSatus(),
                'disabled'     => $isElementDisabled
            ]
        );
 
        $fieldset->addField(
            'bp_sp_price',
            'select',
            [
                'name'         => 'bp_sp_price',
                'label'     => __('Bp Special Price'),
                'title'     => __('Bp Special Price'),
                'type'         => 'options',
                'required'     => true,
                'options'     => $this->_bppricelist->getSatus(),
                'disabled'     => $isElementDisabled
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
