<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bppurchaseorders;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bppurchaseordersFactory
     */
    protected $_bppurchaseordersFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bppurchaseordersFactory $bppurchaseordersFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BppurchaseordersFactory $BppurchaseordersFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_bppurchaseordersFactory = $BppurchaseordersFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_bppurchaseordersFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(['suppliers'=> 'bsitc_brightpearl_suppliers'], 'suppliers.contactid = main_table.supplier_id', ['suppliers.pcf_leadtime']);

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
 
        $this->addColumn(
            'po_id',
            [
                'header' => __('PO Id'),
                'index' => 'po_id',
            ]
        );
        
        $this->addColumn(
            'orgdeliverydate',
            [
                'header' => __('Delivery Date'),
                'index' => 'orgdeliverydate',
            ]
        );
        
        $this->addColumn(
            'deliverydate',
            [
                'header' => __('Latest Delivery Date'),
                'index' => 'deliverydate',
            ]
        );
        
        $this->addColumn(
            'supplier_id',
            [
                'header' => __('Supplier Id'),
                'index' => 'supplier_id',
            ]
        );
        
        $this->addColumn(
            'pcf_leadtime',
            [
                'header' => __('Delivery Buffer'),
                'index' => 'pcf_leadtime',
            ]
        );
        
        $this->addColumn(
            'productid',
            [
                'header' => __('Product Id'),
                'index' => 'productid',
            ]
        );
        
        $this->addColumn(
            'productname',
            [
                'header' => __('Product Name'),
                'index' => 'productname',
            ]
        );
        
        $this->addColumn(
            'productsku',
            [
                'header' => __('Product SKU'),
                'index' => 'productsku',
            ]
        );
         
        $this->addColumn(
            'quantity',
            [
                'header' => __('Remainder'),
                'index' => 'quantity',
            ]
        );
     
        $this->addColumn(
            'on_order_qty',
            [
                'header' => __('On Order Quantity'),
                'index' => 'on_order_qty',
            ]
        );
		
        $this->addColumn(
            'limited_edition',
            [
                'header' => __('Limited Edition'),
                'index' => 'limited_edition',
            ]
        );
        
        $this->addColumn(
            'limited_edition_msg',
            [
                'header' => __('L. E. Message'),
                'index' => 'limited_edition_msg',
            ]
        );
        
        $this->addColumn(
            'limited_edition_exp_date',
            [
                'header' => __('L. E. Exp Date'),
                'index' => 'limited_edition_exp_date',
				'type'      => 'datetime',
            ]
        );
		
		
        
        $this->addColumn(
            'createdon',
            [
                'header' => __('Create At'),
                'index' => 'createdon',
                'type'      => 'datetime',
            ]
        );
            
            
        $this->addColumn(
            'updatedon',
            [
                'header' => __('Updated On'),
                'index' => 'updatedon',
                'type'      => 'datetime',
            ]
        );
            
         
        $this->addExportType($this->getUrl('brightpearl/*/exportCsv', ['_current' => true]), __('CSV'));
        $this->addExportType($this->getUrl('brightpearl/*/exportExcel', ['_current' => true]), __('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('bppurchaseorders');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }
        

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('brightpearl/*/index', ['_current' => true]);
    }

    /**
     * @param \Bsitc\Brightpearl\Model\bppurchaseorders|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        // return $this->getUrl('brightpearl/*/edit',['id' => $row->getId()]);
    }
}
