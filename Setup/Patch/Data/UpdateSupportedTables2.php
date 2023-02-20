<?php

namespace Bsitc\Brightpearl\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpdateSupportedTables2 implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    private $setup;
    
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SchemaSetupInterface $setup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->setup = $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        foreach ($this->getCustomTables() as $table) {
            $this->moduleDataSetup->getConnection()->createTable($table);
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        foreach ($this->getCustomTables() as $tableName => $table) {
            $this->moduleDataSetup->getConnection()->dropTable(
                $this->moduleDataSetup->getTable($tableName)
            );
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
        
        ];
    }
    
    public function getCustomTables()
    {
        $tables = [];
        
        $tables['bsitc_thirdparty_orders']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_thirdparty_orders'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('tp_order_id', Table::TYPE_INTEGER, 11, ['default' => null], 'tp_order_id')
            ->addColumn('bp_order_id', Table::TYPE_INTEGER, 11, ['default' => null], 'bp_order_id')
            ->addColumn('po_id', Table::TYPE_INTEGER, 11, ['default' => null], 'po_id')
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['default' => null], 'sku')
            ->addColumn('qty', Table::TYPE_INTEGER, 11, ['default' => null], 'qty')
            ->addColumn('received_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
  
            
         $tables['bsitc_transittime_shipping_mapping']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_transittime_shipping_mapping'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('store_id', Table::TYPE_INTEGER, 11, ['default' => null], 'store_id')
            ->addColumn('shipping_method', Table::TYPE_TEXT, 255, ['default' => null], 'mgt_shipping_method')
            ->addColumn('country', Table::TYPE_TEXT, 255, ['default' => null], 'country')
            ->addColumn('transit_time_msg', Table::TYPE_TEXT, 65535, ['default' => null], 'transittime_description')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
              
        return $tables;
    }
}
