<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpshippingmap;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bpshippingmapFactory
     */
    protected $_bpshippingmapFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    
    protected $_bpshipping;
        
    protected $_allcountryoptions;

    protected $_allwarehouse ;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bpshippingmapFactory $bpshippingmapFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BpshippingmapFactory $BpshippingmapFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Bsitc\Brightpearl\Model\Bpshipping $bpshipping,
        \Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $allwarehouse,		
		\Bsitc\Brightpearl\Model\Config\Source\Allcountryoptions $allcountryoptions,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_bpshippingmapFactory = $BpshippingmapFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
		$this->_allwarehouse = $allwarehouse;
        $this->_bpshipping = $bpshipping;
		$this->_allcountryoptions = $allcountryoptions;
         
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
        $collection = $this->_bpshippingmapFactory->create()->getCollection();
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
        $this->addColumn('id', [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]);

        $this->addColumn('code', [
                'header' => __('MGT Shipping Method'),
                'index'  => 'code',
                'type'   =>'options',
                 'options' => $this->_bpshipping->getMgtShippinngOptionCustomArray(),
            ]);

        $this->addColumn('bpcode', [
                'header' => __('BP Shipping Method'),
                'index' => 'bpcode',
                'type'   =>'options',
                 'options' => $this->_bpshipping->getBpShippinngOptionArray(),
             ]);
			 
			 
		$this->addColumn('country',[
				'header' => __('Country'),
				'index'  => 'country',
				'type'   =>'options',
 				'options' => $this->_allcountryoptions->toCountryArray(),
			]
		);
 		
		$this->addColumn('deliveryday',
			[
				'header' => __('Delivery in Days'),
				'index' => 'deliveryday',
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
        $this->getMassactionBlock()->setFormFieldName('bpshippingmap');
        $this->getMassactionBlock()->addItem('delete', ['label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]);
 
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
     * @param \Bsitc\Brightpearl\Model\bpshippingmap|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
 
        /*Fetch MGT Shipping methods*/
    public static function getOptionArray4()
    {
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $shipingObj = $obj->create('\Magento\Shipping\Model\Config');
        $scopeConfigObj = $obj->create('\Magento\Framework\App\Config\ScopeConfigInterface');

        $activeCarriers = $shipingObj->getActiveCarriers();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $methods = [];
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = [];
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                     $code= $carrierCode.'_'.$methodCode;
                }
                   $carrierTitle =$scopeConfigObj->getValue('carriers/'.$carrierCode.'/title');
                    $methods[]=['value'=>$code,'label'=> $carrierTitle];
            }
        }
        return $methods;
    }
}
