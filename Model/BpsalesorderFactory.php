<?php

namespace Bsitc\Brightpearl\Model;

class BpsalesorderFactory extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
   // protected $_objectManager;

    public $_objectManager;
    public $_storeManager;
    public $_scopeConfig;
    public $_logManager;
    public $_api;
    public $_date;
    public $_bpsalesorder;


    public $ordercheck;
    public $refundOrder;
    public $itemCreationFactory;
    public $creditmemo;
    public $invoice;
    public $creditmemoFactory;
    public $creditmemoService;
    public $orderLoaded = null;
    public $creationArguments;
    public $trSearchResult;

    
   /* public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {

        $this->_objectManager = $objectManager;
    }
    */

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Bsitc\Brightpearl\Model\Bpsalesorder  $bpsalesorder,
        \Magento\Sales\Model\Order $ordercheck,
        \Magento\Sales\Model\RefundOrder $refundOrder,
        \Magento\Sales\Model\Order\Creditmemo\ItemCreationFactory $itemCreationFactory,
        \Magento\Sales\Model\Order\Creditmemo\CreationArguments $creationArguments,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $trSearchResult
    ) {
        $this->_objectManager   = $objectManager;
        $this->_storeManager    = $storeManager;
        $this->_api             = $api;
        $this->_logManager      = $logManager;
        $this->_date            = $date;
        $this->_bpsalesorder    = $bpsalesorder;
        $this->_scopeConfig     = $scopeConfig;

        $this->ordercheck       = $ordercheck;
        $this->refundOrder      = $refundOrder;
        $this->itemCreationFactory = $itemCreationFactory;
        $this->creditmemo       = $creditmemo;
        $this->invoice          = $invoice;
        $this->creditmemoFactory= $creditmemoFactory;
        $this->creditmemoService= $creditmemoService;
        $this->creationArguments= $creationArguments;
        $this->trSearchResult   = $trSearchResult;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpsalesorder', $arguments, false);
    }

    public function syncPoFromApi()
    {
            
        if ($this->_api->authorisationToken) {
            $days = '1';
            $data = $this->_api->getUpdatedSalesCredit($days);
            $response = $data['response'];
            if (array_key_exists("results", $response) && count($response['results'])> 0) {
                $magetnoOrdersArray = [];
                $mainarr            = [];
                $i                  = 0;
                $arrTxt             = [];
                foreach ($response['results'] as $sc) {
                    $orderIncrementId   =  trim(str_replace("#", "", $sc['7']));
                    // --- here need to check order exist in magetno or  sales credit table -----------

                    if (!$this->isMagentoOrder($orderIncrementId)) {
                        continue;
                    }

                    if ($this->isOrderExistInBpSalescredit($orderIncrementId)) {
                        continue;
                    }

                    $so_order_id        = trim($sc[10]);
                    $sc_order_id        = trim($sc[0]);
                    $mgt_order_id       = $orderIncrementId ;
                    $state              = '0';
                    $json               = '';
                    
                    if (is_numeric($orderIncrementId)) {
                        $tmp = [];
                        $tmp['sc_order_id']     = $sc_order_id;
                        $tmp['so_order_id']     = $so_order_id;
                        $tmp['mgt_order_id']    = $mgt_order_id;
                        $tmp['state']           = $state;
                        $tmp['json']            = $json;
                        $magetnoOrdersArray[$sc_order_id] = $tmp;
                        // -------- create  order url to fetch multipal order data in ssingle alpi call
                        if ($i > 15) {
                            $i=0;
                            $arrTxt[] = $sc_order_id;
                            $mainarr[] = "/order/".implode(",", $arrTxt);
                            $arrTxt = [];
                        } else {
                            $arrTxt[] = $sc_order_id;
                            $i++;
                        }
                    }
                }
                if (count($arrTxt)) {
                    $mainarr[] = "/order/".implode(",", $arrTxt);
                }
                if (count($mainarr)> 0) {
                    foreach ($mainarr as $row) {
                        $scArray = $this->_api->orderGetUri($row);
                        if (array_key_exists("response", $scArray) && count($scArray['response'])> 0) {
                            foreach ($scArray['response'] as $sc) {
                                if (array_key_exists($sc['id'], $magetnoOrdersArray)) {
                                    $finalData          = [];
                                    $finalData          = $magetnoOrdersArray[$sc['id']];
                                    $finalData['json']  = json_encode($sc, true);
                                    $this->addRecord($finalData);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function addRecord($data)
    {
        $bpSalesCredit  = $this->create();
        $bpSalesCredit->setData($data);
        $bpSalesCredit->save();
    }
 
    /* --- Check if order is in PROCESSING state for more than configured time --- */
    public function updateStuckOrderStatus()
    {
            $adminHours = 0;
        if (!$adminHours) {
            $adminHours = 2;
        }

          $pendingState = 0;
          $processingState = 1;
          $completeState = 2;
          $collection = $this->_bpsalesorder->getCollection();
          $collection->addFieldToFilter('state', ['eq'=>$processingState]);
        if (count($collection)>0) {
            foreach ($collection as $item) {
                $date_a = $this->_date->date($item->getUpdatedAt());
                $date_b = $this->_date->date();
                //$diff = $date_a->diff($date_b)->format('%h');
                $diff = $date_a->diff($date_b)->format('%i');
                if ($diff >= $adminHours) {
                    $item->setState($pendingState)->save();
                }
            }
        }
          return true;
    }

    public function checkForOrderProcessing()
    {
        $pendingState = 0;
        $processingState = 1;
        $completeState = 2;
        $collection = $this->_bpsalesorder->getCollection();
        $collection->addFieldToFilter('state', ['eq'=>$processingState]);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }
 
    public function processQueue()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $pendingState = 0;
        $processingState = 1;
        $completeState = 2;
        
        $this->updateStuckOrderStatus();
        if ($this->checkForOrderProcessing()) {
            return '';
        }

        $scConfigRefundApprove = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/ra_status_id', $storeScope);
        $scConfigNewSalesCredit = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/new_status_id', $storeScope);

        $soConfigCancleStatus = $this->_scopeConfig->getValue('bpconfiguration/bp_so_config/so_cancel_status', $storeScope);
        $soConfigDecrsQty = $this->_scopeConfig->getValue('bpconfiguration/bp_so_config/so_decreased_qty_status', $storeScope);

        $collectionAll =  $this->_bpsalesorder->getCollection()
                            ->addFieldToFilter('state', ['eq'=>$pendingState])
                            ->addFieldToFilter('order_type', ['in'=>['SO','SC']])
                             ->addFieldToFilter('status', ['in'=>[$scConfigRefundApprove,$scConfigNewSalesCredit,$soConfigCancleStatus,$soConfigDecrsQty]]);

        $collectionSO = [];
        $collectionSC = [];
        foreach ($collectionAll as $item) {
            if ($item->getOrderType() == 'SO') {
                $collectionSO[] = $item;
            } elseif ($item->getOrderType() == 'SC') {
                $collectionSC[] = $item;
            }
            $item->setState($processingState);
            $item->save();
        }

        /** -- Process START for SALES CREDIT -- **/
     
        $refund_shipping_attribute = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/refund_shipping_attribute', $storeScope);
        $nominal_code_for_shipping = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/nominal_code_for_shipping', $storeScope);
        $nominal_code_for_adjustment = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/nominal_code_for_adjustment', $storeScope);

        foreach ($collectionSC as $item) {
            $data = [];
            if (!empty($item->getJson())) {
                $orderIncId = $item->getMgtOrderId();
                $parseDataArray =  $this->parseBrightPearlJson($item->getJson(), $nominal_code_for_shipping, $nominal_code_for_adjustment);
                if (!count($parseDataArray['products'])) {
                    $logItem->setCategory('Sales Credit Memo creation')
                            ->setTitle($orderIncId)
                            ->setError("product data not found to return")
                            ->setStoreId(1)
                            ->save();
                    continue;
                }
                $data = $parseDataArray['products'];
                $shippingAmt = $parseDataArray['shipping'];
                $adjustmentAmt = $parseDataArray['adjustment'];
                $cr_id = $this->createCreditMemoRefund($orderIncId, $data, $shippingAmt, $adjustmentAmt);
                if ($cr_id && $cr_id!="") {
                    $item->setMgtCreditmemoId($cr_id);
                    $item->setState($completeState);
                    $item->save();
                }
            }
        }
        /** -- Process END for SALES CREDIT -- **/


        /** --- Process START for SALES ORDER --- **/
        
        foreach ($collectionSO as $item) {
            /** --- cancelled case start --- **/
            if ($item->getStatus() == $soConfigCancleStatus) {
                $cr_id = $this->createCreditMemoRefundFull($item->getMgtOrderId());
                if ($cr_id && $cr_id!="") {
                    $item->setMgtCreditmemoId($cr_id);
                    $item->setState($completeState);
                    $item->save();
                }
            }
            /** --- cancelled case end --- **/


            /** --- descrease qty case start --- **/
            if ($item->getStatus() == $soConfigDecrsQty) {
                $dataDecrease = [];
                $orderIncId = $item->getMgtOrderId();
                $parseDataArray =  $this->parseBrightPearlJson($item->getJson(), $nominal_code_for_shipping, $nominal_code_for_adjustment);
                if (!count($parseDataArray['products'])) {
                    $logItem->setCategory('Sales Order Credit Memo creation')
                            ->setTitle($orderIncId)
                            ->setError("product data not found to return")
                            ->setStoreId(1)
                            ->save();
                    continue;
                }

                $dataDecrease = $parseDataArray['products'];
                $recentAvailQtyData = [];
                $order =   $this->ordercheck->loadByAttribute('increment_id', $orderIncId);
                $orderItems = $order->getItems();
                 $dataToRefund = [];
                foreach ($orderItems as $ordItem) {
                    $orderedData[$ordItem->getSku()]  = $ordItem->getQtyOrdered();
                    $refundedData[$ordItem->getSku()] = $ordItem->getQtyRefunded();
                    $recentAvailQtyData[$ordItem->getSku()] = $ordItem->getQtyOrdered() - $ordItem->getQtyRefunded();
                    /*--------  check order item removed on BP ------------*/
                    if (!array_key_exists($ordItem->getSku(), $dataDecrease)) {
                         $dataToRefund[$ordItem->getSku()] = $ordItem->getQtyOrdered() - $ordItem->getQtyRefunded();
                    }
                    /*--------  check order item removed on BP ------------*/
                }
                
               
                $flagToreturn = true;
                foreach ($dataDecrease as $sku => $qty) {
                    $refundQty = $recentAvailQtyData[$sku] - $qty;
                    if ($refundQty > 0) {
                        $dataToRefund[$sku] = $refundQty;
                    }
                }
                $cr_id = $this->createCreditMemoRefund($orderIncId, $dataToRefund);
                if ($cr_id && $cr_id!="") {
                    $item->setMgtCreditmemoId($cr_id);
                    $item->setState($completeState);
                    $item->save();

                    $logdata = " OrderedQty > ".json_encode($orderedData);
                    $logdata .= " RefundedQty > ".json_encode($refundedData);
                    $logdata .= " BP Qty > ".json_encode($dataDecrease);
                    $logdata .= " Final Qty Refund> ".json_encode($dataToRefund);
                    $logItem = $this->_logManager->create();
                    $logItem->setCategory('Sales Order Descrease Process')
                        ->setTitle($orderIncId)
                        ->setError($logdata)
                        ->setStoreId(1)
                        ->save();
                }
            }
            /** --- descrease qty case end --- **/
        }
    }

    public function isOrderExistInBpSalescredit($incrementId)
    {
        $collection = $this->_bpsalesorder->getCollection();
        $collection->addFieldToFilter('mgt_order_id', ['eq'=>$incrementId]);
        if (count($collection)) {
            return true;
        } else {
            return false;
        }
    }

    public function isMagentoOrder($incrementId)
    {
        $order =   $this->ordercheck->loadByAttribute('increment_id', $incrementId);
        $this->orderLoaded = $order;
        if ($order->getId()) {
            return true;
        } else {
            return false;
        }
    }

    public function isOrderInvoiced($orderId)
    {
        $order =   $this->ordercheck->loadByAttribute('increment_id', $orderId);
        if ($order->hasInvoices()) {
            return true;
        } else {
            return false;
        }
    }

    public function isOrderPending($orderId)
    {
        $order =   $this->ordercheck->loadByAttribute('increment_id', $orderId);
        $state = $order->getState();
        if ($state == 'new' || $state == 'pending') {
            return true;
        } else {
            return false;
        }
    }


    public function createCreditMemoRefund($orderId, $bpOrderItemData, $shipmentAmt = 0, $adjustmentAmt = 0)
    {
    
        $cr_id = '';
        
        if (!$this->isMagentoOrder($orderId)) {
            $logItem = $this->_logManager->create();
            $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("Order Id not found in magento system")
                        ->setStoreId(1)
                        ->save();
            return "";
        }

        if (!$this->isOrderInvoiced($orderId)) {
            $logItem = $this->_logManager->create();
            $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("This order not invoiced in magento")
                        ->setStoreId(1)
                        ->save();
            return "";
        }

        
        $orderItems = $this->orderLoaded->getAllVisibleItems();
        $itemIdsToRefund = [];
        $orderItemIdCollection = [];
        $orderItemSkuCollection = [];
        $qtys = [];
        $tmpDecQtys = [];
        
        foreach ($orderItems as $item) {
            $orderItemIdCollection[$item->getSku()] = $item->getId();
            $orderItemSkuCollection[] = $item->getSku();
        }
       
         $logItem = $this->_logManager->create();
                $logItem->setCategory('order item removed')
                        ->setTitle($orderId)
                        ->setError(json_encode($qtys, true))
                        ->setStoreId(1)
                        ->save();
                        
                        
       
        foreach ($bpOrderItemData as $sku => $qty) {
            if (!in_array($sku, $orderItemSkuCollection)) {
                $logItem = $this->_logManager->create();
                $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("SKU $sku not exist in order data")
                        ->setStoreId(1)
                        ->save();
                continue;
            } else {
               /* $creditmemoItem = $this->itemCreationFactory->create();
                $creditmemoItem->setQty($qty)->setOrderItemId($orderItemIdCollection[$sku]);
                $itemIdsToRefund[] = $creditmemoItem;*/
                $qtys[$orderItemIdCollection[$sku]] = $qty;
            }
        }
         
        /**---  Shipping and Adjustement Fee start---**/
      /*   $creationArguments = $this->creationArguments;
         $creationArguments->setShippingAmount($shipmentAmt);
         $creationArguments->setAdjustmentPositive($adjustmentAmt);*/

         $dataRefund = [];
         $dataRefund['qtys'] = $qtys;
         $dataRefund["shipping_amount"] = $shipmentAmt;
         $dataRefund["adjustment_positive"] = $adjustmentAmt;

        //$data["adjustment_negative"];
        /* echo '<pre>';
         print_r($dataRefund);
         echo '</pre>';
         die;*/

        /**---  Shipping and Adjustement Fee end ---**/

        if (count($qtys)>0) {
            /*try{
               // $result = $this->refundOrder->execute($orderId,$itemIdsToRefund);
                 $result = $this->refundOrder->execute($orderId,
                                                       $itemIdsToRefund,
                                                       false,
                                                       false,
                                                       null,
                                                       $creationArguments);
                $creditMemo = $this->creditmemo->load($result);
                $cr_id = $creditMemo->getIncrementId();
                return $cr_id;
            }catch(\Exception $ex){
                // ---- log error
                $logItem = $this->_logManager->create();
                $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("Error while generating Credi Memo for Order ID ".$orderId)
                        ->setStoreId(1)
                        ->save();
                return '';
            }*/

            $paymentStatus = $this->checkOrderPaymentStatus($this->orderLoaded);
            $invoices = $this->orderLoaded->getInvoiceCollection();
            foreach ($invoices as $invoice) {
                $invoiceincrementid = $invoice->getIncrementId();
            }


            try {
                $invoiceobj = $this->invoice->loadByIncrementId($invoiceincrementid);
                $creditmemo = $this->creditmemoFactory->createByOrder($this->orderLoaded, $dataRefund);

                if ($paymentStatus == "online") {
                    $creditmemo->setInvoice($invoiceobj);
                }
                $cr_id = $this->creditmemoService->refund($creditmemo);
                return $cr_id->getIncrementId();
            } catch (\Exception $e) {
                // ---- log error
                $logItem = $this->_logManager->create();
                $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("Error while generating Credi Memo for Order ID ".$orderId." ".$e->getMessage())
                        ->setStoreId(1)
                        ->save();
                return '';
            }
        }
        return $cr_id;
    }

    public function createCreditMemoRefundFull($orderId)
    {
        $cr_id = '';

        if (!$this->isMagentoOrder($orderId)) {
            $logItem = $this->_logManager->create();
            $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("Order Id not found in magento system")
                        ->setStoreId(1)
                        ->save();
            return "";
        }

        if (!$this->isOrderInvoiced($orderId)) {
            $logItem = $this->_logManager->create();
            $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("This order not invoiced in magento")
                        ->setStoreId(1)
                        ->save();
            return "";
        }

        $paymentStatus = $this->checkOrderPaymentStatus($this->orderLoaded);
        $invoices = $this->orderLoaded->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            $invoiceincrementid = $invoice->getIncrementId();
        }

        try {
            $invoiceobj = $this->invoice->loadByIncrementId($invoiceincrementid);
            $creditmemo = $this->creditmemoFactory->createByOrder($this->orderLoaded);

            if ($paymentStatus == "online") {
                $creditmemo->setInvoice($invoiceobj);
            }
            $cr_id = $this->creditmemoService->refund($creditmemo);
            return $cr_id->getIncrementId();
        } catch (\Exception $ex) {
            // ---- log error
            $logItem = $this->_logManager->create();
            $logItem->setCategory('Sales Credit')
                    ->setTitle($orderId)
                    ->setError("Error while generating Credi Memo for Order ID ".$orderId." ".$e->getMessage())
                    ->setStoreId(1)
                    ->save();
            return '';
        }

        return $cr_id;
    }


    public function parseBrightPearlJson($json, $shipNominalCode = 4040, $otherNominalCode = 4030)
    {
        $finalDataToReturn = [];
        $dataProducts      = [];
        $dataShipping      = 0;
        $dataAdjustment    = 0;
        $orderData = json_decode($json, true);
        foreach ($orderData['orderRows'] as $row) {
            /* -- check for product rows -- */
            if (($row['nominalCode'] == 4000) && $row['rowValue']['rowNet']['value'] > 0) {
                $sku = $row['productSku'];
                $quantity = $row['quantity']['magnitude'];
                $dataProducts[$sku] = $quantity;
            }

            /* -- check for shipping rows -- */
            if (($row['nominalCode'] == $shipNominalCode)) {
                $dataShipping =  $dataShipping + $row['rowValue']['rowNet']['value'] + + $row['rowValue']['rowTax']['value'];
                //$dataAdjustment =  $dataAdjustment + $row['rowValue']['rowTax']['value'];
            }

            /* -- check for shipping rows -- */
            if (($row['nominalCode'] == $otherNominalCode)) {
                $dataAdjustment =  $dataAdjustment + $row['rowValue']['rowNet']['value'] + $row['rowValue']['rowTax']['value'];
            }
        }


        $finalDataToReturn['products']   =    $dataProducts;
        $finalDataToReturn['shipping']   =    $dataShipping;
        $finalDataToReturn['adjustment'] =    $dataAdjustment;

        return $finalDataToReturn;
    }

    public function checkOrderPaymentStatus($order)
    {
        $paymentStatus = "";
        $trCollection = $this->trSearchResult->create()
                             ->addOrderIdFilter($order->getId());
        
        if (count($trCollection)) {
            foreach ($trCollection as $trItem) {
                $paymentTrType = $trItem->getTxnType();
            }
        } else {
                $paymentStatus = "offline";
        }

        if (isset($paymentTrType) && $paymentTrType == "capture") {
                $paymentStatus = "online";
        }

        return $paymentStatus;
    }
}
