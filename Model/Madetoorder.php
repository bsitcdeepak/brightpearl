<?php

namespace Bsitc\Brightpearl\Model;

class Madetoorder extends \Magento\Framework\Model\AbstractModel
{
    public $_scopeConfig;
    public $_storeManager;
    public $_objectManager;
    public $_logManager;
    public $_data;
    public $is_bpcron_enable;
    protected $_connection;
    protected $_resource;
    public $_date;

    protected $_product;
    protected $_stockStateInterface;
    protected $_stockRegistry;
    protected $_productaction;
    protected $_categoryleadtimefactory;
    public $_builderstockFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\Product $product,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\Product\Action $productaction,
        \Bsitc\Brightpearl\Model\CategoryleadtimeFactory $categoryleadtimefactory,
		\Bsitc\Brightpearl\Model\BuilderstockFactory $builderstockFactory
    ) {
        $this->_objectManager     = $objectManager;
        $this->_storeManager     = $storeManager;
        $this->_scopeConfig     = $scopeConfig;
        $this->_logManager         = $logManager;
        $this->_date            = $date;
        $this->_resource        = $resource;
        $this->_connection      = $this->_resource->getConnection();

        $this->_product                     = $product;
        $this->_stockStateInterface         = $stockStateInterface;
        $this->_stockRegistry                 = $stockRegistry;
        $this->_productaction                 = $productaction;
        $this->_categoryleadtimefactory        = $categoryleadtimefactory;
		$this->_builderstockFactory        = $builderstockFactory;
        $this->configure();
    }

    protected function configure()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $data['enable']            = $this->_scopeConfig->getValue('bpconfiguration/madetoorder/enable', $storeScope);
        $data['mtocategories']    = $this->_scopeConfig->getValue('bpconfiguration/madetoorder/mtocategories', $storeScope);
        $data['mtotitle']        = $this->_scopeConfig->getValue('bpconfiguration/madetoorder/mtotitle', $storeScope);
        $data['ptocategories']    = $this->_scopeConfig->getValue('bpconfiguration/madetoorder/ptocategories', $storeScope);
        $data['ptotitle']        = $this->_scopeConfig->getValue('bpconfiguration/madetoorder/ptotitle', $storeScope);
        $data['leadtimemsg']    = $this->_scopeConfig->getValue('bpconfiguration/madetoorder/leadtimemsg', $storeScope);
        $data['leadtimedays']    = $this->_scopeConfig->getValue('bpconfiguration/madetoorder/leadtimedays', $storeScope);
        $this->_data            = $data;
        $this->is_bpcron_enable = $this->_scopeConfig->getValue('bpconfiguration/bpcron/enable', $storeScope);
    }

    public function recordLog($log_data, $title = "MTO")
    {
        $logArray = [];
        $logArray['category'] = 'Global';
        $logArray['title'] =  $title;
        $logArray['store_id'] =  1;
        $logArray['error'] =  json_encode($log_data, true);
        $this->_logManager->addLog($logArray);
        return true;
    }

    public function updateMtoCategoryProducts()
    {
        if ($this->is_bpcron_enable) {
            $configuration             = $this->_data;
            $motCategoryIdsArray    = explode(",", $configuration['mtocategories']);
            $ptoCategoryIdsArray    = explode(",", $configuration['ptocategories']);
            //$categoryIdsArray        = array_unique( array_merge($motCategoryIdsArray, $ptoCategoryIdsArray) );

            $mtoProductIds = $this->getCategoryProductIds($motCategoryIdsArray);
            if (count($mtoProductIds) > 0) {
                $this->recordLog($mtoProductIds, "updateMtoCategoryProducts");
                foreach ($mtoProductIds as $productId) {
                    $this->disableStockManagement($productId, "is_madetoorder");
                }
            }

            $ptoProductIds = $this->getCategoryProductIds($ptoCategoryIdsArray);
            if (count($ptoProductIds) > 0) {
                $this->recordLog($ptoProductIds, "updatePtoCategoryProducts");
                foreach ($ptoProductIds as $productId) {
                    $this->disableStockManagement($productId, "is_printtoorder");
                }
            }
        }
         return true;
    }

    public function getCategoryProductIds($categoryIdsArray)
    {
        $product_id = [];
        $ccp = $this->_resource->getTableName('catalog_category_product');
        $cpe = $this->_resource->getTableName('catalog_product_entity');

        $sql = $this->_connection->select()->from(['ccp' => $ccp], ['product_id']);

        $sql->join(['cpe'=> $cpe], 'cpe.entity_id = ccp.product_id', []);
        $sql->where('cpe.type_id IN (?)', ['simple','virtual']);

        $sql->where('category_id IN (?)', $categoryIdsArray);
        $sql->columns(['product_id' => new \Zend_Db_Expr("group_concat(product_id SEPARATOR ',')")]);

        $response = $this->_connection->query($sql);

        $result = $response->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $product_id = array_unique(explode(",", $result['product_id']));
            sort($product_id);
        }
         return $product_id;
    }

    public function disableStockManagementOld($productId, $type = "is_madetoorder")
    {
        usleep(1000000); /* 1 sec = 1000000 microseconds  */
        //$product    =    $this->_product->load($productId); //load product which you want to update stock
        $stockItem    =    $this->_stockRegistry->getStockItem($productId); // load stock of that product
        $stockItem->setData('manage_stock', 0);  // stock_data  manage_stock
        $stockItem->setData('use_config_manage_stock', 0);  // use_config_manage_stock  use_config_notify_stock_qty
        $stockItem->setData('is_in_stock', 0);  // use_config_manage_stock  use_config_notify_stock_qty
        $stockItem->setData('qty', 0);  // use_config_manage_stock  use_config_notify_stock_qty
        $stockItem->save(); //save stock of item
        //$product->save(); //  also save product
        $this->_productaction->updateAttributes([$productId], [$type => '1' ], 0);
        if ($type == "is_madetoorder") {
            $this->_productaction->updateAttributes([$productId], ['is_printtoorder' => '0' ], 0);
        }
        if ($type == "is_printtoorder") {
            $this->_productaction->updateAttributes([$productId], ['is_madetoorder' => '0' ], 0);
        }
    }
	
    public function disableStockManagementOld1($productId, $type = "is_madetoorder")
    {
        usleep(1000000); /* 1 sec = 1000000 microseconds  */
        $stockItem    =    $this->_stockRegistry->getStockItem($productId); // load stock of that product
		if( $stockItem->getQty() > 0 ){
			$stockItem->setData('manage_stock', 1);  // stock_data  manage_stock
			$stockItem->setData('use_config_manage_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->setData('is_in_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->save(); //save stock of item
			$this->_productaction->updateAttributes([$productId], ['is_madetoorder' => 0, 'is_printtoorder' => 0 ], 0);
		}
		else
		{
			$stockItem->setData('manage_stock', 0);  // stock_data  manage_stock
			$stockItem->setData('use_config_manage_stock', 0);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->setData('is_in_stock', 0);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->setData('qty', 0);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->save(); //save stock of item
			$this->_productaction->updateAttributes([$productId], [$type => '1' ], 0);
			if ($type == "is_madetoorder") {
				$this->_productaction->updateAttributes([$productId], ['is_printtoorder' => '0' ], 0);
			}
			if ($type == "is_printtoorder") {
				$this->_productaction->updateAttributes([$productId], ['is_madetoorder' => '0' ], 0);
			}
		}
    }

    public function disableStockManagement($productId, $type = "is_madetoorder")
    {
        usleep(1000000); /* 1 sec = 1000000 microseconds  */
        $stockItem    =    $this->_stockRegistry->getStockItem($productId); // load stock of that product
		if( $stockItem->getQty() > 0 ){
			$stockItem->setData('manage_stock', 1);  // stock_data  manage_stock
			$stockItem->setData('use_config_manage_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->setData('is_in_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->save(); //save stock of item
			$this->_productaction->updateAttributes([$productId], ['is_madetoorder' => 0, 'is_printtoorder' => 0 ], 0);
		}
		else
		{
			$stockItem->setData('manage_stock', 0);  // stock_data  manage_stock
			$stockItem->setData('use_config_manage_stock', 0);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->setData('is_in_stock', 0);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->setData('qty', 0);  // use_config_manage_stock  use_config_notify_stock_qty
			$stockItem->save(); //save stock of item
			
			$this->_productaction->updateAttributes([$productId], [$type => '1' ], 0);
			
			$search = $this->_builderstockFactory->findRecord('product_id', $stockItem->getProductId());
 			if($search and $search->getQty() > 0)
			{
				$this->_productaction->updateAttributes([$productId], ['is_printtoorder' => '0' ], 0);
				$this->_productaction->updateAttributes([$productId], ['is_madetoorder' => '0' ], 0);
			}
			else
			{
				if ($type == "is_madetoorder") {
					$this->_productaction->updateAttributes([$productId], ['is_printtoorder' => '0' ], 0);
				}
				if ($type == "is_printtoorder") {
					$this->_productaction->updateAttributes([$productId], ['is_madetoorder' => '0' ], 0);
				}
			}
		}
    }



    public function disableMtoCategoryProducts()
    {
        if ($this->is_bpcron_enable) {
            $configuration             = $this->_data;
            $motCategoryIdsArray    = explode(",", $configuration['mtocategories']);
            $ptoCategoryIdsArray    = explode(",", $configuration['ptocategories']);
            $categoryIdsArray        = array_unique(array_merge($motCategoryIdsArray, $ptoCategoryIdsArray));

            $productIds = $this->getCategoryProductIds($categoryIdsArray);
            if (count($productIds) > 0) {
                $this->recordLog($productIds, "disableMtoCategoryProducts");
                foreach ($productIds as $productId) {
                    $this->enableStockManagement($productId);
                }
            }
        }
         return true;
    }

    public function enableStockManagement($productId)
    {
        usleep(1000000); /* 1 sec = 1000000 microseconds  */
        //$product    =    $this->_product->load($productId); //load product which you want to update stock
        $stockItem    =    $this->_stockRegistry->getStockItem($productId); // load stock of that product
         $stockItem->setData('manage_stock', 1);  // stock_data  manage_stock
        $stockItem->setData('use_config_manage_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
        $stockItem->setData('is_in_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
        $stockItem->save(); //save stock of item
        //$product->save(); //  also save product
         $this->_productaction->updateAttributes([$productId], ['is_madetoorder' => 0, 'is_printtoorder' => 0 ], 0);
    }

    public function isProductInMadeToOrderCategory($_product)
    {
        $result = false;
        $configuration = $this->_data;
        $motCategoryIdsArray = explode(",", $configuration['mtocategories']);
        $productCategoryIds = $_product->getCategoryIds();
        foreach ($motCategoryIdsArray as $mtoc) {
            if (in_array($mtoc, $productCategoryIds)) {
                 $result = true;
                 break;
            }
        }
        return $result;
    }

    public function isProductInPrintToOrderCategory($_product)
    {
        $result = false;
        $configuration = $this->_data;
        $ptoCategoryIdsArray = explode(",", $configuration['ptocategories']);
        $productCategoryIds = $_product->getCategoryIds();
        foreach ($ptoCategoryIdsArray as $ptoc) {
            if (in_array($ptoc, $productCategoryIds)) {
                 $result = true;
                 break;
            }
        }
        return $result;
    }

    public function getCategoryLeadtime($cid)
    {
        $leadtime = '';
         $leadtime = $this->_categoryleadtimefactory->findRecord('category_id', $cid);
         return $leadtime;
    }

    public function getMtoProductCategoryLeadtime($product)
    {
		$_product    =    $this->_product->load($product->getId());
		$tmp = [];
		$tmp['sku'] = $_product->getSku();
        $leadtime = '';
		if($product->getLeadtime()) 
		{
				$dataArray = [];
				$dataArray['leadtime'] = $product->getLeadtime();
				$dataArray['leattime_msg'] = $product->getLeadtimemsg();
				$leadtime = new \Magento\Framework\DataObject();
				$leadtime->setData($dataArray);	
				$tmp['leadtime'] = $leadtime->getData();				
		}
		else
		{
			$productCategoryIds = $_product->getCategoryIds();
			$tmp['productCategoryIds'] = $productCategoryIds;
			$configuration        = $this->_data;
			$mto_categories        = explode(",", $configuration['mtocategories']);
			$tmp['mto_categories'] = $configuration['mtocategories'];
			foreach ($mto_categories as $mtoc) {
				if (in_array($mtoc, $productCategoryIds)) {
					$leadtime =  $this->getCategoryLeadtime($mtoc);
					$tmp['leadtime'] = $leadtime->getData();
					break;
				}
			}
			// $this->recordLog(json_encode( $tmp,true) , 'KAI'  );
		}

        return $leadtime;
    }
	
    public function getPtoProductCategoryLeadtime($product)
    {
		$_product    =    $this->_product->load($product->getId());
		$tmp = [];
		$tmp['sku'] = $_product->getSku();
        $leadtime = '';
		if($product->getLeadtime()) 
		{
				$dataArray = [];
				$dataArray['leadtime'] = $product->getLeadtime();
				$dataArray['leattime_msg'] = $product->getLeadtimemsg();
				$leadtime = new \Magento\Framework\DataObject();
				$leadtime->setData($dataArray);	
				$tmp['leadtime'] = $leadtime->getData();				
		}
		else
		{
			$productCategoryIds = $_product->getCategoryIds();
			$tmp['productCategoryIds'] = $productCategoryIds;
			$configuration         = $this->_data;
			$pto_categories        = explode(",", $configuration['ptocategories']);
			$tmp['pto_categories'] = $configuration['ptocategories'];
			foreach ($pto_categories as $ptoc) {
				if (in_array($ptoc, $productCategoryIds)) {
					$leadtime =  $this->getCategoryLeadtime($ptoc);
					$tmp['leadtime'] = $leadtime->getData();
					break;
				}
			}
			// $this->recordLog(json_encode( $tmp,true) , 'KAI'  );
		}		
        return $leadtime;
    }

    public function getMadeToOrderMsg($_product, $returnType = '')
    {
        $made_to_order_msg      = '';
        $made_to_order_array      = [];
        if ($_product->getIsMadetoorder()) {
            $configuration             = $this->_data;
            $made_to_order_title    = trim($configuration['mtotitle']);
            $mto_cat_leadtime =    $this->getMtoProductCategoryLeadtime($_product);

            if ($mto_cat_leadtime) {
                $mto_lead_time_txt   =  $mto_cat_leadtime->getLeattimeMsg();
                $mto_lead_time_days  =  $mto_cat_leadtime->getLeadtime();
            } else {
                $mto_lead_time_txt   =  trim($configuration['leadtimemsg']);
                $mto_lead_time_days  =  trim($configuration['leadtimedays']);
            }

            if (trim($made_to_order_title) == "") {
                $made_to_order_title = __('Made To Order');
            }
            $made_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][mto_id]' value='".$_product->getId()."' />";
            $made_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][is_madetoorder]' value='1' />";
            $made_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][generalinfo]' value='".$made_to_order_title."' />";
            $made_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][mto_lead_time_txt]' value='".$mto_lead_time_txt."' />";
            $made_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][mto_lead_time_days]' value='".$mto_lead_time_days."' />";
            // $made_to_order_msg .= "<span class='wk-date-title'>".__('Available in %1',$mto_lead_time_txt)."</span>";
            $made_to_order_msg .= "<span class='wk-date-title'>".__('%1 - Available in %2 ', '<strong>' . $made_to_order_title . '</strong>', $mto_lead_time_txt)."</span>";
            $made_to_order_array['mto_id']                 = $_product->getId();
            $made_to_order_array['is_madetoorder']         = 1;
            $made_to_order_array['generalinfo']         = $made_to_order_title;
            $made_to_order_array['mto_lead_time_txt']     = $mto_lead_time_txt;
            $made_to_order_array['mto_lead_time_days']     = $mto_lead_time_days;
        }

        if ($returnType != "") {
            return $made_to_order_array;
        } else {
            return $made_to_order_msg;
        }
    }

    public function getPrintToOrderMsg($_product, $returnType = '')
    {
        $print_to_order_msg	= '';
        $print_to_order_array	= [];
        if ($_product->getIsPrinttoorder()) 
		{
			$configuration = $this->_data;
            $print_to_order_title = trim($configuration['ptotitle']);
            $pto_cat_leadtime = $this->getPtoProductCategoryLeadtime($_product);
            if ($pto_cat_leadtime) {
                $pto_lead_time_txt   =  $pto_cat_leadtime->getLeattimeMsg();
                $pto_lead_time_days  =  $pto_cat_leadtime->getLeadtime();
            } else {
                $pto_lead_time_txt   =  trim($configuration['leadtimemsg']);
                $pto_lead_time_days  =  trim($configuration['leadtimedays']);
            }
            if (trim($print_to_order_title) == "") {
                $print_to_order_title = __('Print To Order');
            }
            $print_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][pto_id]' value='".$_product->getId()."' />";
            $print_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][is_printtoorder]' value='1' />";
            $print_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][generalinfo]' value='".$print_to_order_title."' />";
            $print_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][pto_lead_time_txt]' value='".$pto_lead_time_txt."' />";
            $print_to_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][pto_lead_time_days]' value='".$pto_lead_time_days."' />";
            // $print_to_order_msg .= "<span class='wk-date-title'>".__('Available in %1',$pto_lead_time_txt)."</span>";
            $print_to_order_msg .= "<span class='wk-date-title'>".__('%1 - Available in %2 ', '<strong>' . $print_to_order_title . '</strong>', $pto_lead_time_txt)."</span>";
            $print_to_order_array['pto_id']                 = $_product->getId();
            $print_to_order_array['is_printtoorder']         = 1;
            $print_to_order_array['generalinfo']             = $print_to_order_title;
            $print_to_order_array['pto_lead_time_txt']         = $pto_lead_time_txt;
            $print_to_order_array['pto_lead_time_days']     = $pto_lead_time_days;
        }
        if ($returnType != "") {
            return $print_to_order_array;
        } else {
            return $print_to_order_msg;
        }
    }

    public function getPreOrderMsg($_product, $returnType = '')
    {
        $pre_order_msg      = '';
        $pre_order_array      = [];
        if ($_product->getWkPreorder()) {
             $configuration             = $this->_data;
            // $pre_order_title        = trim($configuration['pretitle']);
            $pre_order_title         = __('Pre Order');
            $exp_delivery_date       =  trim($configuration['leadtimemsg']);
			$remainderQuantity = 0 ;
            $po_id                  =  0;
            $poObj = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Bppurchaseorders');
            $collection = $poObj->getCollection()->addFieldToFilter('productsku', $_product->getSku());
            $collection->addFieldToFilter('quantity', ['gt'=>0]);
            $collection->setOrder('deliverydate', 'ASC');
            $collection->getSelect()->joinLeft(['suppliers'=> 'bsitc_brightpearl_suppliers'], 'suppliers.contactid = main_table.supplier_id', ['suppliers.pcf_leadtime']);
            if ($collection->getSize()) {
                $po              = $collection->getFirstItem();
                $po_id          = $po->getPoId();
                $deliverydate    = $po->getDeliverydate();
                $supplier_id      = $po->getSupplierId();
                $supLeadTime     = $po->getPcfLeadtime();
				$remainderQuantity = $po->getQuantity();

                if ($supLeadTime > 0 and $deliverydate!="") {
                    $plus_days = "+".$supLeadTime." day";
                } elseif ($supLeadTime > 0 and $deliverydate == "") {
                    $deliverydate      = date("Y-m-d");
                    $plus_days = "+".$supLeadTime." day";
                } elseif ($supLeadTime == 0 and $deliverydate != "") {
                    $plus_days = "+".$supLeadTime." day";
                } else {
                    $supLeadTime          = 7;
                    $deliverydate      = date("Y-m-d");
                    $plus_days = "+".$supLeadTime." day";
                }
                $date = strtotime($plus_days, strtotime($deliverydate));
                $exp_delivery_date = date("Y-m-d", $date);
            }

            $date = date_create($exp_delivery_date);
            $exp_delivery_date = date_format($date, 'l jS F Y');
            $pre_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][pre_id]' value='".$_product->getId()."' />";
            $pre_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][is_preorder]' value='1' />";
            $pre_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][generalinfo]' value='".$pre_order_title."' />";
            $pre_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][exp_delivery_date]' value='".$exp_delivery_date."' />";
            $pre_order_msg .= "<input type='hidden' name='custom_info[".$_product->getId()."][po_id]' value='".$po_id."' />";

			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			$poleEnable = $this->_scopeConfig->getValue('bpconfiguration/podeliveryalert/pole_enable', $storeScope);
 			if($poleEnable)
			{
				$limited_edition = $po->getLimitedEdition();
				$limited_edition_msg = $po->getLimitedEditionMsg();
				$limited_edition_exp_date = $po->getLimitedEditionExpDate();

 				$todate = date("Y-m-d");
				if( $limited_edition and strtotime($limited_edition_exp_date) >  strtotime($todate) ){
					$pre_order_msg .= "<span class='wk-date-title'>".$limited_edition_msg."</span>";
 				}else{
					$pre_order_msg .= "<span class='wk-date-title'>".__('%1 - Available On %2 ', '<strong>' . $pre_order_title . '</strong>', $exp_delivery_date)."</span>";
				}
 			}else{
				$pre_order_msg .= "<span class='wk-date-title'>".__('%1 - Available On %2 ', '<strong>' . $pre_order_title . '</strong>', $exp_delivery_date)."</span>";
			}

            $pre_order_array['pre_id']                 = $_product->getId();
            $pre_order_array['is_preorder']         = 1;
            $pre_order_array['generalinfo']         = $pre_order_title;
            $pre_order_array['exp_delivery_date']     = $exp_delivery_date;
            $pre_order_array['po_id']                 = $po_id;
			$pre_order_array['remainder_quantity'] 	= $remainderQuantity;
        }

        if ($returnType != "") {
            return $pre_order_array;
        } else {
            return $pre_order_msg;
        }
    }
}
