<?php

namespace Bsitc\Brightpearl\Model;

class BpsalescreditFactory extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
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
     
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService
    ) {
          $this->_objectManager     = $objectManager;
        $this->_storeManager     = $storeManager;
        $this->_api             = $api;
        $this->_logManager         = $logManager;
        $this->_date             = $date;
        $this->_bpsalesorder    = $bpsalesorder;
        $this->_scopeConfig        = $scopeConfig;

        $this->ordercheck         = $ordercheck;
        $this->refundOrder         = $refundOrder;
        $this->itemCreationFactory = $itemCreationFactory;
        $this->creditmemo         = $creditmemo;
        $this->invoice          = $invoice;
        $this->creditmemoFactory= $creditmemoFactory;
        $this->creditmemoService= $creditmemoService;
    }

    /**
     * Create new country model
     *
     * @param array $arguments
     * @return \Magento\Directory\Model\Country
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpsalescredit', $arguments, false);
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
                $i                     = 0;
                $arrTxt             = [];
                foreach ($response['results'] as $sc) {
                      $orderIncrementId     =  trim(str_replace("#", "", $sc['7']));
                    // --- here need to check order exist in magetno or  sales credit table -----------

                    if (!$this->isMagentoOrder($orderIncrementId)) {
                        continue;
                    }

                    if ($this->isOrderExistInBpSalescredit($orderIncrementId)) {
                        continue;
                    }

                    $so_order_id         = trim($sc[10]);
                    $sc_order_id         = trim($sc[0]);
                    $mgt_order_id         = $orderIncrementId ;
                    $state                 = '0';
                    $json                 = '';
                    
                    if (is_numeric($orderIncrementId)) {
                        $tmp = [];
                        $tmp['sc_order_id']        = $sc_order_id;
                         $tmp['so_order_id']        = $so_order_id;
                        $tmp['mgt_order_id']    = $mgt_order_id;
                        $tmp['state']            = $state;
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
                                    $finalData             = [];
                                    $finalData             = $magetnoOrdersArray[$sc['id']];
                                    $finalData['json']     = json_encode($sc, true);
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
                $diff = $date_a->diff($date_b)->format('%h');
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
            return ;
        }
         $scConfigRefundApprove = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/ra_status_id', $storeScope);
         $collection =  $this->_bpsalesorder->getCollection()
                             ->addFieldToFilter('state', ['eq'=>$pendingState])
                                ->addFieldToFilter('order_type', ['eq'=>'SC'])
                                ->addFieldToFilter('status', ['eq'=>$scConfigRefundApprove]);

        foreach ($collection as $item) {
            $item->setState($processingState);
            $item->save();
        }

        /** -- Process START for SALES CREDIT -- **/
        
        $collection =  $this->_bpsalesorder->getCollection()
                             ->addFieldToFilter('state', ['eq'=>$processingState])
                                ->addFieldToFilter('order_type', ['eq'=>'SC'])
                                ->addFieldToFilter('status', ['eq'=>$scConfigRefundApprove]);
        foreach ($collection as $item) {
            $data = [];
            if (!empty($item->getJson())) {
                    $orderIncId = $item->getMgtOrderId();
                    $orderData = json_decode($item->getJson(), true);
                foreach ($orderData['orderRows'] as $row) {
                    $sku = $row['productSku'];
                    $quantity = $row['quantity']['magnitude'];
                    $data[$sku] = $quantity;
                }
                $cr_id = $this->createCreditMemoRefund($orderIncId, $data);
                if ($cr_id && $cr_id!="") {
                    $item->setMgtCreditmemoId($cr_id);
                    $item->setState($completeState);
                    $item->save();
                }
            }
        }
         /** -- Process END for SALES CREDIT -- **/

         /** --- Process START for SALES ORDER --- **/
         $soConfigCancleStatus = $this->_scopeConfig->getValue('bpconfiguration/bp_so_config/so_cancel_status', $storeScope);
         $soConfigDecrsQty = $this->_scopeConfig->getValue('bpconfiguration/bp_so_config/so_decreased_qty_status', $storeScope);

         $collection =  $this->_bpsalesorder->getCollection()
                            ->addFieldToFilter('state', ['eq'=>$pendingState])
                            ->addFieldToFilter('order_type', ['eq'=>'SO'])
                            ->addFieldToFilter('status', ['in'=>[$soConfigCancleStatus,$soConfigDecrsQty]]);

        foreach ($collection as $item) {
            $item->setState($processingState);
            $item->save();
        }

         $collection =  $this->_bpsalesorder->getCollection()
                            ->addFieldToFilter('state', ['eq'=>$processingState])
                            ->addFieldToFilter('order_type', ['eq'=>'SO'])
                            ->addFieldToFilter('status', ['in'=>[$soConfigCancleStatus,$soConfigDecrsQty]]);
                          

         
        foreach ($collection as $item) {
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
                $orderData = json_decode($item->getJson(), true);
                foreach ($orderData['orderRows'] as $row) {
                    $sku = $row['productSku'];
                    $quantity = $row['quantity']['magnitude'];
                    $dataDecrease[$sku] = $quantity;
                }

                 $recentAvailQtyData = [];
                 $order =   $this->ordercheck->loadByAttribute('increment_id', $orderIncId);
                 $orderItems = $order->getItems();
                foreach ($orderItems as $ordItem) {
                    $orderedData[$ordItem->getSku()]  = $ordItem->getQtyOrdered();
                    $refundedData[$ordItem->getSku()] = $ordItem->getQtyRefunded();
                    $recentAvailQtyData[$ordItem->getSku()] = $ordItem->getQtyOrdered() - $ordItem->getQtyRefunded();
                }
                   
                  $dataToRefund = [];
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
                        ->setStoreid(1)
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
        $order = $this->orderLoaded;
        if ($order->hasInvoices()) {
            return true;
        } else {
            return false;
        }
    }

    public function isOrderPending($orderId)
    {
        $order =  $this->orderLoaded;
        $state = $order->getState();
        if ($state == 'new' || $state == 'pending') {
            return true;
        } else {
            return false;
        }
    }


    public function createCreditMemoRefund($orderId, $bpOrderItemData)
    {
        $cr_id = '';
        
        if (!$this->isMagentoOrder($orderId)) {
            $logItem = $this->_logManager->create();
            $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("Order Id not found in magento system")
                        ->setStoreid(1)
                        ->save();
            return "";
        }

        if (!$this->isOrderInvoiced($orderId)) {
            $logItem = $this->_logManager->create();
                $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("This order not invoiced in magento")
                        ->setStoreid(1)
                        ->save();
            return "";
        }

        
        $orderItems = $this->orderLoaded->getAllVisibleItems();
        $itemIdsToRefund = [];
        $orderItemIdCollection = [];
        $orderItemSkuCollection = [];
        
        foreach ($orderItems as $item) {
            $orderItemIdCollection[$item->getSku()] = $item->getId();
            $orderItemSkuCollection[] = $item->getSku();
        }
        $check = [];
        foreach ($bpOrderItemData as $sku => $qty) {
            if (!in_array($sku, $orderItemSkuCollection)) {
                $logItem = $this->_logManager->create();
                $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("SKU $sku not exist in order data")
                        ->setStoreid(1)
                        ->save();

                return "";
            } else {
                $creditmemoItem = $this->itemCreationFactory->create();
                $creditmemoItem->setQty($qty)->setOrderItemId($orderItemIdCollection[$sku]);
                $itemIdsToRefund[] = $creditmemoItem;
            }
        }

        if (count($itemIdsToRefund)>0) {
            try {
                $result = $this->refundOrder->execute($orderId, $itemIdsToRefund);
                $creditMemo = $this->creditmemo->load($result);
                $cr_id = $creditMemo->getIncrementId();
                return $cr_id;
            } catch (\Exception $ex) {
                // ---- log error
                $logItem = $this->_logManager->create();
                $logItem->setCategory('Sales Credit')
                      ->setTitle($orderId)
                      ->setError("Error while generating Credi Memo for Order ID ".$orderId)
                      ->setStoreid(1)
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
                        ->setStoreid(1)
                        ->save();
            return "";
        }

        if (!$this->isOrderInvoiced($orderId)) {
            $logItem = $this->_logManager->create();
            $logItem->setCategory('Sales Credit')
                        ->setTitle($orderId)
                        ->setError("This order not invoiced in magento")
                        ->setStoreid(1)
                        ->save();
            return "";
        }

        $invoices = $this->orderLoaded->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            $invoiceincrementid = $invoice->getIncrementId();
        }

        try {
            $invoiceobj = $this->invoice->loadByIncrementId($invoiceincrementid);
            $creditmemo = $this->creditmemoFactory->createByOrder($this->orderLoaded);
            $creditmemo->setInvoice($invoiceobj);
            $cr_id = $this->creditmemoService->refund($creditmemo);
             return $cr_id->getIncrementId();
        } catch (\Exception $ex) {
        // ---- log error
            $logItem = $this->_logManager->create();
            $logItem->setCategory('Sales Credit')
            ->setTitle($orderId)
            ->setError("Error while generating Credi Memo for Order ID ".$orderId)
            ->setStoreid(1)
            ->save();
            return '';
        }

         return $cr_id;
    }
}
