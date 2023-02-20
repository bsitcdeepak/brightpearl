<?php 

namespace Bsitc\Brightpearl\Block\Adminhtml\Dmsubscriber;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\dmsubscriberFactory
     */
    protected $_dmsubscriberFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;
    protected $_queuestatus;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\dmsubscriberFactory $dmsubscriberFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\DmsubscriberFactory $DmsubscriberFactory,
        \Bsitc\Brightpearl\Model\Status $status,
		\Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_dmsubscriberFactory = $DmsubscriberFactory;
        $this->_status = $status;
		$this->_queuestatus = $queuestatus;
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
        $collection = $this->_dmsubscriberFactory->create()->getCollection();
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
			'customer_id',
			[
				'header' => __('Customer Id'),
				'index' => 'customer_id',
			]
		);
		
		$this->addColumn(
			'fname',
			[
				'header' => __('First Name'),
				'index' => 'fname',
			]
		);
		
		$this->addColumn(
			'lname',
			[
				'header' => __('Last Name'),
				'index' => 'lname',
			]
		);
		
		$this->addColumn(
			'email',
			[
				'header' => __('Email'),
				'index' => 'email',
			]
		);
		
 
		
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_queuestatus->getOptionArray()
            ]
        );
		
		
		$this->addColumn(
			'created_at',
			[
				'header' => __('Created At'),
				'index' => 'created_at',
				'type'      => 'datetime',
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
        $this->getMassactionBlock()->setFormFieldName('dmsubscriber');
        $this->getMassactionBlock()->addItem('delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
		/*
        $statuses = $this->_status->getOptionArray();
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('brightpearl/ * /massStatus', ['_current' => true]),
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
		*/
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
     * @param \Bsitc\Brightpearl\Model\dmsubscriber|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		return $this->getUrl('brightpearl/*/edit',['id' => $row->getId()]);	
    }

	

}