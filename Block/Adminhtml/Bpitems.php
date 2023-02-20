<?php

namespace Bsitc\Brightpearl\Block\Adminhtml;

class Bpitems extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'bpitems/bpitems.phtml';

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
            'Synchronize',
            [
                'label' => __('Synchronize'),
                'onclick' => 'setLocation(\'' .$this->_getSynUrl(). '\')',
                'class' => 'primary add'
            ],
            1
        );
 
        $this->buttonList->add(
            'Import Mapping',
            [
                'label' => __('Import Mapping'),
                'onclick' => 'setLocation(\'' .$this->_getMappingUrl(). '\')',
                'class' => 'primary add hidden'
            ],
            0
        );
 
        $this->setChild('grid', $this->getLayout()->createBlock('Bsitc\Brightpearl\Block\Adminhtml\Bpitems\Grid', 'bsitc.bpitems.grid'));
        return parent::_prepareLayout();
    }
    
    protected function _getSynUrl()
    {
        return $this->getUrl('brightpearl/*/synbpitemsapi');
    }
    
    protected function _getMappingUrl()
    {
        return $this->getUrl('brightpearl/index/index');
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
