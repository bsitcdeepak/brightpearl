<?php

namespace Bsitc\Brightpearl\Block\Adminhtml;

class Bpproducts extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'bpproducts/bpproducts.phtml';

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

        
        /*$addButtonProps = [
            'id' => 'add_new',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddButtonOptions(),
        ];*/
       // $this->buttonList->add('add_new', $addButtonProps);
        

        $this->buttonList->add(
            'Create Products',
            [
                'label' => __('1. Create Products'),
                'onclick' => 'setLocation(\'' .$this->_getQueueUrl(). '\')',
                'class' => 'primary add'
            ],
            0
        );



        $this->buttonList->add(
            'Sync Configurable Products',
            [
                'label' => __('2. Sync Configurable Products'),
                'onclick' => 'setLocation(\'' .$this->_getConfigSyncQueueUrl(). '\')',
                'class' => 'primary add'
            ],
            0
        );


        $this->buttonList->add(
            'Associate Configurable Products',
            [
                'label' => __('3. Associate Configurable Products'),
                'onclick' => 'setLocation(\'' .$this->_getAssociateConfigProductsQueueUrl(). '\')',
                'class' => 'primary add'
            ],
            0
        );
        



        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Bsitc\Brightpearl\Block\Adminhtml\Bpproducts\Grid', 'bsitc.bpproducts.grid')
        );
        return parent::_prepareLayout();
    }

    protected function _getQueueUrl()
    {
        return $this->getUrl(
            'brightpearl/*/createproduct'
        );
    }

    protected function _getConfigSyncQueueUrl()
    {
        return $this->getUrl(
            'brightpearl/*/syncConfigproduct'
        );
    }


    protected function _getAssociateConfigProductsQueueUrl()
    {
        return $this->getUrl(
            'brightpearl/*/associateConfigProductsQueueUrl'
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
