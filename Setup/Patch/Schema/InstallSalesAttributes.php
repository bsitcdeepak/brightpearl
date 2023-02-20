<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bsitc\Brightpearl\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallSalesAttributes implements SchemaPatchInterface
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
            'warehouse_store' => [
                'table' => ['quote','sales_order','sales_order_grid'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'comment' => 'BrightPearl Warehouse'
                ]
            ]

        ];
    }
    

    public function getAttributeDataStep2()
    {
        
        return [
            'warehouse_store' => [
                'table' => ['quote','order'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'comment' => 'BrightPearl Warehouse'
                ]
            ],
            'is_bespoke' => [
                'table' => ['quote','quote_item','order','order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 10,
                    'default' => 0,
                    'comment' => 'Is Bespoke'
                ]
            ],
            'bespoke_type' => [
                'table' => ['quote_item','order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'Is Bespoke'
                ]
            ],
            'is_customer_trade' => [
                'table' => ['shipment','invoice','creditmemo'],
                'attributeDefination' => [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 10,
                    'default' => 0,
                    'comment' => 'Is customer Trade'
                ]
            ],
            'is_preorder' => [
                'table' => ['quote','quote_item','order','order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 10,
                    'default' => 0,
                    'comment' => 'Is Preorder'
                ]
            ],
            'exp_delivery_date' => [
                'table' => ['quote_item','order_item'],
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
                'table' => ['quote_item','order_item'],
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
            'is_madetoorder' => [
                'table' => ['quote','quote_item','sales_order','order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 10,
                    'default' => 0,
                    'comment' => 'Is Madetoorder'
                ]
            ],
            'mto_id' => [
                'table' => ['quote_item','order_item'],
                'attributeDefination' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'visible' => true,
                    'required' => false,
                    'length' => 255,
                    'default' => 0,
                    'comment' => 'MTO Id'
                ]
            ],
            'generalinfo' => [
                'table' => ['quote_item','order_item'],
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
            'mto_lead_time_txt' => [
                'table' => ['quote_item','order_item'],
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
            'mto_lead_time_days' => [
                'table' => ['quote_item','order_item'],
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
