<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Mgtcreditmemo;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\mgtcreditmemoFactory
     */
    protected $_mgtcreditmemoFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    protected $_queuestatus;
    protected $_resultstatus;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\mgtcreditmemoFactory $mgtcreditmemoFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\MgtcreditmemoFactory $MgtcreditmemoFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Bsitc\Brightpearl\Model\Resultstatus $resultstatus,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_mgtcreditmemoFactory = $MgtcreditmemoFactory;
        $this->_status = $status;
        $this->_queuestatus = $queuestatus;
        $this->_resultstatus = $resultstatus;
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
        $collection = $this->_mgtcreditmemoFactory->create()->getCollection();
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
            'mgt_creditmemo_id',
            [
                'header' => __('MGT Creditmemo'),
                'index' => 'mgt_creditmemo_id',
            ]
        );
        
        $this->addColumn(
            'so_order_id',
            [
                'header' => __('SO Id'),
                'index' => 'so_order_id',
            ]
        );
        
        $this->addColumn(
            'sc_order_id',
            [
                'header' => __('SC Id'),
                'index' => 'sc_order_id',
            ]
        );
        /*
        $this->addColumn('sc_payment_status',
            [
                'header' => __('SC Payment Status'),
                'index' => 'sc_payment_status',
            ]
        );
        */

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_resultstatus->getOptionArray()
            ]
        );

        /*
        $this->addColumn('created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type'      => 'datetime',
            ]
        );
        */
            
        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
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
        // return $this;
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('mgtcreditmemo');
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
     * @param \Bsitc\Brightpearl\Model\mgtcreditmemo|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
}
