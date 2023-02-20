<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bplayawaypayment;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bplayawaypaymentFactory
     */
    protected $_bplayawaypaymentFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    protected $_resultstatus;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bplayawaypaymentFactory $bplayawaypaymentFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BplayawaypaymentFactory $BplayawaypaymentFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\Resultstatus $resultstatus,
        array $data = []
    ) {
        $this->_bplayawaypaymentFactory = $BplayawaypaymentFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_resultstatus = $resultstatus;
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
        $collection = $this->_bplayawaypaymentFactory->create()->getCollection();
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
            'mgt_order_id',
            [
                'header' => __('MGT Order'),
                'index' => 'mgt_order_id',
            ]
        );
        
        $this->addColumn(
            'so_order_id',
            [
                'header' => __('BP Order Id'),
                'index' => 'so_order_id',
            ]
        );
        
        $this->addColumn(
            'bp_payment_id',
            [
                'header' => __('BP Order Payment Id'),
                'index' => 'bp_payment_id',
            ]
        );
        
                
        $this->addColumn(
            'status',
            [
                'header' => __('BP Payment Status'),
                'index' => 'status',
                'type' => 'options',
                'options' =>  $this->_resultstatus->getOptionArray()
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
        $this->getMassactionBlock()->setFormFieldName('bplayawaypayment');
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
     * @param \Bsitc\Brightpearl\Model\bplayawaypayment|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
}
