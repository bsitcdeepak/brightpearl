<?php

namespace Bsitc\Brightpearl\Block\Adminhtml;

class Reconciliation extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'reconciliation/reconciliation.phtml';

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
            'step1',
            [
                'label' => __('Step 1'),
                'onclick' => 'setLocation(\'' .$this->_getStep1Url(). '\')',
                'class' => 'primary add hidden'
            ],
            1
        );


        $this->buttonList->add(
            'step2',
            [
                'label' => __('Step 2'),
                'onclick' => 'setLocation(\'' .$this->_getStep2Url(). '\')',
                'class' => 'primary add hidden'
            ],
            2
        );

        $this->buttonList->add(
            'step3',
            [
                'label' => __('Step 3'),
                'onclick' => 'setLocation(\'' .$this->_getStep3Url(). '\')',
                'class' => 'primary add hidden'
            ],
            3
        );

        $this->buttonList->add(
            'executereport',
            [
                'label' => __('Execute Report'),
                'onclick' => 'setLocation(\'' .$this->_getStep4Url(). '\')',
                'class' => 'primary add'
            ],
            4
        );


        
        $this->setChild('grid', $this->getLayout()->createBlock('Bsitc\Brightpearl\Block\Adminhtml\Reconciliation\Grid', 'bsitc.reconciliation.grid'));
        return parent::_prepareLayout();
    }
    
    protected function _getStep1Url()
    {
        return $this->getUrl('brightpearl/*/refreshreport');
    }
    
    protected function _getStep2Url()
    {
        return $this->getUrl('brightpearl/*/refreshfrombp');
    }
    
    protected function _getStep3Url()
    {
        return $this->getUrl('brightpearl/*/refreshbundlestock');
    }
    
    protected function _getStep4Url()
    {
        return $this->getUrl('brightpearl/*/executereport');
    }
    
    

    /**
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

    /**
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl('brightpearl/*/new');
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
