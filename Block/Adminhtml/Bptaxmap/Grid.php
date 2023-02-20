<?php
namespace Bsitc\Brightpearl\Block\Adminhtml\Bptaxmap;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Bsitc\Brightpearl\Model\bptaxmapFactory
     */
    protected $_bptaxmapFactory;

    /**
     * @var \Bsitc\Brightpearl\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bsitc\Brightpearl\Model\bptaxmapFactory $bptaxmapFactory
     * @param \Bsitc\Brightpearl\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bsitc\Brightpearl\Model\BptaxmapFactory $BptaxmapFactory,
        \Bsitc\Brightpearl\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_bptaxmapFactory = $BptaxmapFactory;
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
        $collection = $this->_bptaxmapFactory->create()->getCollection();
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
            'name',
            [
                'header' => __('Mgt Tax Name'),
                'index' => 'name',
            ]
        );
                
                        
        $this->addColumn(
            'code',
            [
                'header' => __('Mgt Tax Code'),
                'index' => 'code',
                'type' => 'options',
                'options' => \Bsitc\Brightpearl\Block\Adminhtml\Bptaxmap\Grid::getOptionArray4()
            ]
        );
        
        $this->addColumn(
            'bpcode',
            [
                'header' => __('BP Tax Code'),
                'index' => 'bpcode',
                'type' => 'options',
                'options' => \Bsitc\Brightpearl\Block\Adminhtml\Bptaxmap\Grid::getOptionArray2()
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
        $this->getMassactionBlock()->setFormFieldName('bptaxmap');
        $this->getMassactionBlock()->addItem(
            'delete',
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
     * @param \Bsitc\Brightpearl\Model\bptaxmap|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('brightpearl/*/edit', ['id' => $row->getId()]);
    }
    
    public static function getOptionArray2()
    {
        $data_array=[];
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $obj->create('Bsitc\Brightpearl\Model\ResourceModel\Bptax\Collection');
        if (count($collection)>0) {
            foreach ($collection as $item) {
                //$data_array[$item['bp_id']] = $item['code'];
                $data_array[$item['code']] = $item['code'];
            }
        }
        return($data_array);
    }

    public static function getValueArray2()
    {
        $data_array=[];
        foreach (\Bsitc\Brightpearl\Block\Adminhtml\Bptaxmap\Grid::getOptionArray2() as $k => $v) {
            $data_array[]=['value'=>$k,'label'=>$v];
        }
        return($data_array);
    }
        
    public static function getOptionArray4()
    {
        $data_array=[];
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $obj->create('Magento\Tax\Model\TaxClass\Source\Product')->getAllOptions();
            
        if (count($collection)>0) {
            foreach ($collection as $item) {
                if ($item['value'] == 0) {
                    $item['label']    = 'none';
                } else {
                    $item['value'] = $item['value'];
                }
                    $data_array[$item['value']] = $item['label'];
            }
        }
        return $data_array;
    }

    public static function getValueArray4()
    {
        $data_array=[];
        foreach (\Bsitc\Brightpearl\Block\Adminhtml\Bptaxmap\Grid::getOptionArray4() as $k => $v) {
            $data_array[]=['value'=>$k,'label'=>$v];
        }
        return($data_array);
    }
}
