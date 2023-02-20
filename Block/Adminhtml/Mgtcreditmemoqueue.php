<?php

namespace Bsitc\Brightpearl\Block\Adminhtml;

class Mgtcreditmemoqueue extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'mgtcreditmemoqueue/mgtcreditmemoqueue.phtml';

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
        $this->buttonList->add('add_new', $addButtonProps, 2);
        

        $this->buttonList->add(
            'Process Queue',
            [
                'label' => __('Process Queue'),
                'onclick' => 'setLocation(\'' .$this->_getQueueUrl(). '\')',
                'class' => 'primary add'
            ],
            1
        );

 
        $this->buttonList->add(
            'Retry Failed Creditmemo',
            [
                'label' => __('Retry Failed Creditmemo'),
                'onclick' => 'setLocation(\'' .$this->_getRetryUrl(). '\')',
                'class' => 'primary add hidden'
            ],
            0
        );


        
 
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Bsitc\Brightpearl\Block\Adminhtml\Mgtcreditmemoqueue\Grid', 'bsitc.mgtcreditmemoqueue.grid')
        );
        return parent::_prepareLayout();
    }
    
    protected function _getQueueUrl()
    {
        return $this->getUrl(
            'brightpearl/*/processqueue'
        );
    }
    
    
    protected function _getRetryUrl()
    {
        return $this->getUrl(
            'brightpearl/*/retrycreditmemo'
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
