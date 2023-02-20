<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Fullstocksyn;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\fullstocksynFactory
     */
    protected $_fullstocksynFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    protected $_allwarehouse;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\fullstocksynFactory $fullstocksynFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\FullstocksynFactory $FullstocksynFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $allwarehouse,
        array $data = []
    ) {
        $this->_fullstocksynFactory = $FullstocksynFactory;
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
        $collection = $this->_fullstocksynFactory->create()->getCollection();
         $this->setCollection($collection);
           parent::_prepareCollection();
        //$this->_prepareColumns();
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
            'location_id',
            [
                'header' => __('Mgt Inventory Location'),
                'index' => 'location_id',
                'type'=>'options',
                'options' => $this->_allwarehouse->getLocationSatus()
                // 'options' => \Bsitc\Brightpearl\Block\Adminhtml\Fullstocksyn\Grid::getMgtWebsiteName()
            ]
        );
        
/*
        $this->addColumn('warehouse_id',
            [
                'header' => __('BP Warehouse'),
                'index' => 'warehouse_id',
                'type'=>'options',
                'options' => $this->_allwarehouse->toArray(),
            ]
        );
*/
        
        $this->addColumn(
            'warehouses',
            [
                'header' => __('BP Warehouse'),
                'index' => 'warehouses',
            ]
        );
        
        $this->addColumn(
            'bp_id',
            [
                'header' => __('BP Id'),
                'index' => 'bp_id',
            ]
        );
        
        $this->addColumn(
            'mgt_id',
            [
                'header' => __('Mgt Id'),
                'index' => 'mgt_id',
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
            'mgt_qty',
            [
                'header' => __('Pre. Qty'),
                'index' => 'mgt_qty',
            ]
        );
        
        $this->addColumn(
            'bp_qty',
            [
                'header' => __('Updated Qty'),
                'index' => 'bp_qty',
            ]
        );
        
        $this->addColumn(
            'bp_ptype',
            [
                'header' => __('Bundle'),
                'index' => 'bp_ptype',
            ]
        );
        
        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'index' => 'updated_at',
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
        $this->getMassactionBlock()->setFormFieldName('fullstocksyn');

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
     * @param \Bsitc\Brightpearl\Model\fullstocksyn|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
         // return $this->getUrl('brightpearl/*/edit',['id' => $row->getId()]);
    }

    public function getMgtWebsiteName()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $associate = $resource->getTableName('cataloginventory_stock');
        $sql = $connection->select()->from($associate);
        $results = $connection->fetchAll($sql);
        $data = [];
        foreach ($results as $result) {
                $data[$result['website_id']] = $result['stock_name'];
        }
        return $data;
    }
}
