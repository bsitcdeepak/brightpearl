<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\Bpcurrency;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bpcurrencyFactory
     */
    protected $_bpcurrencyFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bpcurrencyFactory $bpcurrencyFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BpcurrencyFactory $BpcurrencyFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_bpcurrencyFactory = $BpcurrencyFactory;
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
        $collection = $this->_bpcurrencyFactory->create()->getCollection();
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
			'currency_id',
			[
				'header' => __('Currency ID'),
				'index' => 'currency_id',
			]
		);
		
		$this->addColumn(
			'title',
			[
				'header' => __('Title'),
				'index' => 'title',
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
			'symbol',
			[
				'header' => __('Symbol'),
				'index' => 'symbol',
			]
		);
		
		$this->addColumn(
			'exchangeratevariancenominalcode',
			[
				'header' => __('Exchange Rate'),
				'index' => 'exchangeratevariancenominalcode',
			]
		);
		
		$this->addColumn(
			'isdefault',
			[
				'header' => __('isDefault'),
				'index' => 'isdefault',
			]
		);
		
		$this->addColumn(
			'exchangeratevariancenominalcode',
			[
				'header' => __('Exchange RateVarianceNominal Code'),
				'index' => 'exchangeratevariancenominalcode',
			]
		);
		
		$this->addColumn(
			'updated_at',
			[
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
		return $this;
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('bpcurrency');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = $this->_status->getOptionArray();
        $this->getMassactionBlock()->addItem(
            'status',
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
     * @param \Bsitc\Brightpearl\Model\bpcurrency|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        // return $this->getUrl('brightpearl/*/edit',['id' => $row->getId()]);
    }

	

}