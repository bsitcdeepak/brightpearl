<?php

namespace Bsitc\Brightpearl\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpdateSupportedTables implements DataPatchInterface, PatchRevertableInterface
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
             
         $tables['bsitc_category_leadtime']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_category_leadtime'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('category_id', Table::TYPE_TEXT, 20, ['default' => null], 'category_id')
            ->addColumn('leattime_msg', Table::TYPE_TEXT, 255, ['default' => null], 'leattime_msg')
            ->addColumn('leadtime', Table::TYPE_INTEGER, 10, ['default' => null], 'leadtime')
            ->addColumn('status', Table::TYPE_INTEGER, 2, ['default' => null], 'status')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null, 'unsigned' => true], 'store_id')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
            
              
        return $tables;
    }
}
