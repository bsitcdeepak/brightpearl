<?php

namespace Bsitc\Brightpearl\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpdateSupportedTables10 implements DataPatchInterface, PatchRevertableInterface
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
		
		$tables['bsitc_segmatrics_orders']  = $this->moduleDataSetup->getConnection()
		->newTable($this->moduleDataSetup->getTable('bsitc_segmatrics_orders'))
		->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
		->addColumn('order_id', Table::TYPE_TEXT, 10, ['default' => null], 'order_id')
		->addColumn('increment_id', Table::TYPE_TEXT, 50, ['default' => null], 'increment_id')
		->addColumn('status', Table::TYPE_INTEGER, 2, ['default' => null], 'status')
		->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
		->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
		->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');

		$tables['bsitc_segmatrics_customers']  = $this->moduleDataSetup->getConnection()
		->newTable($this->moduleDataSetup->getTable('bsitc_segmatrics_customers'))
		->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
		->addColumn('first_name', Table::TYPE_TEXT, 10, ['default' => null], 'first_name')
		->addColumn('last_name', Table::TYPE_TEXT, 50, ['default' => null], 'last_name')
		->addColumn('email', Table::TYPE_TEXT, 50, ['default' => null], 'email')
		->addColumn('status', Table::TYPE_INTEGER, 2, ['default' => null], 'status')
		->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
		->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
		->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
			
			
			   
        return $tables;
    }
}
