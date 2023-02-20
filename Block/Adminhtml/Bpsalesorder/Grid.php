<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpsalesorder;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bpsalesorderFactory
     */
    protected $_bpsalesorderFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    protected $_allsalesstatus;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bpsalesorderFactory $bpsalesorderFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BpsalesorderFactory $BpsalesorderFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\Config\Source\Allsalesstatus $allsalesstatus,
        array $data = []
    ) {
        $this->_bpsalesorderFactory = $BpsalesorderFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_allsalesstatus = $allsalesstatus;
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
        $collection = $this->_bpsalesorderFactory->create()->getCollection();
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
                    'so_order_id',
                    [
                        'header' => __('BP Order Id'),
                        'index' => 'so_order_id',
                    ]
                );
                
                $this->addColumn(
                    'mgt_order_id',
                    [
                        'header' => __('MGT Order Id'),
                        'index' => 'mgt_order_id',
                    ]
                );
                
                $this->addColumn(
                    'mgt_creditmemo_id',
                    [
                        'header' => __('MGT Creditmemo Id'),
                        'index' => 'mgt_creditmemo_id',
                    ]
                );
 
                
                $this->addColumn(
                    'state',
                    [
                        'header' => __('State'),
                        'index' => 'state',
                        'type' => 'options',
                        'options' => \Bsitc\Brightpearl\Block\Adminhtml\Bpsalescredit\Grid::getOptionArray11()
                    ]
                );
        
                $this->addColumn(
                    'status',
                    [
                        'header' => __('BP Order Status'),
                        'index' => 'status',
                        'type' => 'options',
                        'options' => $this->_allsalesstatus->toArray()
                    ]
                );
        
 
                
                
                $this->addColumn(
                    'order_type',
                    [
                        'header' => __('BP Order Type'),
                        'index' => 'order_type',
                    ]
                );
                
                $this->addColumn(
                    'updated_at',
                    [
                        'header' => __('Updated At'),
                        'index' => 'updated_at',
                        'type'      => 'datetime',
                    ]
                );
                    
                    


        
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
        

        
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
        //$this->getMassactionBlock()->setTemplate('Bsitc_Brightpearl::bpsalesorder/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('bpsalesorder');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('brightpearl/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
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
     * @param \Bsitc\Brightpearl\Model\bpsalesorder|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        
        return $this->getUrl(
            'brightpearl/*/edit',
            ['id' => $row->getId()]
        );
    }
}
