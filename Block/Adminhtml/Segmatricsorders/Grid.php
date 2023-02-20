<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Segmatricsorders;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\segmatricsordersFactory
     */
    protected $_segmatricsordersFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    protected $_queuestatus;
	
	protected $_scopeConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\segmatricsordersFactory $segmatricsordersFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\SegmatricsordersFactory $SegmatricsordersFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
		\Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_segmatricsordersFactory = $SegmatricsordersFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
		$this->_queuestatus = $queuestatus;
		$this->_scopeConfig = $scopeConfig;
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
        $collection = $this->_segmatricsordersFactory->create()->getCollection();
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
        $this->addColumn( 'id', [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		
		$this->addColumn( 'order_id', [
				'header' => __('Order Id'),
				'index' => 'order_id',
			]
		);
		
		$this->addColumn( 'increment_id', [
				'header' => __('Increment Id'),
				'index' => 'increment_id',
			]
		);
			
        $this->addColumn('status', [
                'header' => __('Segmatrics Post Status'),  
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_queuestatus->getOptionArray()
             ]
         );		
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$show_response_data_order = $this->_scopeConfig->getValue('bpconfiguration/segmetrics/show_response_data_order', $storeScope );

		if($show_response_data_order)
		{
			$this->addColumn('json', [
					'header' => __('JSON Response Data'),  
					'index' => 'json'
				 ]
			 );	
		}
		 
				
		$this->addColumn( 'created_at', [
				'header' => __('Created At'),
				'index' => 'created_at',
			]
		);
		
		$this->addColumn( 'updated_at', [
				'header' => __('Updated At'),
				'index' => 'updated_at',
			]
		);

		
		$this->addExportType($this->getUrl('brightpearl/*/exportCsv', ['_current' => true]),__('CSV'));
		$this->addExportType($this->getUrl('brightpearl/*/exportExcel', ['_current' => true]),__('Excel XML'));

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
        $this->getMassactionBlock()->setFormFieldName('segmatricsorders');
        $this->getMassactionBlock()->addItem('delete', [
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
     * @param \Bsitc\Brightpearl\Model\segmatricsorders|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        // return $this->getUrl( 'brightpearl/*/edit', ['id' => $row->getId()] );
		
    }

	

}