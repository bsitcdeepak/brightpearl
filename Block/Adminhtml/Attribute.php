<?php

namespace Bsitc\Brightpearl\Block\Adminhtml;

class Attribute extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'attribute/attribute.phtml';

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

          $this->buttonList->add(
              'Sync BP Attr',
              [
                'label' => __('Sync BP Attr'),
                'onclick' => 'setLocation(\'' .$this->_getSynUrl(). '\')',
                'class' => 'primary add'
              ],
              0
          );

        $this->buttonList->add(
            'Sync Mgt Attr',
            [
                'label' => __('Sync Mgt Attr'),
                'onclick' => 'setLocation(\'' .$this->_getMgtSynUrl(). '\')',
                'class' => 'primary add'
            ],
            0
        );


       /* $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);
        */
        
      

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Bsitc\Brightpearl\Block\Adminhtml\Attribute\Grid', 'bsitc.attribute.grid')
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

    protected function _getSynUrl()
    {
        return $this->getUrl(
            'brightpearl/*/synfrombpapi'
        );
    }


    protected function _getMgtSynUrl()
    {
        return $this->getUrl(
            'brightpearl/*/synfrommgtapi'
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
