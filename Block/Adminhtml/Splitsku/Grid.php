<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Splitsku;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\splitskuFactory
     */
    protected $_splitskuFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\splitskuFactory $splitskuFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\SplitskuFactory $SplitskuFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_splitskuFactory = $SplitskuFactory;
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
        $collection = $this->_splitskuFactory->create()->getCollection();
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

		$this->addColumn('sku',[
				'header' => __('SKU'),
				'index' => 'sku',
			]
		);
		
		$this->addColumn('splitsku',[
				'header' => __('Split SKU'),
				'index' => 'splitsku',
			]
		);
		
		$this->addColumn('created_at',[
				'header' => __('Created At'),
				'index' => 'created_at',
				'type' => 'datetime',
			]
		);

		$this->addColumn('updated_at',[
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
        $this->getMassactionBlock()->setFormFieldName('splitsku');
        $this->getMassactionBlock()->addItem( 'delete', [
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
     * @param \Bsitc\Brightpearl\Model\splitsku|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit',['id' => $row->getId()]);
		
    }

	

}