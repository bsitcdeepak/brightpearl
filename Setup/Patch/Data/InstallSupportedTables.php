<?php

namespace Bsitc\Brightpearl\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class InstallSupportedTables implements DataPatchInterface, PatchRevertableInterface
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

         $tables['bsitc_brightpearl_allwarehouse']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_allwarehouse'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('warehouse_id', Table::TYPE_TEXT, 255, ['default' => null], 'warehouse_id')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('type_code', Table::TYPE_TEXT, 255, ['default' => null], 'type_code')
            ->addColumn('type_description', Table::TYPE_TEXT, 255, ['default' => null], 'type_description')
            ->addColumn('address', Table::TYPE_TEXT, 255, ['default' => null], 'address')
            ->addColumn('click_and_collect_enabled', Table::TYPE_TEXT, 255, ['default' => null], 'click_and_collect_enabled')
            ->addColumn('status', Table::TYPE_TEXT, 255, ['default' => null], 'status')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');

         $tables['bsitc_brightpearl_associate_products']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_associate_products'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_TEXT, 50, ['default' => null], 'bp_id')
            ->addColumn('mg_sup_id', Table::TYPE_TEXT, 20, ['default' => null], 'mg_sup_id')
            ->addColumn('mg_child_id', Table::TYPE_TEXT, 20, ['default' => null], 'mg_child_id')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');

         $tables['bsitc_brightpearl_bpitems']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_bpitems'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
             ->addColumn('bp_id', Table::TYPE_INTEGER, 50, ['default' => null], 'bp_id')
            ->addColumn('bp_sku', Table::TYPE_TEXT, 255, ['default' => null], 'bp_sku')
            ->addColumn('bp_ptype', Table::TYPE_TEXT, 50, ['default' => null], 'bp_ptype')
             ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addIndex($this->setup->getIdxName('bsitc_brightpearl_bpitems', ['bp_sku']), ['bp_sku']);

 
           $tables['bsitc_brightpearl_brand']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_brand'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_TEXT, 50, ['default' => null], 'bp_id')
            ->addColumn('magento_id', Table::TYPE_TEXT, 50, ['default' => null], 'magento_id')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('description', Table::TYPE_TEXT, 255, ['default' => null], 'description')
            ->addColumn('sync', Table::TYPE_TEXT, 255, ['default' => null], 'sync');

         $tables['bsitc_brightpearl_categories']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_categories'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('category_id', Table::TYPE_TEXT, 50, ['default' => null], 'category_id')
            ->addColumn('parentId', Table::TYPE_TEXT, 50, ['default' => null], 'parentId')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('active', Table::TYPE_TEXT, 50, ['default' => null], 'active')
            ->addColumn('createdOn', Table::TYPE_TEXT, 50, ['default' => null], 'createdOn')
            ->addColumn('createdById', Table::TYPE_TEXT, 50, ['default' => null], 'createdById')
            ->addColumn('updatedOn', Table::TYPE_TEXT, 50, ['default' => null], 'updatedOn')
            ->addColumn('updatedById', Table::TYPE_TEXT, 50, ['default' => null], 'updatedById')
            ->addColumn('description', Table::TYPE_TEXT, 255, ['default' => null], 'description')
            ->addColumn('mg_category_id', Table::TYPE_TEXT, 50, ['default' => null], 'mg_category_id')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync');
                        
         $tables['bsitc_brightpearl_channel']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_channel'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('channel_id', Table::TYPE_TEXT, 50, ['default' => null], 'channel_id')
            ->addColumn('name', Table::TYPE_TEXT, 50, ['default' => null], 'name')
            ->addColumn('channel_type_id', Table::TYPE_TEXT, 50, ['default' => null], 'channel_type_id')
            ->addColumn('contact_group_id', Table::TYPE_TEXT, 50, ['default' => null], 'contact_group_id')
            ->addColumn('default_price_list_id', Table::TYPE_TEXT, 50, ['default' => null], 'default_price_list_id')
            ->addColumn('channel_brand_id', Table::TYPE_TEXT, 50, ['default' => null], 'channel_brand_id')
            ->addColumn('show_inchannel_menu', Table::TYPE_TEXT, 50, ['default' => null], 'show_inchannel_menu')
            ->addColumn('default_warehouse_id', Table::TYPE_TEXT, 255, ['default' => null], 'default_warehouse_id')
            ->addColumn('warehouse_ids', Table::TYPE_TEXT, 255, ['default' => null], 'warehouse_ids')
            ->addColumn('integration_detail', Table::TYPE_TEXT, 255, ['default' => null], 'integration_detail')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('status', Table::TYPE_TEXT, 100, ['default' => null], 'status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
        
         $tables['bsitc_brightpearl_customattributes']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_customattributes'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('code', Table::TYPE_TEXT, 20, ['default' => null], 'code')
            ->addColumn('collection_id', Table::TYPE_TEXT, 20, ['default' => null], 'collection_id')
            ->addColumn('collection_name', Table::TYPE_TEXT, 20, ['default' => null], 'collection_name')
            ->addColumn('brand_id', Table::TYPE_TEXT, 20, ['default' => null], 'brand_id')
            ->addColumn('custom_data', Table::TYPE_TEXT, 20, ['default' => null], 'custom_data')
            ->addColumn('option_value_id', Table::TYPE_TEXT, 20, ['default' => null], 'option_value_id')
            ->addColumn('mgt_code', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_code')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');
 
         $tables['bsitc_brightpearl_fulfilment']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_fulfilment'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('gon_id', Table::TYPE_INTEGER, 10, ['default' => null], 'gon_id')
            ->addColumn('so_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'so_order_id')
            ->addColumn('mgt_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_order_id')
            ->addColumn('mgt_shipment_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_shipment_id')
            ->addColumn('mgt_shipment_status', Table::TYPE_INTEGER, 2, ['default' => null], 'mgt_shipment_status')
            ->addColumn('status', Table::TYPE_INTEGER, 2, ['default' => null], 'status')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null, 'unsigned' => true], 'store_id')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');

         $tables['bsitc_brightpearl_inventory_intransit']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_inventory_intransit'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('source_warehouse_id', Table::TYPE_TEXT, 50, ['default' => null], 'source_warehouse_id')
            ->addColumn('target_warehouse_id', Table::TYPE_TEXT, 50, ['default' => null], 'target_warehouse_id')
            ->addColumn('transfer', Table::TYPE_TEXT, 50, ['default' => null], 'transfer')
            ->addColumn('stock_transfer_id', Table::TYPE_TEXT, 50, ['default' => null], 'stock_transfer_id')
            ->addColumn('product_id', Table::TYPE_TEXT, 50, ['default' => null], 'product_id')
            ->addColumn('quantity', Table::TYPE_TEXT, 50, ['default' => null], 'quantity')
            ->addColumn('status', Table::TYPE_INTEGER, 20, ['default' => null], 'status')
             ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null, 'unsigned' => true], 'store_id')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');

         $tables['bsitc_brightpearl_layaway_payment']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_layaway_payment'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('mgt_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_order_id')
            ->addColumn('so_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'so_order_id')
            ->addColumn('bp_payment_id', Table::TYPE_TEXT, 20, ['default' => null], 'bp_payment_id')
            ->addColumn('status', Table::TYPE_INTEGER, 2, ['default' => null], 'status')
             ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null, 'unsigned' => true], 'store_id')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
         
         $tables['bsitc_brightpearl_map_leadsources']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_map_leadsources'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_INTEGER, 10, ['default' => null], 'bp_id')
            ->addColumn('code', Table::TYPE_TEXT, 255, ['default' => null], 'code')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('bpcode', Table::TYPE_TEXT, 50, ['default' => null], 'bpcode')
            ->addColumn('bpname', Table::TYPE_TEXT, 100, ['default' => null], 'bpname')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');

         $tables['bsitc_brightpearl_map_paymentmethods']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_map_paymentmethods'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('code', Table::TYPE_TEXT, 100, ['default' => null], 'code')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('bpcode', Table::TYPE_TEXT, 255, ['default' => null], 'bpcode')
            ->addColumn('bpname', Table::TYPE_TEXT, 255, ['default' => null], 'bpname')
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, ['default' => null, 'unsigned' => true], 'store_id')
            ->addColumn('status', Table::TYPE_TEXT, 50, ['default' => null], 'status')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');

         $tables['bsitc_brightpearl_map_pricelist']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_map_pricelist'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('website_id', Table::TYPE_TEXT, 100, ['default' => null], 'website_id')
            ->addColumn('website_code', Table::TYPE_TEXT, 100, ['default' => null], 'website_code')
            ->addColumn('bp_price', Table::TYPE_TEXT, 100, ['default' => null], 'bp_price')
            ->addColumn('bp_sp_price', Table::TYPE_TEXT, 100, ['default' => null], 'bp_sp_price')
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, ['default' => null, 'unsigned' => true], 'store_id')
            ->addColumn('status', Table::TYPE_TEXT, 50, ['default' => null], 'status')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');

         $tables['bsitc_brightpearl_map_shippingmethods']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_map_shippingmethods'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_INTEGER, 10, ['default' => null], 'bp_id')
            ->addColumn('code', Table::TYPE_TEXT, 50, ['default' => null], 'code')
            ->addColumn('name', Table::TYPE_TEXT, 100, ['default' => null], 'name')
            ->addColumn('bpcode', Table::TYPE_TEXT, 50, ['default' => null], 'bpcode')
            ->addColumn('bpname', Table::TYPE_TEXT, 100, ['default' => null], 'bpname')
            ->addColumn('status', Table::TYPE_TEXT, 50, ['default' => null], 'status')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');

         $tables['bsitc_brightpearl_map_tax']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_map_tax'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('code', Table::TYPE_TEXT, 100, ['default' => null], 'code')
            ->addColumn('bpname', Table::TYPE_TEXT, 255, ['default' => null], 'bpname')
            ->addColumn('bpcode', Table::TYPE_TEXT, 255, ['default' => null], 'bpcode')
            ->addColumn('status', Table::TYPE_TEXT, 50, ['default' => null], 'status')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');

         $tables['bsitc_brightpearl_mgtcreditmemo']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_mgtcreditmemo'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('mgt_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_order_id')
            ->addColumn('mgt_creditmemo_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_creditmemo_id')
            ->addColumn('so_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'so_order_id')
            ->addColumn('sc_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'sc_order_id')
            ->addColumn('sc_payment_status', Table::TYPE_TEXT, 20, ['default' => null], 'sc_payment_status')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
             ->addColumn('status', Table::TYPE_INTEGER, 2, ['default' => null], 'status')
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, ['default' => null], 'store_id')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
         $tables['bsitc_brightpearl_mgtcreditmemo_queue']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_mgtcreditmemo_queue'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('cm_id', Table::TYPE_INTEGER, 10, ['default' => null], 'cm_id')
            ->addColumn('cm_increment_id', Table::TYPE_TEXT, 50, ['default' => null], 'cm_increment_id')
            ->addColumn('order_id', Table::TYPE_INTEGER, 10, ['default' => null], 'order_id')
            ->addColumn('order_increment_id', Table::TYPE_TEXT, 50, ['default' => null], 'order_increment_id')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('status', Table::TYPE_TEXT, 50, ['default' => null], 'status')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
         $tables['bsitc_brightpearl_nominals']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_nominals'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('name', Table::TYPE_TEXT, 50, ['default' => null], 'name')
            ->addColumn('code', Table::TYPE_TEXT, 50, ['default' => null], 'code')
            ->addColumn('account_type', Table::TYPE_INTEGER, 10, ['default' => null], 'account_type')
            ->addColumn('account_type_name', Table::TYPE_TEXT, 50, ['default' => null], 'account_type_name')
            ->addColumn('active', Table::TYPE_TEXT, 50, ['default' => null], 'active')
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, ['default' => null], 'store_id')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
         $tables['bsitc_brightpearl_option']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_option'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('option_id', Table::TYPE_TEXT, 20, ['default' => null], 'option_id')
            ->addColumn('attr_code', Table::TYPE_TEXT, 20, ['default' => null], 'attr_code')
            ->addColumn('option_value_id', Table::TYPE_TEXT, 20, ['default' => null], 'option_value_id')
            ->addColumn('option_value_name', Table::TYPE_TEXT, 20, ['default' => null], 'option_value_name')
            ->addColumn('sort_order', Table::TYPE_TEXT, 20, ['default' => null], 'sort_order')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('mg_option_value_id', Table::TYPE_TEXT, 20, ['default' => null], 'mg_option_value_id')
            ->addColumn('mgt_code', Table::TYPE_TEXT, 50, ['default' => null], 'mgt_code');
            
         $tables['bsitc_brightpearl_orderporelation']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_orderporelation'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, ['default' => null], 'store_id')
            ->addColumn('order_id', Table::TYPE_INTEGER, 100, ['default' => null], 'order_id')
            ->addColumn('po_id', Table::TYPE_INTEGER, 100, ['default' => null], 'po_id')
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['default' => null], 'sku')
            ->addColumn('qty', Table::TYPE_INTEGER, 100, ['default' => null], 'qty')
            ->addColumn('orgdeliverydate', Table::TYPE_DATETIME, null, ['default' => null], 'orgdeliverydate')
            ->addColumn('deliverydate', Table::TYPE_DATETIME, null, ['default' => null], 'deliverydate')
            ->addColumn('bp_order_id', Table::TYPE_INTEGER, 11, ['default' => 0], 'bp_order_id')
            ->addColumn('post_bp_status', Table::TYPE_INTEGER, 11, ['default' => 0], 'post_bp_status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addColumn('state', Table::TYPE_TEXT, 20, ['default' => null], 'Order PO Relation Post State TO CUSTOM APP');

         $tables['bsitc_brightpearl_order_cancel']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_order_cancel'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('mgt_order_id', Table::TYPE_TEXT, 255, ['default' => null], 'mgt_order_id')
            ->addColumn('mgt_increment_id', Table::TYPE_TEXT, 255, ['default' => null], 'mgt_increment_id')
            ->addColumn('bp_order_id', Table::TYPE_TEXT, 255, ['default' => null], 'bp_order_id')
            ->addColumn('status', Table::TYPE_TEXT, 100, ['default' => null], 'status')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');
 
         $tables['bsitc_brightpearl_order_leadsource']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_order_leadsource'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_TEXT, 50, ['default' => null], 'bp_id')
            ->addColumn('owner_id', Table::TYPE_TEXT, 50, ['default' => null], 'owner_id')
            ->addColumn('parent_id', Table::TYPE_TEXT, 50, ['default' => null], 'parent_id')
            ->addColumn('name', Table::TYPE_TEXT, 50, ['default' => null], 'name')
            ->addColumn('is_active', Table::TYPE_TEXT, 50, ['default' => null], 'is_active')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('status', Table::TYPE_TEXT, 100, ['default' => null], 'status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
  
         $tables['bsitc_brightpearl_order_paymentmethods']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_order_paymentmethods'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('payment_id', Table::TYPE_TEXT, 50, ['default' => null], 'payment_id')
            ->addColumn('name', Table::TYPE_TEXT, 50, ['default' => null], 'name')
            ->addColumn('code', Table::TYPE_TEXT, 50, ['default' => null], 'code')
            ->addColumn('isactive', Table::TYPE_TEXT, 50, ['default' => null], 'isactive')
            ->addColumn('bank_accounts', Table::TYPE_TEXT, 65535, ['default' => null], 'bank_accounts')
            ->addColumn('installed_integration_id', Table::TYPE_TEXT, 50, ['default' => null], 'installed_integration_id')
             ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('status', Table::TYPE_TEXT, 100, ['default' => null], 'status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
  
         $tables['bsitc_brightpearl_order_shippingmethods']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_order_shippingmethods'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bpid', Table::TYPE_TEXT, 50, ['default' => null], 'bpid')
            ->addColumn('bpcode', Table::TYPE_TEXT, 50, ['default' => null], 'bpcode')
            ->addColumn('bpname', Table::TYPE_TEXT, 255, ['default' => null], 'bpname')
            ->addColumn('method_type', Table::TYPE_TEXT, 255, ['default' => null], 'method_type')
            ->addColumn('additional_information_required', Table::TYPE_TEXT, 255, ['default' => null], 'additional_information_required')
            ->addColumn('breaks', Table::TYPE_TEXT, 255, ['default' => null], 'breaks')
            ->addColumn('bpdescription', Table::TYPE_TEXT, 255, ['default' => null], 'bpdescription')
             ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('status', Table::TYPE_TEXT, 100, ['default' => null], 'status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
            
         $tables['bsitc_brightpearl_order_status']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_order_status'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('status_id', Table::TYPE_INTEGER, 10, ['default' => null], 'status_id')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('order_type_code', Table::TYPE_TEXT, 25, ['default' => null], 'order_type_code')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
         $tables['bsitc_brightpearl_order_warehousemap']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_order_warehousemap'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('mgt_store', Table::TYPE_TEXT, 255, ['default' => null], 'mgt_store')
            ->addColumn('bp_warehouse', Table::TYPE_TEXT, 255, ['default' => null], 'bp_warehouse')
            ->addColumn('mgt_pos', Table::TYPE_TEXT, 255, ['default' => null], 'mgt_pos')
             ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');
            
         $tables['bsitc_brightpearl_paymentcapturequeue']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_paymentcapturequeue'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('account_code', Table::TYPE_TEXT, 255, ['default' => null], 'account_code')
            ->addColumn('resource_type', Table::TYPE_TEXT, 255, ['default' => null], 'resource_type')
            ->addColumn('bp_id', Table::TYPE_TEXT, 25, ['default' => null], 'bp_id')
            ->addColumn('lifecycle_event', Table::TYPE_TEXT, 255, ['default' => null], 'lifecycle_event')
            ->addColumn('full_event', Table::TYPE_TEXT, 255, ['default' => null], 'full_event')
            ->addColumn('status', Table::TYPE_TEXT, 100, ['default' => null], 'status')
             ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
            
         $tables['bsitc_brightpearl_picking']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_picking'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('gon_id', Table::TYPE_INTEGER, 10, ['default' => null], 'gon_id')
            ->addColumn('so_order_id', Table::TYPE_TEXT, 25, ['default' => null], 'so_order_id')
            ->addColumn('mgt_order_id', Table::TYPE_TEXT, 25, ['default' => null], 'mgt_order_id')
            ->addColumn('mgt_payment_status', Table::TYPE_TEXT, 25, ['default' => null], 'mgt_payment_status')
            ->addColumn('status', Table::TYPE_INTEGER, 10, ['default' => null], 'status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, ['default' => null], 'store_id');
  
         $tables['bsitc_brightpearl_pricelist']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_pricelist'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_product_id', Table::TYPE_TEXT, 25, ['default' => null], 'bp_product_id')
            ->addColumn('bp_pricelist', Table::TYPE_TEXT, 65535, ['default' => null], 'bp_pricelist')
            ->addColumn('mg_product_id', Table::TYPE_TEXT, 50, ['default' => null], 'mg_product_id')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('queue_status', Table::TYPE_TEXT, 50, ['default' => null], 'queue_status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
            
         $tables['bsitc_brightpearl_pricelist_config']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_pricelist_config'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_TEXT, 20, ['default' => null], 'bp_id')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('code', Table::TYPE_TEXT, 50, ['default' => null], 'code')
            ->addColumn('currency_code', Table::TYPE_TEXT, 50, ['default' => null], 'currency_code')
            ->addColumn('currency_symbol', Table::TYPE_TEXT, 50, ['default' => null], 'currency_symbol')
            ->addColumn('currency_id', Table::TYPE_TEXT, 50, ['default' => null], 'currency_id')
            ->addColumn('pricelist_type_code_id', Table::TYPE_TEXT, 50, ['default' => null], 'pricelist_type_code_id')
            ->addColumn('gross', Table::TYPE_TEXT, 50, ['default' => null], 'gross')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');

         $tables['bsitc_brightpearl_products']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_products'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('product_id', Table::TYPE_TEXT, 20, ['default' => null], 'product_id')
            ->addColumn('magento_id', Table::TYPE_TEXT, 20, ['default' => null], 'magento_id')
            ->addColumn('brand_id', Table::TYPE_TEXT, 20, ['default' => null], 'brand_id')
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['default' => null], 'sku')
            ->addColumn('upc', Table::TYPE_TEXT, 255, ['default' => null], 'upc')
            ->addColumn('isbn', Table::TYPE_TEXT, 255, ['default' => null], 'isbn')
            ->addColumn('ean', Table::TYPE_TEXT, 255, ['default' => null], 'ean')
            ->addColumn('mpc', Table::TYPE_TEXT, 255, ['default' => null], 'mpc')
            ->addColumn('barcode', Table::TYPE_TEXT, 255, ['default' => null], 'barcode')
            ->addColumn('product_group_id', Table::TYPE_TEXT, 25, ['default' => null], 'product_group_id')
            ->addColumn('dimension', Table::TYPE_TEXT, 255, ['default' => null], 'dimension')
            ->addColumn('taxcode_id', Table::TYPE_TEXT, 25, ['default' => null], 'taxcode_id')
            ->addColumn('taxcode_code', Table::TYPE_TEXT, 25, ['default' => null], 'taxcode_code')
            ->addColumn('sales_channel_name', Table::TYPE_TEXT, 255, ['default' => null], 'sales_channel_name')
            ->addColumn('product_name', Table::TYPE_TEXT, 255, ['default' => null], 'product_name')
            ->addColumn('product_condition', Table::TYPE_TEXT, 25, ['default' => null], 'product_condition')
            ->addColumn('categories', Table::TYPE_TEXT, 255, ['default' => null], 'categories')
            ->addColumn('description', Table::TYPE_TEXT, 65535, ['default' => null], 'description')
            ->addColumn('short_description', Table::TYPE_TEXT, 65535, ['default' => null], 'short_description')
            ->addColumn('warehouse', Table::TYPE_TEXT, 255, ['default' => null], 'warehouse')
            ->addColumn('nominal_purchase_stock', Table::TYPE_TEXT, 20, ['default' => null], 'nominal_purchase_stock')
            ->addColumn('nominal_purchase_purchase', Table::TYPE_TEXT, 255, ['default' => null], 'nominal_purchase_purchase')
            ->addColumn('nominal_purchase_sales', Table::TYPE_TEXT, 255, ['default' => null], 'nominal_purchase_sales')
            ->addColumn('condition', Table::TYPE_TEXT, 25, ['default' => null], 'condition')
            ->addColumn('featured', Table::TYPE_TEXT, 25, ['default' => null], 'featured')
            ->addColumn('variations', Table::TYPE_TEXT, 255, ['default' => null], 'variations')
            ->addColumn('type', Table::TYPE_TEXT, 50, ['default' => null], 'type')
            ->addColumn('state', Table::TYPE_TEXT, 50, ['default' => null], 'state')
            ->addColumn('status', Table::TYPE_TEXT, 50, ['default' => null], 'status')
            ->addColumn('queue_status', Table::TYPE_TEXT, 50, ['default' => null], 'queue_status')
            ->addColumn('syc_status', Table::TYPE_TEXT, 50, ['default' => null], 'syc_status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addColumn('product_type_id', Table::TYPE_TEXT, 50, ['default' => null], 'product_type_id')
            ->addColumn('collection_id', Table::TYPE_TEXT, 50, ['default' => null], 'collection_id')
            ->addColumn('season', Table::TYPE_TEXT, 255, ['default' => null], 'season')
            ->addColumn('custom_field', Table::TYPE_TEXT, 65535, ['default' => null], 'custom_field')
            ->addColumn('conf_pro_id', Table::TYPE_TEXT, 255, ['default' => null], 'conf_pro_id');
 
         $tables['bsitc_brightpearl_products_uri']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_products_uri'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('url', Table::TYPE_TEXT, 255, ['default' => null], 'url')
            ->addColumn('sync', Table::TYPE_INTEGER, 2, ['default' => null, 'unsigned' => true], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');
            
         $tables['bsitc_brightpearl_product_image']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_product_image'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_TEXT, 255, ['default' => null], 'bp_id')
            ->addColumn('mgt_id', Table::TYPE_TEXT, 255, ['default' => null], 'mgt_id')
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['default' => null], 'sku')
            ->addColumn('img_url', Table::TYPE_TEXT, 255, ['default' => null], 'img_url')
            ->addColumn('status', Table::TYPE_TEXT, 255, ['default' => null], 'status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null, 'unsigned' => true], 'store_id');

         $tables['bsitc_brightpearl_product_inventory']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_product_inventory'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_TEXT, 255, ['default' => null], 'bp_id')
            ->addColumn('total', Table::TYPE_TEXT, 65535, ['default' => null], 'total')
            ->addColumn('warehouses', Table::TYPE_TEXT, 65535, ['warehouses' => null], 'total')
            ->addColumn('status', Table::TYPE_TEXT, 255, ['default' => null], 'status')
            ->addColumn('sync', Table::TYPE_INTEGER, 2, ['default' => null, 'unsigned' => true], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');
            
         $tables['bsitc_brightpearl_purchaseorders']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_purchaseorders'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('po_id', Table::TYPE_INTEGER, 100, ['default' => null], 'po_id')
            ->addColumn('supplier_id', Table::TYPE_INTEGER, 100, ['default' => null], 'supplier_id')
            ->addColumn('reference', Table::TYPE_TEXT, 255, ['default' => null], 'reference')
            ->addColumn('createdon', Table::TYPE_DATETIME, null, ['default' => null], 'createdon')
            ->addColumn('updatedon', Table::TYPE_DATETIME, null, ['default' => null], 'updatedon')
            ->addColumn('createdbyid', Table::TYPE_INTEGER, 100, ['default' => null], 'createdbyid')
            ->addColumn('orgdeliverydate', Table::TYPE_DATETIME, null, ['default' => null], 'orgdeliverydate')
            ->addColumn('deliverydate', Table::TYPE_DATETIME, null, ['default' => null], 'deliverydate')
            ->addColumn('productid', Table::TYPE_INTEGER, 100, ['default' => null], 'productid')
            ->addColumn('productname', Table::TYPE_TEXT, 255, ['default' => null], 'productname')
            ->addColumn('productsku', Table::TYPE_TEXT, 255, ['default' => null], 'productsku')
            ->addColumn('quantity', Table::TYPE_INTEGER, 11, ['default' => null], 'quantity')
            ->addColumn('received_qty', Table::TYPE_INTEGER, 11, ['default' => 0], 'received_qty')
            ->addColumn('on_order_qty', Table::TYPE_INTEGER, 11, ['default' => 0], 'on_order_qty')
            ->addColumn('warehouseid', Table::TYPE_INTEGER, 11, ['default' => null], 'warehouseid')
            ->addColumn('stock_status', Table::TYPE_INTEGER, 11, ['default' => null], 'stock_status')
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, ['default' => null], 'store_id')
            ->addColumn('poupdateno', Table::TYPE_INTEGER, 10, ['default' => 0], 'poupdateno')
            ->addColumn('emailtoadmin', Table::TYPE_INTEGER, 10, ['default' => 0], 'emailtoadmin')
            ->addColumn('update_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'update_at');
 
         $tables['bsitc_brightpearl_salesorder_report']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_salesorder_report'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('mgt_order_id', Table::TYPE_TEXT, 100, ['default' => null], 'mgt_order_id')
            ->addColumn('mgt_customer_id', Table::TYPE_TEXT, 100, ['default' => null], 'mgt_customer_id')
            ->addColumn('bp_customer_id', Table::TYPE_TEXT, 100, ['default' => null], 'bp_customer_id')
            ->addColumn('bp_customer_status', Table::TYPE_TEXT, 100, ['default' => null], 'bp_customer_status')
            ->addColumn('bp_order_id', Table::TYPE_TEXT, 100, ['default' => null], 'bp_order_id')
            ->addColumn('bp_order_status', Table::TYPE_TEXT, 100, ['default' => null], 'bp_order_status')
            ->addColumn('bp_inventory_status', Table::TYPE_TEXT, 100, ['default' => null], 'bp_inventory_status')
            ->addColumn('bp_payment_status', Table::TYPE_TEXT, 100, ['default' => null], 'bp_payment_status')
            ->addColumn('update_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'update_at');

         $tables['bsitc_brightpearl_sales_credit']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_sales_credit'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('so_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'so_order_id')
            ->addColumn('sc_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'sc_order_id')
            ->addColumn('mgt_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_order_id')
            ->addColumn('mgt_creditmemo_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_creditmemo_id')
            ->addColumn('state', Table::TYPE_TEXT, 20, ['default' => null], 'state')
            ->addColumn('status', Table::TYPE_INTEGER, 10, ['default' => null], 'status')
            ->addColumn('order_type', Table::TYPE_TEXT, 20, ['default' => null], 'order_type')
            ->addColumn('refund_shipping', Table::TYPE_INTEGER, 10, ['default' => null], 'refund_shipping')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json');
             
         $tables['bsitc_brightpearl_sales_order']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_sales_order'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('so_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'so_order_id')
            ->addColumn('mgt_order_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_order_id')
            ->addColumn('mgt_creditmemo_id', Table::TYPE_TEXT, 20, ['default' => null], 'mgt_creditmemo_id')
            ->addColumn('state', Table::TYPE_TEXT, 20, ['default' => null], 'state')
            ->addColumn('status', Table::TYPE_INTEGER, 10, ['default' => null], 'status')
            ->addColumn('order_type', Table::TYPE_TEXT, 20, ['default' => null], 'order_type')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json');
            
 
         $tables['bsitc_brightpearl_sales_order_queue']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_sales_order_queue'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('order_id', Table::TYPE_INTEGER, 11, ['default' => null], 'order_id')
            ->addColumn('increment_id', Table::TYPE_TEXT, 50, ['default' => null], 'increment_id')
            ->addColumn('state', Table::TYPE_TEXT, 20, ['default' => null], 'Order Processing State')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
         $tables['bsitc_brightpearl_stock_transfer']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_stock_transfer'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('goodsoutnoteid', Table::TYPE_INTEGER, 100, ['default' => null], 'goodsoutnoteid')
            ->addColumn('fromwarehouseid', Table::TYPE_INTEGER, 100, ['default' => null], 'fromwarehouseid')
            ->addColumn('targetwarehouseid', Table::TYPE_INTEGER, 100, ['default' => null], 'targetwarehouseid')
            ->addColumn('stocktransferid', Table::TYPE_INTEGER, 100, ['default' => null], 'stocktransferid')
            ->addColumn('createdby', Table::TYPE_INTEGER, 100, ['default' => null], 'createdby')
            ->addColumn('shippedstatus', Table::TYPE_INTEGER, 100, ['default' => null], 'shippedstatus')
            ->addColumn('productid', Table::TYPE_TEXT, 100, ['default' => null], 'productid')
            ->addColumn('productsku', Table::TYPE_TEXT, 200, ['default' => null], 'productsku')
            ->addColumn('quantity', Table::TYPE_TEXT, 50, ['default' => null], 'quantity')
            ->addColumn('locationid', Table::TYPE_TEXT, 50, ['default' => null], 'locationid')
            ->addColumn('goodsmovementid', Table::TYPE_INTEGER, 100, ['default' => null], 'goodsmovementid')
            ->addColumn('batchid', Table::TYPE_INTEGER, 100, ['default' => null], 'batchid')
            ->addColumn('status', Table::TYPE_INTEGER, 10, ['default' => null], 'status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null], 'store_id') ;
 
         $tables['bsitc_brightpearl_suppliers']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_suppliers'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null], 'store_id')
            ->addColumn('contactid', Table::TYPE_INTEGER, 100, ['default' => null], 'contactid')
            ->addColumn('firstname', Table::TYPE_TEXT, 255, ['default' => null], 'firstname')
            ->addColumn('lastname', Table::TYPE_TEXT, 255, ['default' => null], 'lastname')
            ->addColumn('email', Table::TYPE_TEXT, 255, ['default' => null], 'email')
            ->addColumn('company', Table::TYPE_TEXT, 255, ['default' => null], 'company')
            ->addColumn('pcf_leadtime', Table::TYPE_INTEGER, 100, ['default' => null], 'pcf_leadtime')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
             
         $tables['bsitc_brightpearl_tags']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_tags'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('tag_id', Table::TYPE_INTEGER, 10, ['default' => null], 'tag_id')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null], 'store_id')
            ->addColumn('json', Table::TYPE_TEXT, 65535, ['default' => null], 'json')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
             
         $tables['bsitc_brightpearl_tax']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_tax'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('bp_id', Table::TYPE_INTEGER, 10, ['default' => null], 'bp_id')
            ->addColumn('code', Table::TYPE_TEXT, 255, ['default' => null], 'code')
            ->addColumn('description', Table::TYPE_TEXT, 255, ['default' => null], 'description')
            ->addColumn('rate', Table::TYPE_TEXT, 50, ['default' => null], 'rate')
            ->addColumn('status', Table::TYPE_TEXT, 50, ['default' => null], 'status')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
             ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
         $tables['bsitc_brightpearl_warehouse']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_warehouse'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('name', Table::TYPE_TEXT, 255, ['default' => null], 'name')
            ->addColumn('mgt_location', Table::TYPE_TEXT, 255, ['default' => null], 'mgt_location')
            ->addColumn('bp_warehouse', Table::TYPE_TEXT, 255, ['default' => null], 'bp_warehouse')
            ->addColumn('store_ids', Table::TYPE_TEXT, 50, ['default' => null], 'store_ids')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
             ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');
 
         $tables['bsitc_brightpearl_webhooks']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_webhooks'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('webhook_id', Table::TYPE_INTEGER, 10, ['default' => null], 'webhook_id')
            ->addColumn('subscribe_to', Table::TYPE_TEXT, 255, ['default' => null], 'subscribe_to')
            ->addColumn('uri_template', Table::TYPE_TEXT, 255, ['default' => null], 'uri_template')
             ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
         $tables['bsitc_brightpearl_webhooks_inventory']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_webhooks_inventory'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('account_code', Table::TYPE_TEXT, 255, ['default' => null], 'account_code')
            ->addColumn('resource_type', Table::TYPE_TEXT, 255, ['default' => null], 'resource_type')
            ->addColumn('bp_id', Table::TYPE_TEXT, 255, ['default' => null], 'bp_id')
            ->addColumn('lifecycle_event', Table::TYPE_TEXT, 255, ['default' => null], 'lifecycle_event')
            ->addColumn('full_event', Table::TYPE_TEXT, 255, ['default' => null], 'full_event')
            ->addColumn('mgt_id', Table::TYPE_TEXT, 50, ['default' => null], 'mgt_id')
            ->addColumn('sku', Table::TYPE_TEXT, 50, ['default' => null], 'sku')
            ->addColumn('old_inventory', Table::TYPE_TEXT, 50, ['default' => null], 'old_inventory')
            ->addColumn('updated_inventory', Table::TYPE_TEXT, 50, ['default' => null], 'updated_inventory')
            ->addColumn('status', Table::TYPE_TEXT, 200, ['default' => null], 'status')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
             ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('warehouse_id', Table::TYPE_TEXT, 50, ['default' => null], 'warehouse_id')
            ->addColumn('warehouse_name', Table::TYPE_TEXT, 50, ['default' => null], 'warehouse_name');
 
         $tables['bsitc_brightpearl_webhooks_update']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_brightpearl_webhooks_update'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('account_code', Table::TYPE_TEXT, 255, ['default' => null], 'account_code')
            ->addColumn('resource_type', Table::TYPE_TEXT, 255, ['default' => null], 'resource_type')
            ->addColumn('bp_id', Table::TYPE_TEXT, 255, ['default' => null], 'bp_id')
            ->addColumn('lifecycle_event', Table::TYPE_TEXT, 255, ['default' => null], 'lifecycle_event')
            ->addColumn('full_event', Table::TYPE_TEXT, 255, ['default' => null], 'full_event')
             ->addColumn('status', Table::TYPE_TEXT, 200, ['default' => null], 'status')
            ->addColumn('sync', Table::TYPE_INTEGER, 10, ['default' => null], 'sync')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['default' => null], 'updated_at')
             ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'created_at');
 
         $tables['bsitc_fullstock_syn']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_fullstock_syn'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('warehouse_id', Table::TYPE_TEXT, 50, ['default' => null], 'warehouse_id')
            ->addColumn('warehouses', Table::TYPE_TEXT, 255, ['default' => null], 'warehouses')
            ->addColumn('location_id', Table::TYPE_TEXT, 50, ['default' => null], 'location_id')
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['default' => null], 'sku')
            ->addColumn('bp_id', Table::TYPE_TEXT, 50, ['default' => null], 'bp_id')
            ->addColumn('mgt_id', Table::TYPE_TEXT, 50, ['default' => null], 'mgt_id')
            ->addColumn('mgt_qty', Table::TYPE_TEXT, 50, ['default' => null], 'mgt_qty')
            ->addColumn('bp_qty', Table::TYPE_TEXT, 50, ['default' => null], 'bp_qty')
            ->addColumn('bp_ptype', Table::TYPE_TEXT, 50, ['default' => null], 'bp_ptype')
             ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
             
         $tables['bsitc_logs']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_logs'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('category', Table::TYPE_TEXT, 255, ['default' => null], 'category')
            ->addColumn('title', Table::TYPE_TEXT, 255, ['default' => null], 'title')
            ->addColumn('error', Table::TYPE_TEXT, 65535, ['default' => null], 'error')
            ->addColumn('store_id', Table::TYPE_INTEGER, 2, ['default' => null, 'unsigned' => true], 'store_id')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['default' => null], 'created_at')
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
 
         $tables['bsitc_stock_reconciliation']  = $this->moduleDataSetup->getConnection()
            ->newTable($this->moduleDataSetup->getTable('bsitc_stock_reconciliation'))
            ->addColumn('id', Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id')
            ->addColumn('warehouse_id', Table::TYPE_TEXT, 50, ['default' => null], 'warehouse_id')
            ->addColumn('warehouses', Table::TYPE_TEXT, 255, ['default' => null], 'warehouses')
            ->addColumn('location_id', Table::TYPE_TEXT, 50, ['default' => null], 'location_id')
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['default' => null], 'sku')
            ->addColumn('bp_id', Table::TYPE_TEXT, 50, ['default' => null], 'bp_id')
            ->addColumn('mgt_qty', Table::TYPE_TEXT, 50, ['default' => null], 'mgt_qty')
            ->addColumn('bp_qty', Table::TYPE_TEXT, 50, ['default' => null], 'bp_qty')
            ->addColumn('diff', Table::TYPE_TEXT, 50, ['default' => null], 'diff')
            ->addColumn('bp_ptype', Table::TYPE_TEXT, 50, ['default' => null], 'bp_ptype')
             ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'updated_at');
              
        return $tables;
    }
}
