<?php

namespace Bsitc\Brightpearl\Block\Adminhtml;

class Logs extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'logs/logs.phtml';

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
            'Clean All Log',
            [
                'label' => __('Clean All Log'),
                'onclick' => 'setLocation(\'' .$this->_getCleanlogUrl(). '\')',
                'class' => 'primary add'
            ],
            0
        );
        
        $this->buttonList->add(
            'Clean Log',
            [
                'label' => __('Clean Log'),
                'onclick' => 'setLocation(\'' .$this->_getCleanUrl(). '\')',
                'class' => 'primary add'
            ],
            0
        );
        
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Bsitc\Brightpearl\Block\Adminhtml\Logs\Grid', 'bsitc.logs.grid')
        );
        return parent::_prepareLayout();
    }
    
    protected function _getCleanlogUrl()
    {
        return $this->getUrl(
            'brightpearl/*/deleteall'
        );
    }
     
    protected function _getCleanUrl()
    {
        return $this->getUrl(
            'brightpearl/*/clean'
        );
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
