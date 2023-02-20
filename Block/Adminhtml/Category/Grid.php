<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Category;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bpproductsFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bporderstatusFactory $bporderstatusFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\CategoryFactory $BpproductsFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_categoryFactory = $BpproductsFactory;
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
        $collection = $this->_bpproductsFactory->create()->getCollection();
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
                    'category_id',
                    [
                        'header' => __('Category ID'),
                        'index' => 'category_id',
                    ]
                );
                
                $this->addColumn(
                    'parentId',
                    [
                        'header' => __('Parent ID'),
                        'index' => 'parentId',
                    ]
                );
                
                $this->addColumn(
                    'active',
                    [
                        'header' => __('Active'),
                        'index' => 'active',
                    ]
                );

                $this->addColumn(
                    'createdOn',
                    [
                        'header' => __('CreatedOn'),
                        'index' => 'createdOn',
                    ]
                );
                $this->addColumn(
                    'createdById',
                    [
                        'header' => __('CreatedById'),
                        'index' => 'createdById',
                    ]
                );

                $this->addColumn(
                    'updatedOn',
                    [
                        'header' => __('updatedOn'),
                        'index' => 'updatedOn',
                    ]
                );
                
                $this->addColumn(
                    'updatedById',
                    [
                        'header' => __('UpdatedById Id'),
                        'index' => 'updatedById',
                    ]
                );
                
                $this->addColumn(
                    'description',
                    [
                        'header' => __('Description'),
                        'index' => 'description',
                    ]
                );
                
            
            
                $this->addColumn(
                    'syc_status',
                    [
                        'header' => __('Syc Status'),
                        'index' => 'syc_status',
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
        return true;

       /*  $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Bsitc_Brightpearl::bporderstatus/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('category');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brightpearl/* massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('brightpearl/* /massStatus', ['_current' => true]),
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

        */
    }
        

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('brightpearl/*/index', ['_current' => true]);
    }

    /**
     * @param \Bsitc\Brightpearl\Model\bporderstatus|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '#';
       /*  return $this->getUrl(
            'brightpearl/* /edit',
            ['id' => $row->getId()]
        ); */
    }
}
