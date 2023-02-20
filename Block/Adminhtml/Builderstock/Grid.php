<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Builderstock;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\builderstockFactory
     */
    protected $_builderstockFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    protected $_allAttibutesSets;
    protected $_allwarehouse;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\builderstockFactory $builderstockFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BuilderstockFactory $BuilderstockFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
		\Bsitc\Brightpearl\Model\Config\Source\AllAttibutesSets $allAttibutesSets,
		\Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse $allwarehouse,
        array $data = []
    ) {
        $this->_builderstockFactory = $BuilderstockFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_allAttibutesSets = $allAttibutesSets;
        $this->_allwarehouse = $allwarehouse;
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
        $collection = $this->_builderstockFactory->create()->getCollection();
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
		
		
		$this->addColumn('parent_id', [
				'header' => __('Parent Product Id'),
				'index' => 'parent_id',
			]
		);
		
		$this->addColumn('parent_sku', [
				'header' => __('Parent Product Sku'),
				'index' => 'parent_sku',
			]
		);
		
		
		$this->addColumn('parent_name', [
				'header' => __('Parent Product Name'),
				'index' => 'parent_name',
			]
		);
		
		$this->addColumn('parent_attribute_set_id',[
				'header' => __('Parent Product Collection'),
				'index' => 'parent_attribute_set_id',
				'type' => 'options',
				'options' => $this->_allAttibutesSets->getCollectionAttribute()
			]
		);
		
		$this->addColumn('product_id', [
				'header' => __('Child Product Id'),
				'index' => 'product_id',
			]
		);
				
		$this->addColumn('sku', [
				'header' => __('Child Product Sku'),
				'index' => 'sku',
			]
		);
		
		$this->addColumn('name',[
				'header' => __('Child Product Name'),
				'index' => 'name',
			]
		);
		
		$this->addColumn('attribute_set_id',[
				'header' => __('Child Product Collection'),
				'index' => 'attribute_set_id',
				'type' => 'options',
				'options' => $this->_allAttibutesSets->getCollectionAttribute()
			]
		);
		
		$this->addColumn('bpid', [
				'header' => __('BP Id'),
				'index' => 'bpid',
			]
		);
		

        $this->addColumn('warehouse', [
                'header' => __('BP Warehouse'),
                'index' => 'warehouse',
                'type'=>'options',
                'options' => $this->_allwarehouse->toArray()
            ]
        );

		$this->addColumn('qty',[
				'header' => __('BP Qty'),
				'index' => 'qty',
			]
		);
		
		$this->addColumn('created_at',[
				'header' => __('Created At'),
				'index' => 'created_at',
				'type'      => 'datetime',
			]
		);
			
		$this->addColumn('updated_at',[
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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('builderstock');
        $this->getMassactionBlock()->addItem('delete',
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
     * @param \Bsitc\Brightpearl\Model\builderstock|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit',['id' => $row->getId()]);
    }

	

}