<?php

namespace Bsitc\Brightpearl\Block\Adminhtml;

class Orderqueue extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'orderqueue/orderqueue.phtml';

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
        $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddButtonOptions(),
        ];
        
        $this->buttonList->add(
            'Process Queue',
            [
                'label' => __('Process Queue'),
                'onclick' => 'setLocation(\'' .$this->_getQueueUrl(). '\')',
                'class' => 'primary add'
            ],
            0
        );

        $this->buttonList->add('add_new', $addButtonProps);
        

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Bsitc\Brightpearl\Block\Adminhtml\Orderqueue\Grid', 'bsitc.orderqueue.grid')
        );
        return parent::_prepareLayout();
    }
    
    protected function _getQueueUrl()
    {
        return $this->getUrl(
            'brightpearl/*/processqueue'
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
