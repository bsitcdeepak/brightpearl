<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Warehouse;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\warehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    protected $_allwarehouse;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\warehouseFactory $warehouseFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\WarehouseFactory $WarehouseFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $allwarehouse,
        array $data = []
    ) {
        $this->_warehouseFactory = $WarehouseFactory;
        $this->_status = $status;
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
        $collection = $this->_warehouseFactory->create()->getCollection();
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
            'mgt_location',
            [
                'header' => __('Mgt Inventory Location'),
                'index' => 'mgt_location',
                'type'=>'options',
                'options' => $this->_allwarehouse->getLocationSatus()
                //'options' => \Bsitc\Brightpearl\Block\Adminhtml\Warehouse\Grid::getMgtWebsiteName()
            ]
        );
        
         $this->addColumn(
             'bp_warehouse',
             [
                    'header' => __('BP Warehouse'),
                    'index' => 'bp_warehouse',
                    'type'=>'options',
                    'options' => $this->_allwarehouse->toArray(),
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
        $this->getMassactionBlock()->setFormFieldName('warehouse');
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
     * @param \Bsitc\Brightpearl\Model\warehouse|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
    
    public function getMgtWebsiteName()
    {


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $source = $objectManager->create('Magento\Inventory\Model\Source');
        $results = $source->getCollection();
        foreach ($results as $result) {
            $data[$result->getSourceCode()] = $result->getSourceCode() .' - '. $result->getName() ;
            //$data[] = $result->getName();
        }
        return $data;
        /*

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $associate = $resource->getTableName('cataloginventory_stock');
        // $sql = "Select *  FROM " . $associate;
        $sql = $connection->select()->from($associate);
        $results = $connection->fetchAll($sql);
        $data = array();
        foreach($results as $result){
                $data[$result['website_id']] = $result['stock_name'];
        }
        return $data;
        */
    }
}
