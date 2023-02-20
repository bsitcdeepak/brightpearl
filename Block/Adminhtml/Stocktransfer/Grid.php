<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Stocktransfer;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\stocktransferFactory
     */
    protected $_stocktransferFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    protected $_queuestatus;
    protected $_resultstatus;
    protected $_allwarehouse;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\stocktransferFactory $stocktransferFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\StocktransferFactory $StocktransferFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Bsitc\Brightpearl\Model\Resultstatus $resultstatus,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $allwarehouse,
        array $data = []
    ) {
        $this->_stocktransferFactory = $StocktransferFactory;
        $this->_status = $status;
        $this->_queuestatus = $queuestatus;
        $this->_resultstatus = $resultstatus;
        $this->moduleManager = $moduleManager;
        $this->_allwarehouse = $allwarehouse;
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
        $collection = $this->_stocktransferFactory->create()->getCollection();
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
             'goodsoutnoteid',
             [
                'header' => __('Good Out Note Id'),
                'index' => 'goodsoutnoteid',
             ]
         );
                
        $this->addColumn(
            'fromwarehouseid',
            [
                'header' => __('From Warehouse Id'),
                'index' => 'fromwarehouseid',
                'type'=>'options',
                'options' => $this->_allwarehouse->toArray(),
            ]
        );
            
            
        $this->addColumn(
            'targetwarehouseid',
            [
                'header' => __('Target Warehouse Id'),
                'index' => 'targetwarehouseid',
                'type'=>'options',
                'options' => $this->_allwarehouse->toArray(),
            ]
        );

        $this->addColumn(
            'productid',
            [
                'header' => __('BP Product ID'),
                'index' => 'productid',
            ]
        );
        
        $this->addColumn(
            'productsku',
            [
                'header' => __('SKU'),
                'index' => 'productsku',
            ]
        );


        $this->addColumn(
            'quantity',
            [
                'header' => __('Quantity'),
                'index' => 'quantity',
            ]
        );
        
        $this->addColumn(
            'goodsmovementid',
            [
                'header' => __('Goods Movement Id'),
                'index' => 'goodsmovementid',
            ]
        );
        
        $this->addColumn(
            'batchid',
            [
                'header' => __('Batch Id'),
                'index' => 'batchid',
            ]
        );
        
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_queuestatus->getOptionArray()
            ]
        );
        
 
        $this->addColumn(
            'created_at',
            [
                'header' => __('created_at'),
                'index' => 'created_at',
                'type'      => 'datetime',
            ]
        );
        
        $this->addColumn(
            'updated_at',
            [
                'header' => __('updated_at'),
                'index' => 'updated_at',
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
        $this->getMassactionBlock()->setFormFieldName('stocktransfer');
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
     * @param \Bsitc\Brightpearl\Model\stocktransfer|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
         return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
}
