<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bpcustomerreport;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bpcustomerreportFactory
     */
    protected $_bpcustomerreportFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    protected $_queuestatus;
	
	protected $_customerGroup;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bpcustomerreportFactory $bpcustomerreportFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BpcustomerreportFactory $BpcustomerreportFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Bsitc\Brightpearl\Model\Queuestatus $queuestatus,
        \Magento\Framework\Module\Manager $moduleManager,
		\Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        array $data = []
    ) {
        $this->_bpcustomerreportFactory = $BpcustomerreportFactory;
        $this->_status = $status;
        $this->_queuestatus = $queuestatus;
        $this->moduleManager = $moduleManager;
		 $this->_customerGroup = $customerGroup;
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
        $collection = $this->_bpcustomerreportFactory->create()->getCollection();
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

		$this->addColumn('customer_id',
			[
				'header' => __('Mgt Customer ID'),
				'index' => 'customer_id',
			]
		);
		
		$this->addColumn('bp_customer_id',
			[
				'header' => __('BP Customer Id'),
				'index' => 'bp_customer_id',
			]
		);
		
		$this->addColumn('email',
			[
				'header' => __('Email'),
				'index' => 'email',
			]
		);
		
		/*
		$this->addColumn('group',
			[
				'header' => __('Group'),
				'index' => 'group',
 			]
		);
		*/
		
         $this->addColumn(
             'group',
             [
                'header' => __('Group'),
                'index' => 'group',
                'type' => 'options',
                'options' => $this->_customerGroup->toOptionHash()
             ]
         );
		
		
		$this->addColumn('account_status',
			[
				'header' => __('Account Status'),
				'index' => 'account_status',
			]
		);
		/*
		$this->addColumn('is_subscribe',
			[
				'header' => __('Is Subscribe'),
				'index' => 'is_subscribe',
			]
		);
		*/
		
         $this->addColumn('is_subscribe',
             [
                'header' => __('Is Subscribe'),
                'index' => 'is_subscribe',
                'type' => 'options',
                'options' => $this->getStatusOptionArray()
             ]
         );
		
		
		
		$this->addColumn('update_at',
			[
				'header' => __('Update At'),
				'index' => 'update_at',
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
        $this->getMassactionBlock()->setFormFieldName('bpcustomerreport');
        $this->getMassactionBlock()->addItem('delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        //$statuses = $this->_status->getOptionArray();
		/*
        $statuses = $this->_queuestatus->getOptionArray();

        $this->getMassactionBlock()->addItem('status',
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
        // return $this->getUrl('brightpearl/*/index', ['_current' => true]);
    }

    /**
     * @param \Bsitc\Brightpearl\Model\bpcustomerreport|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		// return $this->getUrl('brightpearl/*/edit',['id' => $row->getId()]);
    }

	public function getStatusOptionArray()
	{
		return [
            '0' => __('No'),
            '1' => __('Yes')
        ];
	}

}