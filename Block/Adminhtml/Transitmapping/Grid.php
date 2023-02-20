<?php 
namespace Bsitc\Brightpearl\Block\Adminhtml\Transitmapping;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\transitmappingFactory
     */
    protected $_transitmappingFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
	
    protected $_allwarehouse ;
	
    protected $_bpshipping;
	
    protected $_allcountryoptions;
	
    protected $_allmgtshipping;
 	

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\transitmappingFactory $transitmappingFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\TransitmappingFactory $TransitmappingFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
		\Bsitc\Brightpearl\Model\Bpshipping $bpshipping,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $allwarehouse,
        \Bsitc\Brightpearl\Model\Config\Source\Allcountryoptions $allcountryoptions,
        \Bsitc\Brightpearl\Model\Config\Source\Allmgtshipping $allmgtshipping,
        array $data = []
    ) {
        $this->_transitmappingFactory = $TransitmappingFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_allwarehouse = $allwarehouse;
        $this->_bpshipping = $bpshipping;
		$this->_allcountryoptions = $allcountryoptions;
		$this->_allmgtshipping = $allmgtshipping;
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
        $collection = $this->_transitmappingFactory->create()->getCollection();
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
        $this->addColumn('id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
 
		$this->addColumn('store_id',
			[
				'header' => __('Shop'),
				'index' => 'store_id',
				'type'=>'options',
				'options' => $this->_allwarehouse->toStoreArray()
			]
		);
		
		$this->addColumn('shipping_method',[
				'header' => __('MGT Shipping Method'),
				'index'  => 'shipping_method',
				'type'   =>'options',
  				'options' => $this->_allmgtshipping->getMgtShippinngOptionCustomArray(),
			]
		);
		
		$this->addColumn('country',[
				'header' => __('Country'),
				'index'  => 'country',
				'type'   =>'options',
 				'options' => $this->_allcountryoptions->toCountryArray(),
			]
		);
 		
		$this->addColumn('transit_time_msg',
			[
				'header' => __('Transittime Msg'),
				'index' => 'transit_time_msg',
			]
		);
		
		/*
		$this->addColumn('created_at',
			[
				'header' => __('Create At'),
				'index' => 'created_at',
				'type'      => 'datetime',
			]
		);
		*/
			
		$this->addColumn('updated_at',
			[
				'header' => __('Updated At'),
				'index' => 'updated_at',
				'type'      => 'datetime',
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
        $this->getMassactionBlock()->setFormFieldName('transitmapping');
        $this->getMassactionBlock()->addItem('delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = $this->_status->getOptionArray();
        $this->getMassactionBlock()->addItem('status',
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
     * @param \Bsitc\Brightpearl\Model\transitmapping|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()] );
    }

}