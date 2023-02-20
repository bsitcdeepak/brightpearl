<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bsitc\Brightpearl\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddSalesAttributes implements SchemaPatchInterface
{
    private $moduleDataSetup;


    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }


    public static function getDependencies()
    {
        return [];
    }


    public function getAliases()
    {
        return [];
    }


    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        
        foreach ($this->getAttributeData() as $attributeCode => $attributeData) {
            $attributeDefination     = $attributeData['attributeDefination'];
            $tables                 = $attributeData['table'];
            foreach ($tables as $table) {
                    $this->moduleDataSetup->getConnection()->addColumn(
                        $this->moduleDataSetup->getTable($table),
                        $attributeCode,
                        $attributeDefination
                    );
            }
        }
       
        $this->moduleDataSetup->endSetup();
    }
   
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        foreach ($this->getAttributeData() as $attributeCode => $attributeData) {
            $tables = $attributeData['table'];
            foreach ($tables as $table) {
                 $this->moduleDataSetup->getConnection()->dropColumn($this->moduleDataSetup->getTable($table), $attributeCode, null);
            }
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function getAttributeData()
    {
        
        return [
            'item_type_info' => [
                'table' => ['quote','quote_item','sales_order','sales_order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 10,
                    'default' => 0,
                    'comment' => 'Item Type Info'
                ]
            ],
            'exp_delivery_date' => [
                'table' => ['quote_item','sales_order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'Exp Delivery Date'
                ]
            ],
            'po_id' => [
                'table' => ['quote_item','sales_order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'PO Id'
                ]
            ],
            'pid' => [
                'table' => ['quote_item','sales_order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'Product Id'
                ]
            ],
            'generalinfo' => [
                'table' => ['quote_item','sales_order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'General Info'
                ]
            ],
            'lead_time_txt' => [
                'table' => ['quote_item','sales_order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'MTO Lead Time'
                ]
            ],
            'lead_time_days' => [
                'table' => ['quote_item','sales_order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'MTO Lead Time Days'
                ]
            ]
        ];
    }
}
