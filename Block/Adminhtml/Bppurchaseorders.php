<?php

namespace Bsitc\Brightpearl\Block\Adminhtml;

class Bppurchaseorders extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'bppurchaseorders/bppurchaseorders.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        /*
        $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);
        */
        $this->buttonList->add(
            'Update Products',
            [
                'label' => __('Update Products'),
                'onclick' => 'setLocation(\'' .$this->_getUpdatePreorderUrl(). '\')',
                'class' => 'primary add'
            ],
            1
        );
        
        $this->buttonList->add(
            'Reset Non Pre Order Products',
            [
                'label' => __('Reset Non Pre Order Products'),
                'onclick' => 'setLocation(\'' .$this->_getResetNonPreorderUrl(). '\')',
                'class' => 'primary add hidden'
            ],
            0
        );
        
        $this->buttonList->add(
            'Syn Purchase Order',
            [
                'label' => __('Syn PO'),
                'onclick' => 'setLocation(\'' .$this->_getSynUrl(). '\')',
                'class' => 'primary add'
            ],
            2
        );
        
        
        

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Bsitc\Brightpearl\Block\Adminhtml\Bppurchaseorders\Grid', 'bsitc.bppurchaseorders.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }
    
    protected function _getSynUrl()
    {
        return $this->getUrl(
            'brightpearl/*/synfromapi'
        );
    }
    
    protected function _getUpdatePreorderUrl()
    {
        return $this->getUrl(
            'brightpearl/*/updatepoproducts'
        );
    }
    protected function _getResetNonPreorderUrl()
    {
        return $this->getUrl(
            'brightpearl/*/resetnonpreorderproducts'
        );
    }
    

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'brightpearl/*/new'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
