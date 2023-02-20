<?php

namespace Bsitc\Brightpearl\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpdateSupportedTables6 implements DataPatchInterface, PatchRevertableInterface
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

		$tables['bsitc_brightpearl_builder_stock']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_builder_stock'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('parent_id', Table::TYPE_TEXT, 50, ['default' => null], 'parent_id')
            ->addColumn('parent_sku', Table::TYPE_TEXT, 255, ['default' => null], 'parent_sku')
            ->addColumn('parent_name', Table::TYPE_TEXT, 255, ['default' => null], 'parent_name')
            ->addColumn('parent_attribute_set_id', Table::TYPE_TEXT, 50, ['default' => null], 'parent_attribute_set_id')
            ->addColumn('product_id', Table::TYPE_TEXT, 50, ['default' => null], 'product_id')
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['default' => null], 'type_code')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('attribute_set_id', Table::TYPE_TEXT, 50, ['default' => null], 'attribute_set_id')
            ->addColumn('bpid', Table::TYPE_TEXT, 50, ['default' => null], 'bpid')
            ->addColumn('warehouse', Table::TYPE_TEXT, 255, ['default' => null], 'warehouse_id')
            ->addColumn('qty', Table::TYPE_TEXT, 50, ['default' => null], 'bp_id')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
			   
        return $tables;
    }
}
