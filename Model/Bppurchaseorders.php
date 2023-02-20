<?php

namespace Bsitc\Brightpearl\Model;

class Bppurchaseorders extends \Magento\Framework\Model\AbstractModel
{
    
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_log;
    protected $_objectManager;
    protected $_storeManager;
    protected $_productRepository;
    public $_date;
    public $_api;
    
    public $is_enable_po;
    public $po_attribute;
    public $is_enable_email_alert;
    public $alertdays;
    public $mailtocustomer;
    public $toname;
    public $toemail;
    public $ccemail;
    public $fromName;
    public $fromemail;
     
    const XML_PATH_EMAIL_RECIPIENT_NAME = 'trans_email/ident_support/name';
    const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'trans_email/ident_support/email';
    
    const XML_PATH_EMAIL_RECIPIENT = 'test/email/send_email';
	
    public $pole;
    public $pole_msg;
    public $pole_exp_date;
	
    public $exclude_child_po_sku;
	
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Bppurchaseorders');
        $objectManager            = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_objectManager    =  $objectManager;
    }
    
    public function initializeEmailObjects()
    {
        $this->_storeManager = $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $this->_inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
        $this->_transportBuilder = $this->_objectManager->create('Magento\Framework\Mail\Template\TransportBuilder');
        $this->_scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_productRepository     = $this->_objectManager->create('Magento\Catalog\Model\ProductRepository');
        $this->_log = $this->_objectManager->create('Bsitc\Brightpearl\Model\LogsFactory');
        $this->_date = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $this->_api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $podeliveryalertConfig = $this->_scopeConfig ->getValue('bpconfiguration/podeliveryalert', $storeScope);
        $podConfigArray =  (array)$podeliveryalertConfig;

        $this->is_enable_po = isset($podConfigArray['enable']) ? $podConfigArray['enable'] : 0 ;
        $this->po_attribute = isset($podConfigArray['po_attribute']) ? $podConfigArray['po_attribute'] : 'PCF_PREORDER' ;
        $this->is_enable_email_alert = isset($podConfigArray['enable_email']) ? $podConfigArray['enable_email'] : 0 ;
        $this->alertdays = isset($podConfigArray['alertdays']) ? $podConfigArray['alertdays'] : '' ;
        $this->mailtocustomer = isset($podConfigArray['mailtocustomer']) ? $podConfigArray['mailtocustomer'] : 0 ;
        $this->toname = isset($podConfigArray['toname']) ? $podConfigArray['toname'] : '' ;
        $this->toemail = isset($podConfigArray['toemail']) ? $podConfigArray['toemail'] : '' ;
        $this->ccemail = isset($podConfigArray['ccemail']) ? $podConfigArray['ccemail'] : '' ;
        $this->fromName = isset($podConfigArray['sendername']) ? $podConfigArray['sendername'] : $this->_scopeConfig ->getValue('trans_email/ident_general/name', $storeScope);
        $this->fromemail = isset($podConfigArray['senderemail']) ? $podConfigArray['senderemail'] : $this->_scopeConfig ->getValue('trans_email/ident_general/email', $storeScope);
		
        $this->pole = isset($podConfigArray['pole']) ? $podConfigArray['pole'] : 'PCF_LTDETON' ;
        $this->pole_msg = isset($podConfigArray['pole_msg']) ? $podConfigArray['pole_msg'] : 'PCF_LEMSG' ;
        $this->pole_exp_date = isset($podConfigArray['pole_exp_date']) ? $podConfigArray['pole_exp_date'] : 'PCF_LEEXPDAT' ;

		$this->exclude_child_po_sku = isset($podConfigArray['exclude_child_po_sku']) ? $podConfigArray['exclude_child_po_sku'] : 0 ;

    }

    public function cleanPurchaseorder()
    {
        
         $currentDate = $this->_date->date('Y-m-d');
         $currentDateTimestamp = $this->_date->gmtTimestamp($currentDate);
         $collection = $this->getCollection();
        foreach ($collection as $item) {
             $deliveryDate = $this->_date->date('Y-m-d', $item->getDeliverydate());
             $leadtime = $item->getLeadtime();
            if ($leadtime > 0) {
                $deliveryDate     = date('Y-m-d', strtotime('+'.$leadtime.' day', strtotime($deliveryDate)));
            }
            $deliveryDateTimestamp = $this->_date->gmtTimestamp($deliveryDate);
            
            if ($item->getQuantity() <= 0 || $item->getOnOrderQty() <= 0 || $currentDateTimestamp > $deliveryDateTimestamp) {
                $tmpLogarray = [];
                $tmpLogarray['deliveryDate'] = $deliveryDate;
                $tmpLogarray['currentDate'] = $currentDate;
                $tmpLogarray['currentDateTimestamp'] = $currentDateTimestamp;
                $tmpLogarray['deliveryDateTimestamp'] = $deliveryDateTimestamp;
                $tmpLogarray['getOnOrderQty'] = $item->getOnOrderQty();
                $tmpLogarray['getQuantity'] = $item->getQuantity();
                $tmpLogarray['po_id'] = $item->getPoId();
                 $msg = 'Remove purchase order that\'s delivery date pass'.json_encode($tmpLogarray, true) ;
                $this->_log->recordLog($msg, "PO", "PO");
                $this->unsetPreorderProduct($item->getProductsku());
                 $item->delete();
            }
        }
        $this->cleanNotExistBpPo();
    }

    /*
    * Clean PO report for real PO
    */
    public function cleanNotExistBpPo()
    {
        /* ----- remove purchase order that are not exist on bright pearl ------------ */
         $collection = $this->getCollection();
        $collection->getSelect()->group('po_id');
        $collection->setOrder('po_id', 'ASC');
        $searchPoOnBp  = $collection ->getColumnValues('po_id');
        $searchString = implode(",", $searchPoOnBp);
        $fpundPoOnBp = [];
        if (count($searchPoOnBp)>0) {
            $filter         = 'orderTypeId=2&orderId='.$searchString; // orginal one
            $fpundPoOnBp     = array_merge($fpundPoOnBp, $this->_api->searchPurchaseOrder($filter, 'idonly'));
        }
        $result=array_diff($searchPoOnBp, $fpundPoOnBp);
        /* --- check foundPoOnBp  po custom filed unchecked for per order then remove it from Magento ---------- */
        if (count($fpundPoOnBp)>0) {
            $i = 0;
            $arrTxt = [];
            foreach ($fpundPoOnBp as $poid) {
                $retunrTypeArray[] = $poid;
                if ($i > 15) {
                    $i=0;
                    $arrTxt[] = $poid;
                    $mainarr[] = "/order/".implode(",", $arrTxt);
                    $arrTxt = [];
                } else {
                    $arrTxt[] = $poid;
                    $i++;
                }
            }
            $mainarr[] = "/order/".implode(",", $arrTxt);
            foreach ($mainarr as $value) {
                $pos = $this->_api->orderGetUri($value);
                foreach ($pos['response'] as $po) {
                    $customFields = $po['customFields'];
                    if (count($customFields) > 0 && array_key_exists($this->po_attribute, $customFields)) {
                        $isPreOrder = $customFields[$this->po_attribute];
                        if (!$isPreOrder) {
                            $result[] = $po['id'];
                        }
                    } else {
                        $result[] = $po['id'];
                    }
                }
            }
        }
        /* --- check fpundPoOnBp  po custom filed unchecked for per order then remove it from Magento ---------- */
        if (count($result)>0) {
            /* --- if we are not found any thing in fpundPoOnBp then recheck here ---------- */
            if (count($searchPoOnBp) == count($result) and count($fpundPoOnBp) < 1) {
                $result = [];
                foreach ($searchPoOnBp as $poid) {
                    $bporder = $this->_api->orderById($poid) ;
                    if (array_key_exists('response', $bporder)) {
                         $po =  $bporder['response'];
                         $result[] = $po['id'];
                    }
                }
            }
            /* ------------------------------------------------- */
            $msg = 'Remove purchase order that are not exist on bright pearl'.json_encode($result, true) ;
            $this->_log->recordLog($msg, "PO", "PO");
             $collection = $this->getCollection();
            $collection->addFieldToFilter('po_id', ['in' => $result]);
            if (count($collection)>0) {
                foreach ($collection as $item) {
                      $this->unsetPreorderProduct($item->getProductsku());
                     $item->delete();
                }
            }
        }
    }

    public function unsetPreorderProduct($productsku)
    {
		$this->_productRepository = $this->_objectManager->create('Magento\Catalog\Model\ProductRepository');
        $product = $this->_productRepository->get($productsku, false, 0);
        $product->setWkPreorder(0);
        $product->setPreorderAvailability("");
        $product->setWkPreorderQty("");
        $product->save();
    }

    public function syncFromApi()
    {
        
        $this->initializeEmailObjects();
         
        if ($this->is_enable_po == 1 and $this->is_enable_po != "") 
		{
			$allParentSKU = array();
			$allChildsPOs = array();
            $parent_pos = [];
            $website_id = '0';
            if ($this->_api->authorisationToken) 
			{
                /* orderStockStatusId =4 menas po not received, orderStatusId = 6 menas pending po */
                $updatedOn = date('Y-m-d', strtotime('-1 days')).'/'.date('Y-m-d', strtotime('+1 days'));
                $filter = 'orderTypeId=2&updatedOn='.$updatedOn;
                $mainarr = $this->_api->searchPurchaseOrder($filter) ;
                $return_type="object";
                if (count($mainarr) > 0) 
				{
                    foreach ($mainarr as $key => $value) 
					{
                        $pos = $this->_api->orderGetUri($value);
                        foreach ($pos['response'] as $po) 
						{
                            $customFields = $po['customFields'];
                            if (count($customFields) > 0 && array_key_exists($this->po_attribute, $customFields)) {
                                $parentOrderId =  $po['parentOrderId'];
                                if ($parentOrderId > 0) {
									// ================= Fixed the split PO issue ================ 
										$allChildsPOs[$parentOrderId] = $po;
									// ================= Fixed the split PO issue ================ 
									
                                    /* --------- get parent po here -----------*/
                                    $purl = '/order/'.$parentOrderId;
                                     $tmp = $this->_api->orderGetUri($purl);
                                     $tmpPoResponse  = $tmp['response'];
                                    if (count($tmpPoResponse) > 0) {
                                        $po = $tmpPoResponse[0];
										// ================= Fixed the split PO issue ================
											foreach ($po['orderRows'] as $product) {
												$allParentSKU[] = $product['productSku'];
											}
										// ================= Fixed the split PO issue ================
                                    }
                                }
                                /* ----- exclude po if delivery date pass away ------ */
                                $po_delivery = $po['delivery'];
                                if (count($po_delivery) > 0 && array_key_exists("deliveryDate", $po_delivery) && $po_delivery['deliveryDate']!="") {
                                    $deliveryArray = explode("T", $po_delivery['deliveryDate']);
                                    $deliverydate    = $deliveryArray[0];
                                    if (date('Y-m-d') <  $deliverydate) {
                                        $po_id =  $po['id'];
                                        $parent_pos[$po_id] =  $po;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /* ---------------------- process only parent po -----------------------*/
			
			// echo '<pre>'; print_r($parent_pos); echo '</pre>'; die; 
			
            $all_po_products = [];
            if (count($parent_pos) > 0) 
			{
				$chkProduct = $this->_objectManager->get('Magento\Catalog\Model\Product');
                foreach ($parent_pos as $po) {
                    /* ----------- Remove PO if inventory received ------------- */
                    if ($po['stockStatusCode'] == 'POA') {
                        $po_id = $po['id'];
                        $findPo =  $this->findPo('po_id', $po_id);
                        if ($findPo) {
                            foreach ($po['orderRows'] as $product) {
                                $productSku = $product['productSku'];
                                // $chkProduct = $this->_objectManager->get('Magento\Catalog\Model\Product');
                                if ($chkProduct->getIdBySku($productSku)) {
                                     $this->unsetPreorderProduct($productSku);
                                }
                            }
                            $this->removeRecord($findPo->getId());
                        }
                        continue;
                    }
                    /* --------------------------- */
                
                    foreach ($po['orderRows'] as $product) 
					{
                        $po_id                     = $po['id'];
                        $productSku             = $product['productSku'];
                        $productId                 = $product['productId'];
                        $qtyofchildpos             = 0 ;
                        $poReceivedQuantity     = 0;
                        $qtyofchildpos             = $this->getProductQtyInChildPos($po_id, $productSku);
                        $pogin                     = $this->getGoodsinnoteQtyForPoProducts($po_id);
                        if (count($pogin)>0) {
                            $poReceivedQuantity = $pogin[$po_id][$productId]['receivedQuantity'];
                        }
                        $sumOfParentChildQty     = $qtyofchildpos + ($product['quantity']['magnitude'] - $poReceivedQuantity) ;
                        
                        $deliveryArray             = explode("T", $po['delivery']['deliveryDate']);
                        $deliverydate            = $deliveryArray[0];
                        $row = [];
                        $row['po_id']            =    $po['id'];
                        $row['reference']        =    $po['reference'];
                        $row['createdon']        =    $po['createdOn'];
                        $row['updatedon']        =    $po['updatedOn'];
                        $row['createdbyid']        =    $po['createdById'];
                        $row['deliverydate']    =    $deliverydate;
                        $row['productid']        =    $product['productId'];
                        $row['productname']        =    $product['productName'];
                        $row['productsku']        =    $productSku;
                        $row['on_order_qty']    =    $sumOfParentChildQty;
                        $row['warehouseid']        =    $po['warehouseId'];
                        $row['supplier_id']        =    $po['parties']['supplier']['contactId'];
                        $row['website_id']        =    $website_id;
                        $row['limited_edition']        = 0;
						
						
						$customFields = $po['customFields'];
						if (count($customFields) > 0 && array_key_exists($this->pole, $customFields)) 
						{
							$row['limited_edition'] = 	$customFields[$this->pole];
							
							$row['limited_edition_msg'] = '';
							if( array_key_exists($this->pole_msg, $customFields) ) {
								$row['limited_edition_msg'] = $customFields[$this->pole_msg];
							}
							
							$row['limited_edition_exp_date'] = '';
							if(array_key_exists($this->pole_exp_date, $customFields)){
								$row['limited_edition_exp_date'] = $customFields[$this->pole_exp_date];
							}

						}
 						
                        $all_po_products[]         =     $row;
                        
                        if ($row['on_order_qty'] <= 0 || $row['productsku'] == "") {
                            continue;
                        }
                        $search = $this->findPoBySkuPoid($row['po_id'], $row['productsku']);
                        
                        //$chkProduct = $this->_objectManager->get('Magento\Catalog\Model\Product');
                        if ($chkProduct->getIdBySku($row['productsku'])) {
                            if ($search!="") {
                                $row['orgdeliverydate']    = $search->getOrgdeliverydate();
                                $latest_deliverydate     =  strtotime($row['deliverydate']);
                                $old_deliverydate         =  strtotime($search->getDeliverydate());
                                $org_deliverydate         =  strtotime($search->getOrgdeliverydate());
                                if ($latest_deliverydate > $old_deliverydate and $latest_deliverydate > $org_deliverydate) {
                                    $row['poupdateno']    =   1;
                                }
                                $this->updatePo($search->getId(), $row); // --- Update PO
                                // $this->setProductPreOrder($row);  // --- Update Product
                                /* ----- write code here for email alert to admin when po delivery date was changed  -------- */
                                
                                if ($latest_deliverydate > $old_deliverydate and $latest_deliverydate > $org_deliverydate) {
                                    $startDate     = $search->getOrgdeliverydate();
                                    $endDate    = date("Y-m-d h:i:s", strtotime($row['deliverydate']));
                                    if ($this->is_enable_email_alert == 1 and $this->alertdays != "") {
                                        if ($this->isDifferenceGreaterConfigureDays($startDate, $endDate, $this->alertdays)) {
                                            $tmp_pump = [];
                                            $tmp_pump['row'] = $row;
                                            $tmp_pump['startDate'] = $startDate;
                                            $tmp_pump['endDate'] = $endDate;
                                            $tmp_pump['alertdays'] = $this->alertdays;
                                            $this->sendMailToRelatedOrdersCustomer($row);
                                        }
                                    }
                                }
                                /* ----- write code here for email alert to admin when po delivery date was changed  -------- */
                            } else {
                                $this->addPo($row);        //---- Add PO
                                //$this->setProductPreOrder($row);  // --- Update Product
                            }
                        }
                    }
                }
            }
			
			// ================= Fixed the split PO issue ================
				if (count($allChildsPOs) > 0) 
				{
					$all_po_products = [];
					$chkProduct = $this->_objectManager->get('Magento\Catalog\Model\Product');
					foreach ($allChildsPOs as $po) 
					{
						// --------------- Remove PO if inventory received ----------------- 
						if ($po['stockStatusCode'] == 'POA') {
							$po_id = $po['id'];
							$findPo =  $this->findPo('po_id', $po_id);
							if ($findPo) {
								foreach ($po['orderRows'] as $product) {
									$productSku = $product['productSku'];
									if ($chkProduct->getIdBySku($productSku)) {
										 $this->unsetPreorderProduct($productSku);
									}
								}
								$this->removeRecord($findPo->getId());
							}
							continue;
						}
						//-------------------------------------------------------------------  
						foreach ($po['orderRows'] as $product) 
						{
							$po_id					= $po['id'];
							$productSku             = $product['productSku'];
							if($this->exclude_child_po_sku) // ----- exclude child po sku if sku exist in parent POs 
							{
								if ( in_array( $productSku, $allParentSKU ) ) {
									continue; // -------- if sku already process by parent POs 
								}
							}
							$productId				= $product['productId'];
							$poReceivedQuantity     = 0;
							$qtyofchildpos          = 0;
							$pogin                  = $this->getGoodsinnoteQtyForPoProducts($po_id);
							if (count($pogin)>0) {
								$poReceivedQuantity = $pogin[$po_id][$productId]['receivedQuantity'];
							}
							$sumOfParentChildQty     = $qtyofchildpos + ($product['quantity']['magnitude'] - $poReceivedQuantity) ;
							$deliveryArray           = explode("T", $po['delivery']['deliveryDate']);
							$deliverydate            = $deliveryArray[0];
							
							$row = [];
							$row['po_id']			= $po['id'];
							$row['reference']		= $po['reference'];
							$row['createdon']		= $po['createdOn'];
							$row['updatedon']		= $po['updatedOn'];
							$row['createdbyid']		= $po['createdById'];
							$row['deliverydate']	= $deliverydate;
							$row['productid']		= $product['productId'];
							$row['productname']		= $product['productName'];
							$row['productsku']		= $productSku;
							$row['on_order_qty']	= $sumOfParentChildQty;
							$row['warehouseid']		= $po['warehouseId'];
							$row['supplier_id']		= $po['parties']['supplier']['contactId'];
							$row['website_id']		= $website_id;
							$row['limited_edition']	= 0;
							
							$customFields = $po['customFields'];
							if (count($customFields) > 0 && array_key_exists($this->pole, $customFields)) 
							{
								$row['limited_edition'] = 	$customFields[$this->pole];
								
								$row['limited_edition_msg'] = '';
								if( array_key_exists($this->pole_msg, $customFields) ) {
									$row['limited_edition_msg'] = $customFields[$this->pole_msg];
								}
								$row['limited_edition_exp_date'] = '';
								if(array_key_exists($this->pole_exp_date, $customFields)){
									$row['limited_edition_exp_date'] = $customFields[$this->pole_exp_date];
								}
								
 							}
							
							if ($row['on_order_qty'] <= 0 || $row['productsku'] == "") {
								continue;
							}
							
							$all_po_products[] = $row;
							
							$search = $this->findPoBySkuPoid($row['po_id'], $row['productsku']);
							if ($chkProduct->getIdBySku($row['productsku'])) 
							{
								if ($search!="") 
								{
									$row['orgdeliverydate']	= $search->getOrgdeliverydate();
									$latest_deliverydate	=  strtotime($row['deliverydate']);
									$old_deliverydate		=  strtotime($search->getDeliverydate());
									$org_deliverydate		=  strtotime($search->getOrgdeliverydate());
									if ($latest_deliverydate > $old_deliverydate and $latest_deliverydate > $org_deliverydate) {
										$row['poupdateno']	=   1;
									}
									$this->updatePo($search->getId(), $row); // --- Update PO
									// ----- write code here for email alert to admin when po delivery date was changed  -------- 
									
									if ($latest_deliverydate > $old_deliverydate and $latest_deliverydate > $org_deliverydate) 
									{
										$startDate     = $search->getOrgdeliverydate();
										$endDate    = date("Y-m-d h:i:s", strtotime($row['deliverydate']));
										if ($this->is_enable_email_alert == 1 and $this->alertdays != "") {
											if ($this->isDifferenceGreaterConfigureDays($startDate, $endDate, $this->alertdays)) {
												$tmp_pump = [];
												$tmp_pump['row'] = $row;
												$tmp_pump['startDate'] = $startDate;
												$tmp_pump['endDate'] = $endDate;
												$tmp_pump['alertdays'] = $this->alertdays;
												$this->sendMailToRelatedOrdersCustomer($row);
											}
										}
									}
									// ----- write code here for email alert to admin when po delivery date was changed  --------
								} else {
									$this->addPo($row);        //---- Add PO
								}
							}
						}
					}
				}
			// ================= Fixed the split PO issue ================

            $this->cleanPurchaseorder();
            $this->cronUpdatePreOrderProductStatus(); // --- update all PO product Status
            $this->resetNonPreOrderProducts(); // --- reset non pre order product status
        } else {
            $this->_log->recordLog('PO functionality is disable in configuration', "PO", "PO");
             return true;
        }
    }
    
    public function setProductPreOrder($row)
    {
        $this->_productRepository = $this->_objectManager->create('Magento\Catalog\Model\ProductRepository');
        $productsku          = $row['productsku'];
        $on_order_qty      = (int)$row['on_order_qty'];
        $deliverydate      = date(DATE_ISO8601, strtotime($row['deliverydate']));
        //  get($sku, $editMode = false, $storeId = null, $forceReload = false)
        $product = $this->_productRepository->get($productsku, false, 0);
		
		/* ------- check if product have stock then continue no need to set it pre order ---------------- */
			$stockItem = $product->getExtensionAttributes()->getStockItem();
			if( $stockItem->getQty() > 0 ){
				$this->enableStockManagement($product->getId());
				return true;
			}
		/* ---------------------------------------------------------------------------------------------- */
		
        $product->setWkPreorder(1);
        $product->setPreorderAvailability($deliverydate);
        $product->setWkPreorderQty($on_order_qty);
        $product->save();
        
        $mgt_pid = $product->getId();
                
        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $associate = $resource->getTableName('cataloginventory_stock_item');
        
        $Inventory = $this->_objectManager->create('Bsitc\Brightpearl\Helper\Inventory');
        $allwarehouse = $this->_objectManager->create('Bsitc\Brightpearl\Model\Config\Source\Allbpwarehouse');
        $locations =  $allwarehouse->getLocationSatus();
        $onhandqtys = 0 ;
        foreach ($locations as $storeid => $name) {
            $stockid = $Inventory->getStockId($storeid);
            if ($stockid == 1) {
                $storeid = 0 ;
                     $update_sql = "UPDATE " . $associate . " SET qty = ".$onhandqtys.", website_id = ".$storeid.", low_stock_date = NULL, is_in_stock = 0, manage_stock = 0, use_config_manage_stock = 0 WHERE product_id=".$mgt_pid."  AND stock_id=". $stockid;
                      $result = $connection->query($update_sql);
            }
             $Inventory->InventoryUpdate($mgt_pid, $onhandqtys, $storeid, $stockid);
        }
    }
 
    public function getProductQtyInChildPos($parent_po_id, $search_sku)
    {
		$qtysumofallchildpos = 0 ;
		$return_type="object";
		$filter = 'orderTypeId=2&parentOrderId='.$parent_po_id; // orginal one
		$mainarr =  $this->_api->searchPurchaseOrder($filter) ;
        foreach ($mainarr as $key => $value) 
		{
            $pos = $this->_api->orderGetUri($value, $return_type);
			if ($pos->response)
			{
				foreach ($pos->response as $po) 
				{
					$pogin = $this->getGoodsinnoteQtyForPoProducts($po->id);
					if ($po->customFields->PCF_PREORDER and $po->customFields->PCF_PREORDER == 1) 
					{
						foreach ($po->orderRows as $product) 
						{
							$poReceivedQuantity = 0;
							if ( count($pogin) > 0) 
							{
								$tmp_pogin = $pogin[$po->id];
 								if ($tmp_pogin and array_key_exists($product->productId, $tmp_pogin) ){
									$poReceivedQuantity = $pogin[$po->id][$product->productId]['receivedQuantity'];
								}
							}
								
							$bpProData = $this->_api->getProductById($product->productId, $return_type);
							// if (count($bpProData)>0) {
							if($bpProData and $bpProData->response and count($bpProData->response) > 0 ) { 
								$productSku = $bpProData->response[0]->identity->sku;
							}
							if (!$productSku) {
								$productSku = @$product->productSku;
							}
							if ($productSku == $search_sku) {
								$qtysumofallchildpos  = $qtysumofallchildpos + ($product->quantity->magnitude - $poReceivedQuantity);
							}
						}
					}
				}
			}	
        }
		return $qtysumofallchildpos;
    }
    
    public function getGoodsinnoteQtyForPoProducts($po_id)
    {
		$pogin = [];
		$return_type="object";
		$result = $this->_api->getGoodsinnoteQtyForPoProducts($po_id);
		$response = $result['response'];
        if ( $response && array_key_exists("results", $response) && count($response['results']) > 0) 
		{
            $i = 0 ;
            foreach ($response['results'] as $row) {
                $purchaseOrderId = $row['0'];
                $batchId = $row['1'];
                $productId = $row['3'];
                $receivedQuantity = $row['12'];
                $onHand = $row['13'];
                if ($i == 0) {
                    $pogin[$purchaseOrderId][$productId]['receivedQuantity'] = $receivedQuantity;
                    $pogin[$purchaseOrderId][$productId]['onHand'] = $onHand     ;
                } else {
                    @$pogin[$purchaseOrderId][$productId]['receivedQuantity'] += $receivedQuantity;
                    @$pogin[$purchaseOrderId][$productId]['onHand'] += $onHand;
                }
                $i++;
            }
        }
        return $pogin;
    }
        
    public function isDifferenceGreaterConfigureDays($startDate, $endDate, $alertdays)
    {
        $flag = false;
        $date1 = strtotime($startDate);
        $date2 = strtotime($endDate);
        $dateDiff = $date2 - $date1;
        $diffDays = floor($dateDiff/(60*60*24));
        if ($diffDays > $alertdays) {
            $flag = true;
        }
        return $flag;
    }
    
    public function addPo($row)
    {
        $row['quantity'] = $row['on_order_qty'];
        $row['orgdeliverydate']    =    $row['deliverydate'];
        if (count($row)>0) {
            $this->setData($row);
            $this->save();
        }
        return true;
    }
    
    public function updatePo($id, $row)
    {
        $po =  $this->load($id);
        $po->setData($row);
        $po->setId($id);
        $po->save();
    }
    
    public function removeAllRecord()
    {
        $collection = $this->getCollection();
        $collection->walk('delete');
        return true;
    }
    
    public function removeRecord($id)
    {
        $record = $this->load($id);
        if ($record) {
            $record->delete();
        }
        return true;
    }
    
    public function updateRemainder($po_id, $productsku, $qty)
    {
        $po =  $this->findPoBySkuPoid($po_id, $productsku);
        if ($po) {
            $finalQty = $po->getQuantity() - $qty;
            $po->setQuantity($finalQty);
            $po->save();
        }
        return true;
    }
    
    public function findPo($column, $value)
    {
        $data = '';
        $collection = $this->getCollection()->addFieldToFilter($column, $value);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
        }
        return $data;
    }

    public function findPoBySkuPoid($po_id, $productsku)
    {
        $data = '';
        $collection = $this->getCollection();
        $collection->addFieldToFilter('po_id', $po_id);
        $collection->addFieldToFilter('productsku', $productsku);
        if ($collection->getSize()) {
            $data  =  $collection->getFirstItem() ;
        }
        return $data;
    }

    public function increaseRemainder($po_id, $productsku, $qty)
    {
        $po =  $this->findPoBySkuPoid($po_id, $productsku);
        if ($po) {
            $finalQty = $po->getQuantity() + $qty;
            $po->setQuantity($finalQty);
            $po->save();
        }
        return true;
    }
    
    public function sendMailToRelatedOrdersCustomer($row)
    {
        $leadtime = 0;
        $contactid         = $row['supplier_id'];
        $deliverydate      = $row['deliverydate'];
        $po_id          = $row['po_id'];
        
        $supplierObj = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpsupplier');
        $supplier = $supplierObj->findSupplier('contactid', $contactid);
        
        if ($supplier!="") {
            $leadtime = $supplier->getPcfLeadtime();
        }
        if ($leadtime > 0 and $deliverydate!="") {
            $plus_days         = "+".$leadtime." day";
            $date             = strtotime($plus_days, strtotime($deliverydate));
            $deliverydate     = date("Y-m-d", $date);
        }
        $orderporelationObj     = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bporderporelation');
        $productRepository         = $this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
        $orderRepository         = $this->_objectManager->create('Magento\Sales\Api\Data\OrderInterface');
        $collection = $orderporelationObj->getCollection()->addFieldToFilter('po_id', $row['po_id']);
        if ($collection->getSize()) {
            foreach ($collection as $_item) {
                $column        =    'deliverydate';
                $value        =    $deliverydate;
                $condition     = ['id'=>$_item->getId() ];
                $orderporelationObj->updateOrderPoRelationColumn($column, $value, $condition);
                /* ------------- prepare datato send email ---------------- */
                $_product                     = $productRepository->get($_item->getSku()); // load product by attribute
                // $order                         = $orderRepository->loadByIncrementId($_item->getOrderId()); // load order by Increment Id
                $order                         = $orderRepository->load($_item->getOrderId()); // load order by Increment Id
                $data = [];
                $data['customer_name']        = $order->getCustomerFirstname();
                $data['product_name']        = $_product->getName();
                $data['product_sku']        = $_product->getSku();
                $data['old_date']            = date("Y-m-d", strtotime($_item->getOrgdeliverydate())) ;
                $data['new_date']            = date("Y-m-d", strtotime($deliverydate)) ;
                $this->sendMail($order, $data);
            }
        }
        return true;
    }

    public function sendMail($order, $data)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->_scopeConfig ->getValue('bpconfiguration/podeliveryalert/enable', $storeScope)) {
            $toemail = $this->toemail;
            if ($this->mailtocustomer == 1) {
                $toemail = $order->getCustomerEmail();
            }
            $storeObj             = $this->_storeManager->getStore($order->getStoreId());
            $customerFName         = $order->getCustomerFirstname();
            $customerLName         = $order->getCustomerLastname();
            $customerFullName    = $customerFName .' '. $customerLName;
            $subject            = 'Delivery date changed for order #'.$order->getIncrementId();
            $data['order']        = $order;
            $data['subject']    = $subject;
            $data['store']        = $storeObj;
            
            $sender = [ 'name' =>  $this->fromName,'email' => $this->fromemail ];

            try {
                $this->_inlineTranslation->suspend();
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                
                if ($this->ccemail !="") {
                    $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('delivey_change_email_template')
                    ->setTemplateOptions(['area' => 'frontend','store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,])
                    ->setTemplateVars($data)
                    ->setFrom($sender)
                    ->addTo($toemail, $customerFullName)
                    ->addCc($this->ccemail)
                    ->getTransport();
                } else {
                    $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('delivey_change_email_template')
                    ->setTemplateOptions(['area' => 'frontend','store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,])
                    ->setTemplateVars($data)
                    ->setFrom($sender)
                    ->addTo($toemail, $customerFullName)
                    ->getTransport();
                }
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
                //$this->messageManager->addSuccess('Email sent successfully');
                return true;
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->_log->recordLog($errorMessage, "PO", "PO");
                return true;
            }
        }
        return true;
    }

    public function getTotalRemainderQtyInPo($productsku)
    {
        $collection = $this->getCollection()->addFieldToFilter('productsku', $productsku);
        $remainderQty = 0 ;
        if ($collection->getSize()) {
            foreach ($collection as $poitem) {
                $remainderQty += $poitem->getQuantity();
            }
        }
        return $remainderQty;
    }


    public function cronUpdatePreOrderProductStatus()
    {
        $this->initializeEmailObjects();
        $this->cleanPurchaseorder();
        $poSkuListAray = $this->getPreOrderProductArray();
        foreach ($poSkuListAray as $sku => $item) {
              $row = [];
             $row['productsku'] = $item['productsku'];
             $row['on_order_qty'] = $item['consolated_quantity'];
             $row['deliverydate'] = $item['exp_delivery_date'];
             $this->setProductPreOrder($row);
        }
    }
    
    public function getPreOrderProductArray()
    {
        $poListAray = [];
        $collection = $this->getCollection();
        $collection->addFieldToFilter('quantity', ['gt'=>0]);
        $collection->setOrder('deliverydate', 'ASC');
        $collection->getSelect()->joinLeft(['suppliers'=> 'bsitc_brightpearl_suppliers'], 'suppliers.contactid = main_table.supplier_id', ['suppliers.pcf_leadtime']);
        if ($collection->getSize()) {
            $poSkuArray = [];
            foreach ($collection as $item) {
                $productsku                         = $item->getProductsku();
                $po_id                                 = $item->getPoId();
                $poSkuArray[$productsku][$po_id]    = $item;
            }
            foreach ($poSkuArray as $sku => $po) {
                $i = 0;
                foreach ($po as $po_id => $item) {
                    $productsku     = $item->getProductsku();
                    $deliverydate     = $item->getDeliverydate();
                    $quantity         = $item->getQuantity();
                    $supLeadTime     = $item->getPcfLeadtime();
                    $on_order_qty     = $item->getOnOrderQty();
                    if ($i == 0) {
                        $poListAray[$productsku]['po']                     = $po_id;
                        $poListAray[$productsku]['productsku']             = $productsku;
                        $poListAray[$productsku]['on_order_qty']         = $on_order_qty;
                        $poListAray[$productsku]['deliverydate']         = $deliverydate;
                        $poListAray[$productsku]['consolated_quantity']    = $quantity;
                        $poListAray[$productsku]['pcf_leadtime']         = $supLeadTime;
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
                        $poListAray[$productsku]['exp_delivery_date'] = $exp_delivery_date;
                        $i++;
                    } else {
                        $poListAray[$productsku]['consolated_quantity'] += $quantity;
                    }
                }
            }
        }
        return $poListAray;
    }
    

    public function resetNonPreOrderProducts()
    {
         $collection = $this->getCollection()->addFieldToSelect('productsku')->getColumnValues('productsku');
        $poSkuListArray = array_unique($collection);
        $productcollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $productcollection->addAttributeToFilter('wk_preorder', '1');
        $productcollection->addAttributeToFilter('sku', ['nin' => $poSkuListArray]);
        if ($productcollection->getSize()) {
            foreach ($productcollection as $product) {
                $this->enableStockManagement($product->getId());
            }
        }
    }
    
    public function enableStockManagement($productId)
    {
		usleep(1000000); /* 1 sec = 1000000 microseconds  */
		$stockRegistry = $this->_objectManager->create('\Magento\CatalogInventory\Api\StockRegistryInterface');
		$productaction = $this->_objectManager->create('\Magento\Catalog\Model\Product\Action');
		$stockItem    =    $stockRegistry->getStockItem($productId); // load stock of that product
		$stockItem->setData('manage_stock', 1);  // stock_data  manage_stock
		$stockItem->setData('use_config_manage_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
		$stockItem->setData('is_in_stock', 1);  // use_config_manage_stock  use_config_notify_stock_qty
		$stockItem->save(); //save stock of item
		$productaction->updateAttributes([$productId], ['wk_preorder' => 0, 'preorder_availability' => '', 'wk_preorder_qty' => 0 ], 0);
    }
}
