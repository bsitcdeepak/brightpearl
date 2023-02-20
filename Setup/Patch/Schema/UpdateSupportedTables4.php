<?php

namespace Bsitc\Brightpearl\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpdateSupportedTables4 implements DataPatchInterface, PatchRevertableInterface
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
        
		$tables['bsitc_bp_currency']  = $this->moduleDataSetup->getConnection()
			->newTable($this->moduleDataSetup->getTable('bsitc_bp_currency'))
			->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
			->addColumn('currency_id', Table::TYPE_INTEGER, 11, ['default' => null], 'currency_id')
			->addColumn('title', Table::TYPE_TEXT, 255, ['default' => null], 'title')
			->addColumn('code', Table::TYPE_TEXT, 50, ['default' => null], 'code')
			->addColumn('symbol', Table::TYPE_TEXT, 10, ['default' => null], 'symbol')
			->addColumn('exchangerate', Table::TYPE_DECIMAL, '12,4', ['nullable' => false], 'exchangerate')
			->addColumn('isdefault', Table::TYPE_INTEGER, 2, ['default' => null], 'isDefault')
			->addColumn('exchangeratevariancenominalcode', Table::TYPE_TEXT, 100, ['default' => null], 'exchangeratevariancenominalcode')
			->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
                
        return $tables;
    }
}