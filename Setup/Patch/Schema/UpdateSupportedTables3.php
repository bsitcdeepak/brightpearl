<?php

namespace Bsitc\Brightpearl\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpdateSupportedTables3 implements DataPatchInterface, PatchRevertableInterface
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
		

		$this->moduleDataSetup->getConnection()->addColumn($this->moduleDataSetup->getTable('bsitc_brightpearl_map_shippingmethods'), 'country',['type' => Table::TYPE_TEXT,'length' => 255,'nullable' => true,'comment'  => 'country' ,'after'  => 'bpname' ]  );		   

		$this->moduleDataSetup->getConnection()->addColumn( $this->moduleDataSetup->getTable('bsitc_brightpearl_map_shippingmethods'), 'deliveryday',['type' => Table::TYPE_INTEGER,'length' => 11,'nullable' => true,'comment'  => 'deliveryday', 'after'  => 'country' ]  );		   
		
		$this->moduleDataSetup->getConnection()->addColumn( $this->moduleDataSetup->getTable('bsitc_brightpearl_map_shippingmethods'), 'store_id',['type' => Table::TYPE_INTEGER,'length' => 11,'nullable' => true,'comment'  => 'store_id', 'after'  => 'deliveryday' ]  );		   
		
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {

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
 
}
