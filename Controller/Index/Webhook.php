<?php

namespace Bsitc\Brightpearl\Controller\Index;

class Webhook extends \Magento\Framework\App\Action\Action
{

    protected $_datahelper;
    protected $_webhookinventory;
    protected $_webhookupdate;
    protected $_fulfilment;
    protected $_paymentcapture;
    protected $_paymentcapturequeue;
    protected $_bpItemsFactory;
    protected $_log;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\WebhookinventoryFactory $webhookinventory,
        \Bsitc\Brightpearl\Model\WebhookupdateFactory $webhookupdate,
        \Bsitc\Brightpearl\Model\FulfilmentFactory $fulfilmentfactory,
        \Bsitc\Brightpearl\Model\PaymentcaptureFactory $paymentcapturefactory,
        \Bsitc\Brightpearl\Model\PaymentcapturequeueFactory $paymentcapturequeuefactory,
        \Bsitc\Brightpearl\Model\BpitemsFactory $bpItemsFactory,
        \Bsitc\Brightpearl\Model\LogsFactory $logsFactory,
        \Bsitc\Brightpearl\Helper\Data $datahelper
    ) {
        $this->_datahelper              = $datahelper;
        $this->_webhookinventory     = $webhookinventory;
        $this->_webhookupdate         = $webhookupdate;
        $this->_fulfilment             = $fulfilmentfactory;
        $this->_paymentcapture         = $paymentcapturefactory;
        $this->_paymentcapturequeue    = $paymentcapturequeuefactory;
        $this->_log                 = $logsFactory;
        $this->_bpItemsFactory         = $bpItemsFactory;
        parent::__construct($context);
    }
	
	public function pp()
    {
		  
		  
		  $sku = "LSWPFS0001-2";
		  
		   $sku = "1-WA-TUL-DI-TOU-XXX-204";

		  $prdObj = $this->_objectManager->create('\Magento\Catalog\Model\ProductRepository');
		  $cat = $this->_objectManager->create('\Magento\Catalog\Model\CategoryRepository');
		  $order = $this->_objectManager->create('\Magento\Sales\Model\Order');
		 $orderObj = $order->load('234');
		$orderIncId = $orderObj->getIncrementId();
		   echo $orderIncId.'<br>';
		   $store_code = $orderObj->getStore()->getCode();
		   echo $store_code.'<br>';
		   
		    $store_id = $orderObj->getStore()->getId();
		   echo $store_id.'<<<store<br>';
		   
		   foreach ($orderObj->getAllItems() as $item)
		   {
			 //echo $item->getSku();
			 
			 if($item->getParentItem())
			 {
				 
				 echo '<< YES PARENT >> '.$item->getParentItem()->getId()." << CHILD >> ".$item->getId();
			 }else{
				  echo '<< NO PARENT >> '.$item->getId();
			 }
			 echo '<br>';
			 echo '<br>';
			}
		    
		   die;
		   
		  $prd = $prdObj->get($sku); 
		  echo $prd->getId().'<br>';
		  
		  $parent = $prd->getTypeInstance()->getParentIdsByChild($prd->getId());
		  echo '<br>';
		  echo count($parent);
		  echo '<br>';
		  print_r($parent);
		  //echo 'parenid>> '.$parent->getId();
		  die;
		  if($prd->getId())
		  {			  
			  echo $prd->getId();	
			  echo '<br>';
			  if(count($prd->getCategoryIds()))
			  {
				  foreach($prd->getCategoryIds() as $item)
				  {
					  $catob = $cat->get($item, $store_id);					  
					  $getLevel = $catob->getLevel();
					  $catName = $catob->getName();					  
					  echo '<br>';
					  echo $catob->getName();
					  break;
				  }
			  }
		  }
		  $requAttr = array(
							"color"=>"",
							"color_visual_filter_swatch_2"=>"",
							"collection_visual_filter_swatch_2"=>"",
							"product_collection"=>"",
							"style"=>"",
							"style_visual_filter_swatch_2"=>"",
							"hh"=>""
							);		
		  foreach($requAttr as $attrId => $attrVal)
		  {		
			   $attribute = $prd->getResource()->getAttribute($attrId);
			   if ($attribute)
				{
					 if( $val = $prd->getResource()->getAttribute($attrId)->getFrontend()->getValue($prd))
					 {
						 $requAttr[$attrId] = $val;
					 }
				}			 
		  } 
		 
		 echo '<pre>';
		 
		 print_r($requAttr);
		
		 echo '<br>';
		  echo '<br>';
		//echo   $prd->getResource()->getAttribute('collection')->getFrontend()->getValue($prd);
		//echo   $prd->getResource()->getAttribute('style')->getFrontend()->getValue($prd);
		//  echo $prd->getAttributeText('colour');
		  echo '<br>';
		//  echo $prd->getAttributeText('collection');
		 echo '<br>';
		//  echo $prd->getAttributeText('style');
		  
	}

    public function execute()
    {
		$this->pp();
		
		die('<br><br>dfdfdf');
		
        $data = [];
        $whObj = file_get_contents('php://input');
        $data = json_decode($whObj, true);
        $data = json_decode($whObj, true);
        $this->_log->recordLog($whObj, "Webhook", "Webhook Event Fire");
        $fullEvent = trim($data['fullEvent']);
 
        switch ($fullEvent) {
            case "product.modified":
                $this->_webhookupdate->Webhookupdate($data);
                break;
            case "order.modified.order-status":
                //$this->processOrderModifiedOrderStatus($data);
                $this->_paymentcapturequeue->addWebhookRecord($data);
                break;
            case "product.modified.on-hand-modified":
                $this->_webhookinventory->Webhookinventory($data);
                break;
            case "goods-out-note.modified.picked":
                $this->_paymentcapture->goodsOutNoteModifiedPicking($data);
                break;
            case "goods-out-note.modified.shipped":
                $this->_fulfilment->goodsOutNoteModifiedShipped($data);
                break;
            case "goods-out-note.created":
                $this->_fulfilment->goodsOutNoteModifiedShipped($data);
                break;
            case "product.destroyed":
                $this->_bpItemsFactory->productEvent($data, 'destroyed');
                break;
            case "product.created":
                $this->_bpItemsFactory->productEvent($data, 'created');
                break;
            default:
                $logger = $this->_objectManager->get("Psr\Log\LoggerInterface");
                $logger->info('fullEvent not found'); //  add logs in system.log
        }
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
    
 
      /*Update Produts*/
    public function ProductUpdated($data)
    {
        $api = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $id = $data['id'];
            if ($id) {
                $result = $api->getProductById($id);
                if ($result) {
                    $this->_datahelper->UpdateProductWebhooks($result, $id);
                }
            }
        }
    }


      /*Create Produts*/
    public function productCreate($res)
    {

        $data = $res;
        $api = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $id = $data['id'];
            $pid = $data['id'];
            if ($id) {
                    $result = $api->getProductById($id);
                    /*Set data in custom tables*/
                if ($result) {
                    $this->_datahelper->setProductsCustomtable($result);
                }
            }

            if ($pid) {
                    $priceresult = $api->getProductPriceList($id);
                if ($priceresult) {
                    $this->_datahelper->setPriceCustomtable($priceresult);
                }
            }
        }
    }

    public function processOrderModifiedOrderStatus($data)
    {
         $api = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
              $id = $data['id'];
            if ($id) {
                $result = $api->orderById($id);
                if (isset($result['response'])) {
                    $bporder = $result['response'][0];
                    $finalData = [];
                    $finalData['so_order_id']         = $bporder['id'];
                    $finalData['mgt_order_id']         = trim(str_replace("#", "", $bporder['reference']));
                    $finalData['state']             = '0';
                    $finalData['status']             = $bporder['orderStatus']['orderStatusId'];
                    $finalData['order_type']        = $bporder['orderTypeCode'];
                    $finalData['json']                 = json_encode($bporder, true);
                    $api->recordLog($finalData['json'], 'Webhook get order from id result');  // -------- Log Data
                    /* ------------- save sales credit --------------------*/
                    $bp_so_config = $api->bp_so_config;
                    if ($bporder['orderTypeCode'] == 'SO' && ($bp_so_config['so_cancel_status'] == $finalData['status'] || $bp_so_config['so_decreased_qty_status'] == $finalData['status']  )) {
                        // $api->recordLog(json_encode($finalData,true), 'Webhook SO' );  // -------- Log Data
                        $soObj = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Bpsalesorder\Collection');
                        $soObj->addFieldToFilter('so_order_id', $finalData['so_order_id']);
                        $soObj->addFieldToFilter('order_type', $finalData['order_type']);
                        $obj = $soObj->getFirstItem();
                        // --------------- check if order already exist ------------------------
                        if ($obj->getSoOrderId() == $finalData['so_order_id']) {
                            $api->recordLog('already exist SO ', 'Webhook SO');  // -------- Log Data
                            // ------------------- update if only order status is decrease qty if it is pending state  --------------------
                            if ($bp_so_config['so_decreased_qty_status'] == $finalData['status'] and $obj->getState() == '0') {
                                $api->recordLog('SO exist with pending state ', 'Webhook SO');  // -------- Log Data
                                $finalData['id'] = $obj->getId();
                                 $obj->setData($finalData);
                                 $obj->save();
                            } else {
                                $api->recordLog('SO exist but not in pending state then add new record', 'Webhook SO');  // -------- Log Data
                                $soObj     = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Bpsalesorder');
                                $soObj->setData($finalData);
                                $soObj->save();
                                 // ------------------- ignore it because  order already process or in queue --------------
                            }
                        } else {
                            // --------------- if order not exist then add it
                            $soObj     = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Bpsalesorder');
                            $soObj->setData($finalData);
                            $soObj->save();
                        }
                    }
                    /* ----------------------------------------------------*/
                    
                    /* ------------- save sales credit --------------------*/
                    $bp_sc_config = $api->bp_sc_config;
                    if ($bporder['orderTypeCode'] == 'SC') {
                        $api->recordLog('SC', 'TEST');  // -------- Log Data
                        $api->recordLog($bp_sc_config['new_status_id'], 'TEST');  // -------- Log Data
                        $api->recordLog($finalData['status'], 'TEST');  // -------- Log Data
                        //  ----------- if sales credit status New sales credit -----------
                        if ($bp_sc_config['new_status_id'] == $finalData['status']) {
                               $api->recordLog('new_status_id yes ', 'TEST');  // -------- Log Data
                            if ($this->updateBrightpearlOrderStatusReadyToRefund($bporder, $api)) {
                                $api->recordLog('updateBrightpearlOrderStatusReadyToRefund', 'TEST');  // -------- Log Data
                                $finalData['status'] = $bp_sc_config['rtr_status_id'];
                                $soObj     = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Bpsalesorder');
                                $soObj->setData($finalData);
                                $soObj->save();
                                $msg = 'webhook sales credit status update for sc'.$bporder['id'];
                                $api->recordLog($msg, 'Webhook');  // -------- Log Data
                            } else {
                                $msg = 'webhook sales credit status update failed for sc'.$bporder['id'];
                                $api->recordLog($msg, 'Webhook');  // -------- Log Data
                                $soObj     = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Bpsalesorder');
                                $soObj->setData($finalData);
                                $soObj->save();
                            }
                        }
                        
                        //  ----------- if sales credit status Refund approved -----------
                        $bp_sc_config_ra_status_id = (int)$bp_sc_config['ra_status_id'];
                        if ($bp_sc_config_ra_status_id  ==  $finalData['status']) {
                            $soObj = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Bpsalesorder\Collection');
                            $soObj->addFieldToFilter('so_order_id', $finalData['so_order_id']);
                            $soObj->addFieldToFilter('order_type', $finalData['order_type']);
                            $firstItem = $soObj->getFirstItem();
                            if ($firstItem->getSoOrderId() == $finalData['so_order_id']) {
                                $api->recordLog('postRefundPaymentForSalesCredit - 0', 'Webhook');  // -------- Log Data
                                $this->postRefundPaymentForSalesCredit($bporder, $api) ;
                                $firstItem->setStatus($finalData['status']);
                                $firstItem->setJson($finalData['json']);
                                $firstItem->save();
                            } else {
                                $msg = 'webhook sales credit status update failed for sc'.$bporder['id'];
                                $api->recordLog($msg, 'Webhook');  // -------- Log Data
                            }
                        }
                    }
                    /* ----------------------------------------------------*/
                }
            }
        }
    }

    public function updateBrightpearlOrderStatusReadyToRefund($bporder, $api)
    {
        $id             = $bporder['id'];
        $parentOrderId     = $bporder['parentOrderId'];
        $result         = $api->orderById($parentOrderId);
        $parentOrder      = $result['response'][0];
        $bp_sc_config = $api->bp_sc_config;
        $rtr_status_id =  (int)$bp_sc_config['rtr_status_id'];
        $refund_shipping_attribute =  $bp_sc_config['refund_shipping_attribute'];
        $customFields   = $parentOrder['customFields'];
        $api->recordLog($refund_shipping_attribute, '  TEST refund_shipping_attribute');
        $api->recordLog($customFields[$refund_shipping_attribute], ' customFields TEST');
        if (array_key_exists($refund_shipping_attribute, $customFields) and $customFields[$refund_shipping_attribute] == '1') {
            $api->recordLog('yes con', ' TEST');
            $order_rows  = $parentOrder['orderRows'];
            $shippingRowArray = [];
            $nominal_code_for_shipping = '4040';
            if ($bp_sc_config['ra_status_id']!= "") {
                $nominal_code_for_shipping = (int)$bp_sc_config['nominal_code_for_shipping'];
            }
         
            foreach ($order_rows as $row) {
                if ($row['nominalCode'] ==  $nominal_code_for_shipping) {
                    $shippingRowArray['productId'] = $row['productId'];
                    $shippingRowArray['productName'] = $row['productName'];
                    $shippingRowArray['quantity']['magnitude'] = $row['quantity']['magnitude'];
                    $shippingRowArray['rowValue']['taxCode'] = $row['rowValue']['taxCode'];
                    $shippingRowArray['rowValue']['rowNet']['value'] =  $row['rowValue']['rowNet']['value'];
                    $shippingRowArray['rowValue']['rowTax']['value'] =  $row['rowValue']['rowTax']['value'] ;
                    $shippingRowArray['nominalCode'] =  $row['nominalCode'];
                    break;
                }
            }
            
            $api->recordLog(json_encode($shippingRowArray, true), 'shippingRowArray');  // -------- Log Data
            
            if (count($shippingRowArray)>0) {
                $result = $api->postOrderRow($id, $shippingRowArray);
                 /* --------- if shipping row added in SC then update order status ------------------ */
                if ($result and $result['response'] !="") {
                    $msg ='shipping row added in  SC #'.$id.' from SO #'.$parentOrderId;
                    $api->recordLog($msg, 'API added shipping row in SC');  // -------- Log Data
                } else {
                    $msg = 'unable to add shipping row in credit memo  #'.$id;
                    $api->recordLog($msg, 'Webhook');  // -------- Log Data
                }
            } else {
                $msg = 'unable to found shipping row in SO  #'.$parentOrderId;
                $api->recordLog($msg, 'Webhook');  // -------- Log Data
            }
        }
        
        /* --------------------- update SC status -------------------------*/
        $orderStatusArray = [];
        $orderStatusArray['orderStatusId'] = $rtr_status_id  ;
        $result = $api->updateOrderStatus($id, $orderStatusArray);
        $api->recordLog(json_encode($result, true), 'API UpdateOrderStatus Result');  // -------- Log Data
        return true;
    }
    
    public function postRefundPaymentForSalesCreditOld($salesCredit, $api)
    {
        $api->recordLog('postRefundPaymentForSalesCredit', 'Webhook');  // -------- Log Data
         $parentOrderId     = $salesCredit['parentOrderId'];
        $result         = $api->orderById($parentOrderId);
        $salesOrder      = $result['response'][0];
        if (count($salesOrder) > 0) {
             $salesreceiptsArray = $this->createSalesreceiptsArray($salesOrder, $salesCredit, $api);
            $api->recordLog(json_encode($salesreceiptsArray, true), 'salesreceiptsArray');  // -------- Log Data
            $msg = 'Unable to create sales receipt for Sales credit '.$salesCredit['id'];
            if (count($salesreceiptsArray) > 0) {
                $api->recordLog(json_encode($salesreceiptsArray, true), 'Webhook');  // -------- Log Data
                $postSalesReceipt = $api->postSalesReceipt($salesreceiptsArray);
                $msg = 'sales receipt created successfully for Sales credit '.$salesCredit['id'].' with receipt id '.json_encode($postSalesReceipt, true) ;
            }
            $api->recordLog($msg, 'Webhook');  // -------- Log Data
        } else {
            $api->recordLog('salesOrder not found from id '.$parentOrderId, 'Webhook');  // -------- Log Data
        }
        return true;
    }
    
    public function postRefundPaymentForSalesCredit($salesCredit, $api)
    {
    
        $api->recordLog('postRefundPaymentForSalesCredit', 'Webhook');  // -------- Log Data
        $parentOrderId     = $salesCredit['parentOrderId'];
        $result         = $api->orderById($parentOrderId);
        $salesOrder      = $result['response'][0];
        if (count($salesOrder) > 0) {
            $postCustomerPaymentArray = $this->postCustomerPayment($salesOrder, $salesCredit, $api);
            $api->recordLog(json_encode($postCustomerPaymentArray, true), 'postCustomerPaymentArray');  // -------- Log Data
            $msg = 'Unable to create sales receipt for Sales credit '.$salesCredit['id'];
            if (count($postCustomerPaymentArray) > 0) {
                $api->recordLog(json_encode($postCustomerPaymentArray, true), 'Webhook');  // -------- Log Data
                $postSalesReceipt = $api->postCustomerPayment($postCustomerPaymentArray);
                $msg = 'sales post customer payment successfully for Sales credit '.$salesCredit['id'].' with receipt id '.json_encode($postSalesReceipt, true) ;
            }
            $api->recordLog($msg, 'Webhook');  // -------- Log Data
        } else {
            $api->recordLog('salesOrder not found from id '.$parentOrderId, 'Webhook');  // -------- Log Data
        }
        
        return true;
    }
    
    public function postCustomerPayment($salesOrder, $salesCredit, $api)
    {
        $orderPayment = $this->getPaymentFormOrderId($salesOrder['id'], $api);
        if ($orderPayment) {
             /* -------- create sales receipt array   -------------------- */
                $paymentsArray = [];
                $paymentsArray['paymentMethodCode'] = $orderPayment['paymentMethodCode'];
                $paymentsArray['paymentType']         = 'RECEIPT';
                $paymentsArray['orderId']             = $salesCredit['id'];
                $paymentsArray['currencyIsoCode']     = $salesCredit['currency']['orderCurrencyCode'];
                $paymentsArray['exchangeRate']         = $salesCredit['currency']['exchangeRate'];
                $paymentsArray['amountPaid']         =  $salesCredit['totalValue']['total'];
                $paymentsArray['paymentDate']         = date("Y-m-d");
                $paymentsArray['journalRef']         = "Sales receipts created by MGT for credit notes" ;
             /* -------- create sales receipt array   -------------------- */
        }
        return $paymentsArray;
    }
    
    public function getPaymentFormOrderId($orderId, $api)
    {
        $searchOrderPayment = $api->searchCustomerPaymentFormOrderId($orderId);
        $header = [];
        $orderPayment =  [];
        if (count($searchOrderPayment['response']) > 0) {
            foreach ($searchOrderPayment['response']['metaData']['columns'] as $column) {
                $header[] = $column['name'];
            }
            foreach ($searchOrderPayment['response']['results'] as $result) {
                $orderPayment = array_combine($header, $result);
                break;
            }
        }
        return $orderPayment;
    }
    
    public function createSalesreceiptsArray($salesOrder, $salesCredit, $api)
    {
        $salesreceiptsArray = [];
        $headerArray = [];
        $paymentsArray = [];
        $orderPayment = $this->getPaymentFormOrderId($salesOrder['id'], $api);
        if ($orderPayment) {
                $headerArray ['contactId'] = $salesCredit['parties']['customer']['contactId'];
                $headerArray ['bankAccountNominalCode'] = $orderPayment['paymentMethodCode'];
                $headerArray ['description'] = "Sales receipts created by MGT for credit notes" ;
                $headerArray ['taxDate'] = date("Y-m-d");
                $headerArray ['currencyId'] = $orderPayment['currencyId'];
                $headerArray ['exchangeRate'] = $salesCredit['currency']['exchangeRate'];
                $paymentsArray[0]['invoiceRef'] = 'SC-'.$salesCredit['id'];
                $paymentsArray[0]['orderId'] = $salesCredit['id'];
                $paymentsArray[0]['value'] = $orderPayment['amountPaid'];
                $paymentsArray[1]['invoiceRef'] = $salesOrder['invoices'][0]['invoiceReference'];
                $paymentsArray[1]['orderId'] = $salesOrder['id'];
                $paymentsArray[1]['value'] = $salesCredit['totalValue']['total'] * -1 ;
                $salesreceiptsArray['header'] =  $headerArray;
                $salesreceiptsArray['payments'] =  $paymentsArray;
        }
        return $salesreceiptsArray;
    }
}
