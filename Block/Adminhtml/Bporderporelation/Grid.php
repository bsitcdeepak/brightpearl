<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bporderporelation;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bporderporelationFactory
     */
    protected $_bporderporelationFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
	
    protected $_queuestatus;
	

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bporderporelationFactory $bporderporelationFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BporderporelationFactory $BporderporelationFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        array $data = []
    ) {
        $this->_bporderporelationFactory = $BporderporelationFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_queuestatus = $queuestatus;
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
        $collection = $this->_bporderporelationFactory->create()->getCollection();
		$collection->getSelect()->join(array('so' =>'sales_order'), 'so.entity_id= main_table.order_id', array('increment_id' => 'so.increment_id') );
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
            'order_id',
            [
                'header' => __('Mgt Order'),
                'index' => 'order_id',
            ]
        );
		
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Mgt Increment Id'),
                'index' => 'increment_id',
            ]
        );
        
        $this->addColumn(
            'po_id',
            [
                'header' => __('PO ID'),
                'index' => 'po_id',
            ]
        );
        
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
            ]
        );
        
        $this->addColumn(
            'qty',
            [
                'header' => __('Qty'),
                'index' => 'qty',
            ]
        );
        
        $this->addColumn(
            'orgdeliverydate',
            [
                'header' => __('Order Delivery Date'),
                'index' => 'orgdeliverydate',
            ]
        );
        
        $this->addColumn(
            'deliverydate',
            [
                'header' => __('Latest PO Delivery Date'),
                'index' => 'deliverydate',
            ]
        );
  
        $this->addColumn(
			'bp_order_id',
            [
                'header' => __('Bp Order Id'),
                'index' => 'bp_order_id',
            ]
        );
 
		
        $this->addColumn(
			'state',
             [
                'header' => __('Third Party App Post Status'),
                'index' => 'state',
                'type' => 'options',
                'options' => $this->_queuestatus->getOptionArray()
             ]
         );
        
        $this->addColumn(
			'updated_at',
             [
                'header' => __('Updated At'),
                'index' => 'updated_at',
                'type' => 'datetime',
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
        return $this;
        /* $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('bporderporelation');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/* /massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this; */
    }
        

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('brightpearl/*/index', ['_current' => true]);
    }

    /**
     * @param \Bsitc\Brightpearl\Model\bporderporelation|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        
        //return $this->getUrl( 'brightpearl/*/edit',['id' => $row->getId()]);
    }
}
