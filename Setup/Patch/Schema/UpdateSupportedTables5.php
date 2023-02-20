<?php

namespace Bsitc\Brightpearl\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpdateSupportedTables5 implements DataPatchInterface, PatchRevertableInterface
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
		
 		$this->moduleDataSetup->getConnection()->addColumn( $this->moduleDataSetup->getTable('bsitc_brightpearl_purchaseorders'), 'limited_edition',['type' => Table::TYPE_INTEGER,'length' => 11,'nullable' => true,'comment'  => 'is limited edition']);		   

		$this->moduleDataSetup->getConnection()->addColumn($this->moduleDataSetup->getTable('bsitc_brightpearl_purchaseorders'), 'limited_edition_msg',['type' => Table::TYPE_TEXT,'length' => 255,'nullable' => true,'comment'  => 'limited edition msg']);

		$this->moduleDataSetup->getConnection()->addColumn($this->moduleDataSetup->getTable('bsitc_brightpearl_purchaseorders'), 'limited_edition_exp_date',['type' => Table::TYPE_DATETIME,'nullable' => true,'comment'  => 'limited edition exp date']);		   
 
		$this->moduleDataSetup->getConnection()->addColumn( $this->moduleDataSetup->getTable('bsitc_brightpearl_purchaseorders'), 'limited_edition_msg_type',['type' => Table::TYPE_INTEGER,'length' => 11,'nullable' => true,'comment'  => 'Message or Time counter']);		   
 		
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
