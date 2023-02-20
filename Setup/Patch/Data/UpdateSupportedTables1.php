<?php

namespace Bsitc\Brightpearl\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpdateSupportedTables1 implements DataPatchInterface, PatchRevertableInterface
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
        
         $tables['bsitc_customer_queue']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_customer_queue'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('customer_id', Table::TYPE_INTEGER, 11, ['default' => null], 'customer_id')
            ->addColumn('email', Table::TYPE_TEXT, 255, ['default' => null], 'email')
            ->addColumn('status', Table::TYPE_TEXT, 20, ['default' => null], 'status')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
            
         $tables['bsitc_customer_report']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_customer_report'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('customer_id', Table::TYPE_TEXT, 100, ['default' => null], 'mgt_order_id')
            ->addColumn('bp_customer_id', Table::TYPE_TEXT, 100, ['default' => null], 'bp_customer_id')
            ->addColumn('email', Table::TYPE_TEXT, 255, ['default' => null], 'mgt_customer_id')
            ->addColumn('group', Table::TYPE_TEXT, 100, ['default' => null], 'group')
            ->addColumn('account_status', Table::TYPE_TEXT, 100, ['default' => null], 'account_status')
            ->addColumn('is_subscribe', Table::TYPE_TEXT, 100, ['default' => null], 'is_subscribe')
            ->addColumn('status', Table::TYPE_TEXT, 100, ['default' => null], 'status')
            ->addColumn('update_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'update_at');
              
        return $tables;
    }
}
