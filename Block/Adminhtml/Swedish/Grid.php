<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Swedish;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\swedishFactory
     */
    protected $_swedishFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
	
    protected $_allcountryoptions;
	
    protected $_allbptax;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\swedishFactory $swedishFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\SwedishFactory $SwedishFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
		\Bsitc\Brightpearl\Model\Config\Source\Allcountryoptions $allcountryoptions,
		\Bsitc\Brightpearl\Model\Config\Source\Allbptax $allbptax,
        array $data = []
    ) {
        $this->_swedishFactory = $SwedishFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
		$this->_allcountryoptions = $allcountryoptions;
		$this->_allbptax = $allbptax;
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
        $collection = $this->_swedishFactory->create()->getCollection();
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
        $this->addColumn('id',[
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn('tax_percent',[
				'header' => __('Tax breakdown percent'),
				'index' => 'tax_percent',
			]
		);
				
		$this->addColumn('tax_class',[
				'header' => __('BP Tax Class'),
				'index' => 'tax_class',
                'type' => 'options',
				'options' => $this->_allbptax->toBpTaxArray(),
			]
		);
				
		$this->addColumn('product_category',[
				'header' => __('Category for VAT breakdown'),
				'index' => 'product_category',
			]
		);

		$this->addColumn('shipping_country',[
				'header' => __('Shipping country for VAT breakdown'),
				'index'  => 'shipping_country',
				//'type'   =>'options',
 				//'options' => $this->_allcountryoptions->toCountryArray(),
			]
		);		

		$this->addColumn('status', [
				'header' => __('Status'),
				'index' => 'status',
				'type' => 'options',
				'options' => $this->_status->getOptionArray(),
			]
		);
		
		
		$this->addColumn('exclude_products',[
				'header' => __('Exclude Products'),
				'index' => 'exclude_products',
			]
		);


		$this->addColumn('created_at',[
				'header' => __('Created At'),
				'index' => 'created_at',
				'type' => 'datetime',
			]
		);

			
		$this->addColumn('updated_at', [
				'header' => __('Updated At'),
				'index' => 'updated_at',
				'type' => 'datetime',
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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('swedish');
        $this->getMassactionBlock()->addItem('delete',[
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = $this->_status->getOptionArray();
        $this->getMassactionBlock()->addItem('status', [
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
     * @param \Bsitc\Brightpearl\Model\swedish|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }	
 
	

}