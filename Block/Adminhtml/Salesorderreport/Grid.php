<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Salesorderreport;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\salesorderreportFactory
     */
    protected $_salesorderreportFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\salesorderreportFactory $salesorderreportFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\SalesorderreportFactory $SalesorderreportFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_salesorderreportFactory = $SalesorderreportFactory;
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
        $collection = $this->_salesorderreportFactory->create()->getCollection();
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
                'header' => __('Mgt Order Id'),
                'index' => 'mgt_order_id',
            ]
        );
        
        $this->addColumn(
            'mgt_customer_id',
            [
                'header' => __('Mgt Customer Id'),
                'index' => 'mgt_customer_id',
            ]
        );
        
        $this->addColumn(
            'bp_customer_id',
            [
                'header' => __('Bp Customer Id'),
                'index' => 'bp_customer_id',
            ]
        );
        
        $this->addColumn(
            'bp_customer_status',
            [
                'header' => __('Bp Customer Status'),
                'index' => 'bp_customer_status',
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
            'bp_order_status',
            [
                'header' => __('Bp Order Status'),
                'index' => 'bp_order_status',
            ]
        );
        
        $this->addColumn(
            'bp_inventory_status',
            [
                'header' => __('Bp Inventory Status'),
                'index' => 'bp_inventory_status',
            ]
        );
        
        $this->addColumn(
            'bp_payment_status',
            [
                'header' => __('Payment Status'),
                'index' => 'bp_payment_status',
            ]
        );
        
        $this->addColumn(
            'update_at',
            [
                'header' => __('Updated At'),
                'index' => 'update_at',
                'type'      => 'datetime',
            ]
        );
        
        
        
        $this->addColumn(
            'resend',
            [
                'header' => __('Resend'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Resend'),
                        'url' => [
                            'base' => '*/*/resend'
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        
        
        $this->addColumn(
            'delete',
            [
                'header' => __('Delete'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Delete'),
                        'url' => [
                            'base' => '*/*/delete'
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('salesorderreport');
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
     * @param \Bsitc\Brightpearl\Model\salesorderreport|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
         return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
}
