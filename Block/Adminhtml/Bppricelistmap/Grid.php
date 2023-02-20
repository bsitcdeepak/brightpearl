<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bppricelistmap;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bppricelistmapFactory
     */
    protected $_bppricelistmapFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    
    protected $_bppricelist;
    
    protected $_bpwarehouse;
    

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bppricelistmapFactory $bppricelistmapFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BppricelistmapFactory $BppricelistmapFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\Config\Source\Allpricelist $bppricelist,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $bpwarehouse,
        array $data = []
    ) {
        $this->_bppricelistmapFactory = $BppricelistmapFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_bppricelist = $bppricelist;
        $this->_bpwarehouse = $bpwarehouse;
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
        $collection = $this->_bppricelistmapFactory->create()->getCollection();
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
            'store_id',
            [
                    'header' => __('Magento Store'),
                    'index' => 'store_id',
                    'type'=>'options',
                    'options' => $this->_bpwarehouse->toStoreArray()
                ]
        );
        
        $this->addColumn(
            'bp_price',
            [
                    'header' => __('Bp Price Product / Order'),
                    'index' => 'bp_price',
                    'type'=>'options',
                    'options' => $this->_bppricelist->getSatus()
                 ]
        );
        
 
        $this->addColumn(
            'bp_sp_price',
            [
                    'header' => __('Bp Special Price'),
                    'index' => 'bp_sp_price',
                    'type'=>'options',
                    'options' => $this->_bppricelist->getSatus()
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
        $this->getMassactionBlock()->setFormFieldName('bppricelistmap');
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
     * @param \Bsitc\Brightpearl\Model\bppricelistmap|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
}
