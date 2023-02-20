<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpleadsourcemap;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bpleadsourcemapFactory
     */
    protected $_bpleadsourcemapFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    
    protected $_mgtleadsource;

    protected $_bpleadsource;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bpleadsourcemapFactory $bpleadsourcemapFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BpleadsourcemapFactory $BpleadsourcemapFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\Config\Source\Allmgtcustomergroup $mgtleadsource,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpleadsource $bpleadsource,
        array $data = []
    ) {
        $this->_bpleadsourcemapFactory = $BpleadsourcemapFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_mgtleadsource = $mgtleadsource;
        $this->_bpleadsource = $bpleadsource;
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
        $collection = $this->_bpleadsourcemapFactory->create()->getCollection();
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
                    'code',
                    [
                        'header' => __('Mgt Customer Group'),
                        'index' => 'code',
                        'type' => 'options',
                        'options' => $this->_mgtleadsource->toOptionArray()
                    ]
                );
                
                $this->addColumn(
                    'bpcode',
                    [
                        'header' => __('BP Lead Source'),
                        'index' => 'bpcode',
                        'type' => 'options',
                        'options' => $this->_bpleadsource->toOptionArray()
                    ]
                );

                /*$this->addColumn(
                    'bp_id',
                    [
                        'header' => __('BP ID'),
                        'index' => 'bp_id',
                    ]
                );

                $this->addColumn(
                    'code',
                    [
                        'header' => __('Code'),
                        'index' => 'code',
                    ]
                );

                $this->addColumn(
                    'name',
                    [
                        'header' => __('Name'),
                        'index' => 'name',
                    ]
                );
                */
                
                /*$this->addColumn(
                    'bpcode',
                    [
                        'header' => __('Bp Code'),
                        'index' => 'bpcode',
                    ]
                );

                $this->addColumn(
                    'bpname',
                    [
                        'header' => __('BP Name'),
                        'index' => 'bpname',
                    ]
                );

                $this->addColumn(
                    'updated_at',
                    [
                        'header' => __('Updated At'),
                        'index' => 'updated_at',
                    ]
                );*/
                


        
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
        //$this->getMassactionBlock()->setTemplate('Bsitc_Brightpearl::bpleadsourcemap/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('bpleadsourcemap');

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
     * @param \Bsitc\Brightpearl\Model\bpleadsourcemap|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
}
