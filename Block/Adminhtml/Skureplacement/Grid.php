<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Skureplacement;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\skureplacementFactory
     */
    protected $_skureplacementFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
	protected $_status;

	protected $_allcountryoptions;
	
	protected $_bpwarehouse;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\skureplacementFactory $skureplacementFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\SkureplacementFactory $SkureplacementFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
		\Bsitc\Brightpearl\Model\Config\Source\Allcountryoptions $allcountryoptions,
		\Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $bpwarehouse,
        array $data = []
    ) {
        $this->_skureplacementFactory = $SkureplacementFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
		$this->_allcountryoptions = $allcountryoptions;
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
        $collection = $this->_skureplacementFactory->create()->getCollection();
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
		
        $this->addColumn('store_id', [
				'header' => __('Magento Store'),
				'index' => 'store_id',
				'type'=>'options',
				'options' => $this->_bpwarehouse->toStoreArray()
			]
        );
        

		$this->addColumn('country',[
				'header' => __('Country'),
				'index'  => 'country',
				'type'   =>'options',
 				'options' => $this->_allcountryoptions->toCountryArray(),
			]
		);

		$this->addColumn('sku',[
				'header' => __('SKU to be replaced'),
				'index' => 'sku',
			]
		);

		$this->addColumn('rsku',[
				'header' => __('Replacement SKU'),
				'index' => 'rsku',
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
        $this->getMassactionBlock()->setFormFieldName('skureplacement');
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
     * @param \Bsitc\Brightpearl\Model\skureplacement|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()] );
    }

}