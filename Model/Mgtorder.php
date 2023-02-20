<?php

namespace Bsitc\Brightpearl\Model;
  
class Mgtorder
{
    protected $orderFactory;
    protected $orderModel;
    protected $shipmentFactory;
    protected $_authorisationFactory;
    protected $_orderRepository;
    protected $_state;
    protected $_log;
    protected $logger;
    protected $trackFactory;
    public $trSearchResult;
    public $invoice;
    public $_salesOrderReport;
    public $_api;
    public $_mgtcreditmemo;
    public $_scopeConfig;
    protected $_invoiceService;
    protected $_transaction;
    protected $_bppaymentmapFactory;
     
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $orderFactory,
        \Magento\Sales\Model\Convert\Order $orderModel,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentFactory,
        \Magento\Paypal\Model\Adminhtml\ExpressFactory $authorisationFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $trSearchResult,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Framework\App\State $state,
        \Bsitc\Brightpearl\Model\LogsFactory $logsFactory,
        \Bsitc\Brightpearl\Model\Salesorderreport $salesOrderReport,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\Mgtcreditmemo $mgtcreditmemo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Bsitc\Brightpearl\Model\BppaymentmapFactory $BppaymentmapFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderModel = $orderModel;
        $this->trackFactory = $trackFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->_authorisationFactory = $authorisationFactory;
        $this->_orderRepository = $orderRepository;
        $this->_state = $state;
        $this->_log = $logsFactory;
        $this->logger = $logger;
        $this->trSearchResult = $trSearchResult;
        $this->invoice = $invoice;
        $this->_salesOrderReport = $salesOrderReport;
        $this->_api = $api;
        $this->_mgtcreditmemo = $mgtcreditmemo;
        $this->_scopeConfig = $scopeConfig;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_bppaymentmapFactory = $BppaymentmapFactory;
         //$this->_state->setAreaCode('frontend');
    }
     
    public function createInvoice()
    {
        /* ---------- write your logic here --------- */
    }

    public function createShipment($orderIncrementId, $bpShippingData = [], $trackingIds = [])
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $tmpOrderFactory = $objectManager->create('\Magento\Sales\Api\Data\OrderInterface') ;
        $order = $tmpOrderFactory->loadByIncrementId($orderIncrementId);
        if ($order->hasInvoices()) {
            if ($order->canShip()) {
                $convertOrder = $this->orderModel; // Initialize the order shipment object
                $shipment = $convertOrder->toShipment($order);
                foreach ($order->getAllItems() as $orderItem) {
                    // Check if order item has qty to ship or is virtual
                    if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }
                    if (array_key_exists($orderItem->getSku(), $bpShippingData)) {
                         $qtyShipped = $orderItem->getQtyToShip();
                        if ($bpShippingData[$orderItem->getSku()]  <= $qtyShipped) {
                            $bpQtyShipped = $bpShippingData[$orderItem->getSku()];
                            // Create shipment item with qty
                            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($bpQtyShipped);
                            // Add shipment item to shipment
                            $shipment->addItem($shipmentItem);
                        } else {
                            $msg = '<br>Shipping qty more then qty available for shipped. AQ : '.$qtyShipped.' == SQ : '.$bpShippingData[$orderItem->getSku()];
                            $this->_log->recordLog($msg, "Create Shipment 1", "Order");
                        }
                    } else {
                        $msg =  '<br>Shipping Sku '.$orderItem->getSku().' not found in order #'.$orderIncrementId;
                        $this->_log->recordLog($msg, "Create Shipment 2", "Order");
                    }
                }
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);
                try {
                    if (count($trackingIds) > 0) {
                        foreach ($trackingIds as $trackingId) {
                            $data = [
                                'carrier_code' => $trackingId['carrier_code'],
                                'title' => $trackingId['title'],
                                'number' => $trackingId['number']
                            ];
                            $track = $this->trackFactory->create()->addData($data);
                            $shipment->addTrack($track)->save();
                        }
                    }
                    $source = $this->getStockSourceForShipment($order->getStoreId());
                    $shipment->getExtensionAttributes()->setSourceCode($source);
                    $shipment->save();
                    $shipment->getOrder()->save();
                    // Send email
                    $this->shipmentFactory->notify($shipment);
                    $shipment->save();
                    $msg = 'Shipment #'.$shipment->getId().' successfully created for the order #'.$orderIncrementId;
                    $this->_log->recordLog($msg, "Create Shipment 3", "Order");
                    return $shipment->getIncrementId();
                } catch (\Exception $e) {
                    //$this->logger->info($e->getMessage());
                    $this->_log->recordLog('Exception : '. json_encode($e->getMessage(), true), "Create Shipment 4", "Order");
                    return false;
                }
            } else {
                //$this->logger->info('You can not create an shipment:' . $orderIncrementId);
                $msg = 'You can not create an shipment:' . $orderIncrementId;
                $this->_log->recordLog($msg, "Create Shipment 5", "Order");
                return false;
            }
        } else {
            $this->logger->info('Invoice is not created for order:' . $orderIncrementId);
            $msg = 'You can not create an shipment:' . $orderIncrementId;
            $this->_log->recordLog($msg, "Create Shipment 6", "Order");
            return false;
        }
    }
        
    public function addTrack($shipment, $carrierCode, $description, $trackingNumber)
    {
        /** Creating Tracking */
        /** @var Track $track */
        $track = $this->trackFactory->create();
        $track->setCarrierCode($carrierCode);
        $track->setDescription($description);
        $track->setTrackNumber($trackingNumber);
        $shipment->addTrack($track);
        $this->_shipmentRepository->save($shipment);
        /* Notify the customer*/
        $this->_shipmentNotifier->notify($shipment);
    }
    
    public function capturePayment($orderIncrementId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $offline_capture = $this->_scopeConfig->getValue('bpconfiguration/payment_capture_config/offline_capture', $storeScope);
        //$order = $this->orderFactory->loadByIncrementId($orderIncrementId);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $tmpOrderFactory = $objectManager->create('\Magento\Sales\Api\Data\OrderInterface') ;
        $order = $tmpOrderFactory->loadByIncrementId($orderIncrementId);
        if ($order) {
            $invoiceState = 1 ;
            if (count($order->getInvoiceCollection()) >0) {
                foreach ($order->getInvoiceCollection() as $invoice) {
                    $invoiceObj = $this->invoice->loadByIncrementId($invoice->getIncrementId());
                    if ($invoiceObj->canCapture()) {
                        $this->_log->recordLog('Can Capture Invoice', $invoice->getIncrementId(), "Invoice");
                        foreach ($invoiceObj->getAllItems() as $item) {
                            if ($item->getQty() > 0) {
                                $item->register();
                            } else {
                                $item->isDeleted(true);
                            }
                        }
                        if (!$offline_capture) {
                            $invoiceObj->capture();
                        } else {
                            $invoiceObj->setCanVoidFlag(false);
                            $invoiceObj->pay();
                        }
                        //----------------------------------------------------------------
                        /*
                        $this->_log->recordLog('canCapture', $invoice->getIncrementId(), "Invoice" );
                        // $invoiceObj->setRequestedCaptureCase(true);

                        if ($offline_capture) {
                            $invoiceObj->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                        } else {
                            $invoiceObj->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        }
                        $invoiceObj->register();
                        // $invoiceObj->save();
                        */
                        $invoiceObj->save();
                        if ($invoiceObj->getState() == 2) {
                            $msg = 'A. Capture the amount against the order (#'.$order->getIncrementId().') Invoice (#'.$invoice->getIncrementId().')';
                            $invoiceState =  $invoiceObj->getState();
                        } else {
                            $msg = 'A0 Unable to capture the amount against the order (#'.$order->getIncrementId().') Invoice (#'.$invoice->getIncrementId().')';
                            $this->_log->recordLog($msg, $invoice->getIncrementId(), "Invoice");
                        }
                    } else {
                            $msg = 'A1 Unable to capture the amount against the order (#'.$order->getIncrementId().') Invoice (#'.$invoice->getIncrementId().')';
                            $this->_log->recordLog($msg, $invoice->getIncrementId(), "Invoice");
                    }
                }
            } else {
                $this->_log->recordLog('Creating  and capture Invoice ', $order->getIncrementId(), "Invoice");
                if ($order->canInvoice()) {
                    $invoice = $this->_invoiceService->prepareInvoice($order);
                    /*
                    if ($offline_capture) {
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                    } else {
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                    }
                    */
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                    $invoice->register();
                    $invoice->save();
                    $transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();
                    //$this->invoiceSender->send($invoice);
                    //send email notification code
                    $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                    ->setIsCustomerNotified(true)
                    ->save();
                    if ($invoice->getState() == 2) {
                        $msg = 'B. Capture the amount against the order (#'.$order->getIncrementId().') Invoice (#'.$invoice->getIncrementId().')';
                        $invoiceState =  $invoice->getState();
                    } else {
                        $msg = 'B0 Unable to create  and capture invoice against the Order (#'.$order->getIncrementId().') Invoice (#'.$invoice->getIncrementId().')';
                        $this->_log->recordLog($msg, $invoice->getIncrementId(), "Invoice");
                    }
                } else {
                    $this->_log->recordLog('B1 Unable to create  and capture Invoice', $order->getIncrementId(), "Invoice");
                }
            }
            return $invoiceState;
        }
    }
    
    public function checkOrderPaymentStatus($order)
    {
        $paymentStatus = "";
        $trCollection = $this->trSearchResult->create()->addOrderIdFilter($order->getId());
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

    public function postCreditMemoToBP($creditmemo, $returnstockJson = [])
    {
        $returnResponse = false;
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/enable', $storeScope);
        $sc_status_id = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/sc_status_id', $storeScope);
        $sc_return_wh = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/sc_return_wh', $storeScope);
        if ($enable) {
            $isalreadyExist = $this->_mgtcreditmemo->findRecord('mgt_creditmemo_id', $creditmemo->getIncrementId());
            if ($isalreadyExist) {
                $msg = 'Credit memo already in post report - '.$creditmemo->getIncrementId();
                $this->_log->recordLog($msg, 'Already Posted Creditmemo', "Creditmemo");
                 return $returnResponse ;
            }
            $order = $creditmemo->getOrder();
            $row = ['mgt_creditmemo_id'=>$creditmemo->getIncrementId(), 'mgt_order_id'=>$order->getIncrementId(),'json'=>json_encode($returnstockJson, true) ];
            $crReportId = $this->_mgtcreditmemo->addRecord($row, 'returnInsertId');
            $returnResponse = true;
            $sorResults = $this->_salesOrderReport->findRecord('mgt_order_id', $order->getIncrementId());
            foreach ($sorResults as $sorResult) {
                $bporderid = $sorResult['bp_order_id'];
                $mgtorderid = $sorResult['mgt_order_id'];
                break;
            }
            if ($sorResults) {
                $row = ['so_order_id' => $bporderid];
                $this->_mgtcreditmemo->updateRecord($crReportId, $row);
                if ($this->_api->authorisationToken) {
                    $response     = $this->_api->orderById($bporderid);
                    if (array_key_exists('response', $response)) {
                        $bpOrder = $response['response'][0]; // --------- get order detail from Brightpearl
                        $scDataArray = [];
                        $scDataArray['customerId'] = $bpOrder['parties']['customer']['contactId'];
                        $scDataArray['ref'] = $mgtorderid;
                        $scDataArray['placedOn'] = date(DATE_ISO8601, strtotime($creditmemo->getCreatedAt()));
                        $scDataArray['taxDate'] = date(DATE_ISO8601, strtotime($creditmemo->getCreatedAt()));
                        $scDataArray['parentId'] = $bpOrder['id'];
                        $scDataArray['statusId'] = $sc_status_id;
                        // $scDataArray['warehouseId'] = $bpOrder['warehouseId'];
                        $scDataArray['channelId'] = $bpOrder['assignment']['current']['channelId'];
                        //$scDataArray['externalRef'] = $bpOrder['id'];
                        $scDataArray['externalRef'] = $creditmemo->getIncrementId();
                        $scDataArray['installedIntegrationInstanceId'] = time();
                        // $scDataArray['leadSourceId'] = $bpOrder['assignment']['current']['leadSourceId'];
                        $scDataArray['teamId'] = $bpOrder['assignment']['current']['teamId'];
                        $scDataArray['priceListId'] = $bpOrder['priceListId'];
                        $scDataArray['priceModeCode'] = $bpOrder['priceModeCode'];
                        $scDataArray['currency']['code'] = $bpOrder['currency']['orderCurrencyCode'];
                    
                        if ($bpOrder['currency']['exchangeRate'] == 1) {
                            $scDataArray['currency']['fixedExchangeRate'] = false;
                        } else {
                            $scDataArray['currency']['fixedExchangeRate'] = true;
                            $scDataArray['currency']['exchangeRate'] = $bpOrder['currency']['exchangeRate'];
                        }
                        $scDataArray['delivery']['date'] = $bpOrder['delivery']['deliveryDate'];
                        $scDataArray['delivery']['address'] = $bpOrder['parties']['delivery'];
                        $scDataArray['delivery']['shippingMethodId'] = $bpOrder['delivery']['shippingMethodId'];
                        $OrderedItemArray = [];
                        $OrderedItemNoSkuArray = [];
                        $OrderedItemRowIdArray = [];
                        foreach ($bpOrder['orderRows'] as $key => $row) {
                            if (array_key_exists("productSku", $row)) {
                                $tmp = [];
                                $sku = $row['productSku'];
                                $tmp['productId'] = $row['productId'];
                                $tmp['name'] = $row['productName'];
                                $tmp['quantity'] = $row['quantity']['magnitude'];
                                $tmp['taxCode'] = $row['rowValue']['taxCode'];
                                $tmp['net'] = $row['rowValue']['rowNet']['value'];
                                $tmp['tax'] = $row['rowValue']['rowTax']['value'];
                                $tmp['nominalCode'] = $row['nominalCode'];
                                $tmp['sku'] = $row['productSku'];
                                $OrderedItemArray[$tmp['sku']] = $tmp;
                                $OrderedItemRowIdArray [ $row['productId'] ] = $key;
                            } else {
                                $tmp = [];
                                $tmp['productId'] = $row['productId'];
                                $tmp['name'] = $row['productName'];
                                $tmp['quantity'] = $row['quantity']['magnitude'];
                                $tmp['taxCode'] = $row['rowValue']['taxCode'];
                                $tmp['net'] = $row['rowValue']['rowNet']['value'];
                                $tmp['tax'] = $row['rowValue']['rowTax']['value'];
                                $tmp['nominalCode'] = $row['nominalCode'];
                                $OrderedItemNoSkuArray[$row['productName']] = $tmp;
                            }
                        }
                        $crmItemArray = [];
                        $returnStockItemArray = [];
                        $tmpParentSkuWh = [];
                        foreach ($creditmemo->getAllItems() as $item) {
                            if ($item->getOrderItem()->getParentItem()) {
                                $tmpParentSkuWh[$item->getSku()] = $item;
                            } else {
                                continue;
                            }
                        }
                        $i= 0 ;
                        foreach ($creditmemo->getAllItems() as $item) {
                            if ($item->getOrderItem()->getParentItem()) {
                                continue;
                            } else {
                                $discount_tax_compensation_amount =  0;
                                if ($item->getDiscountTaxCompensationAmount()  > 0) {
                                    $discount_tax_compensation_amount = $item->getDiscountTaxCompensationAmount();
                                }
                                 $rowTax = $item->getTaxAmount() + $discount_tax_compensation_amount;
                                if ($item->getWarehouseId()) {
                                     $destinationLocationId = $this->getDestinationLocationId($item, $bpOrder['warehouseId']);
                                } else {
                                    if (array_key_exists($item->getSku(), $tmpParentSkuWh)) {
                                        $tmpParentItem = $tmpParentSkuWh[$item->getSku()];
                                        $destinationLocationId = $this->getDestinationLocationId($tmpParentItem, $bpOrder['warehouseId']);
                                    } else {
                                        $destinationLocationId = $this->getDestinationLocationId($item, $bpOrder['warehouseId']);
                                    }
                                }
                                 /* ------ replace sales credit warhouse by first item return warehouse in sales credit --------- */
                                if ($i == 0) {
                                     $scDataArray['warehouseId'] = $destinationLocationId;
                                    $i++;
                                }
                                if (array_key_exists($item->getSku(), $OrderedItemArray)) {
                                    $crItem = [];
                                    $crItem = $OrderedItemArray[$item->getSku()];
                                    $crItem['quantity'] = intval($item->getQty());
                                    $crItem['net'] = $item->getRowTotal();
                                    $crItem['tax'] = $rowTax;
                                    $crmItemArray[] = $crItem ;
                                    /* -------- check if item return to stock yes or no ------------*/
                                    if (array_key_exists($item->getSku(), $returnstockJson)) {
                                        $purchaseOrderRowId = $OrderedItemRowIdArray[$crItem['productId'] ];
                                        $bpItemRow = $bpOrder['orderRows'][$purchaseOrderRowId];
                                        $returnStockItemArray[] = [
                                          'productId'             => $crItem['productId'],
                                          'purchaseOrderRowId'    => $OrderedItemRowIdArray[$crItem['productId'] ],
                                          'quantity'              => $crItem['quantity'],
                                          'destinationLocationId' => $destinationLocationId,
                                          'productValue'          => [
                                              'currency' => $bpItemRow['itemCost']['currencyCode'],
                                              'value'    => $bpItemRow['itemCost']['value'],
                                          ],
                                           ];
                                    }
                                     /* -------- return to stock or not ------------*/
                                } elseif (array_key_exists($item->getName(), $OrderedItemNoSkuArray)) {
                                    /* ----------- add if there is no sku for row ----------------- */
                                    $crItem = [];
                                    $crItem = $OrderedItemNoSkuArray[$item->getName()];
                                    $crItem['quantity'] = intval($item->getQty());
                                    $crItem['net'] = $item->getRowTotal();
                                    $crItem['tax'] = $rowTax;
                                    $crmItemArray[] = $crItem ;
                                } else {
                                    /* ----------- add if there is no name and no sku match found  for row ----------------- */
                                    $taxCode = $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/notaxcode', $storeScope);
                                    if ($item->getTaxAmount()) {
                                        $taxCode = $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/taxcode', $storeScope);
                                    }
                                    $tmpNominal  =  $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/order_product_nominal', $storeScope);
                                    $crItem = [];
                                    $crItem['name'] = $item->getName();
                                    $crItem['quantity'] = intval($item->getQty());
                                    $crItem['taxCode'] = $taxCode;
                                    $crItem['net'] = $item->getRowTotal();
                                    $crItem['tax'] = $rowTax;
                                    $crItem['nominalCode'] = $tmpNominal;
                                    $crmItemArray[] = $crItem ;
                                }
                            }
                        }
                        /* ----------- add shipping returns ----------------- */
                        if ($creditmemo->getShippingAmount() > 0) {
                            $taxCode = $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/notaxcode', $storeScope);
                            $rowTax = '0.00';
                            if ($creditmemo->getShippingTaxAmount() && $creditmemo->getShippingTaxAmount() > 0) {
                                $taxCode = $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/taxcode', $storeScope);
                                $rowTax = number_format($creditmemo->getShippingTaxAmount(), '2', '.', '');
                            }
                            $rowTotal = number_format($creditmemo->getShippingAmount(), '2', '.', '');
                            $shipingKey          = 'Shipping Method - '.$order->getShippingDescription();
                            if (array_key_exists($shipingKey, $OrderedItemNoSkuArray)) {
                                $crItem = [];
                                $crItem = $OrderedItemNoSkuArray[$shipingKey];
                                $crItem['taxCode'] = $taxCode ;
                                $crItem['net'] = $rowTotal;
                                $crItem['tax'] = $rowTax ;
                                $crmItemArray[] = $crItem ;
                            } else {
                                $nominalCode =  $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/order_shipping_nominal', $storeScope);
                                $crItem = [];
                                $crItem['name'] = $shipingKey;
                                $crItem['quantity'] = "1";
                                $crItem['taxCode'] = $taxCode ;
                                $crItem['net'] = $rowTotal;
                                $crItem['tax'] = $rowTax ;
                                $crItem['nominalCode'] = $nominalCode;
                                $crmItemArray[] = $crItem ;
                            }
                        }
                        /* ---------------- add refund discount ------------- */
                        if ($creditmemo->getDiscountAmount() != 0) {
                            $discount_amount    = $creditmemo->getDiscountAmount();
                            if ($creditmemo->getDiscountTaxCompensationAmount() > 0) {
                                $discount_amount =  $discount_amount + $creditmemo->getDiscountTaxCompensationAmount();
                                $taxCode =  $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/taxcode', $storeScope);
                                $rowTax =  $creditmemo->getDiscountTaxCompensationAmount() * -1 ;
                            } else {
                                $taxCode = $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/notaxcode', $storeScope);
                                $rowTax = '0.00';
                            }
                            $discountKey = 'Discount : ' . $creditmemo->getDiscountDescription();
                            if (array_key_exists($discountKey, $OrderedItemNoSkuArray)) {
                                $crItem = [];
                                $crItem = $OrderedItemNoSkuArray[$discountKey];
                                $crItem['taxCode']  = $taxCode;
                                $crItem['net'] = $discount_amount;
                                $crItem['tax'] = $rowTax;
                                $crmItemArray[] = $crItem ;
                            } else {
                                $nominalCode =  $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/order_discount_nominal', $storeScope);
                                $crItem = [];
                                $crItem['name'] = $discountKey;
                                $crItem['quantity'] = "1";
                                $crItem['taxCode'] = $taxCode;
                                $crItem['net'] = $discount_amount;
                                $crItem['tax'] = $rowTax;
                                $crItem['nominalCode'] = $nominalCode;
                                $crmItemArray[] = $crItem ;
                            }
                        }
                        /* ---------------- add adjustment row ------------------  */
                        if ($creditmemo->getAdjustment() > 0) {
                            $nominalCode     =  $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/adjustment_refunds_nominal', $storeScope);
                            $taxCode = $this->_scopeConfig->getValue('bpconfiguration/bp_orderconfig/notaxcode', $storeScope);
                            $crItem = [];
                            $crItem['name'] = "Adjustment Refund";
                            $crItem['quantity'] = "1";
                            $crItem['taxCode'] = $taxCode;
                            $crItem['net'] = $creditmemo->getAdjustment();
                            $crItem['tax'] = "0.0000";
                            $crItem['nominalCode'] = $nominalCode;
                            $crmItemArray[] = $crItem ;
                        }
                        /* -------------------------------------------------- */
                        $scDataArray['rows']    = $crmItemArray;
                        $msg = 'Post Sales Credit '.json_encode($scDataArray, true) ;
                        $this->_log->recordLog($msg, 'Post Sales Credit Data', "creditmemo");
                        /* ---------- Post Sales Credit ---------- */
                        $postSalesCreditResponse = $this->_api->postSalesCredit($scDataArray);
                        $msg = 'Post Sales Credit Response - '.json_encode($postSalesCreditResponse, true) ;
                        $this->_log->recordLog($msg, 'Post Sales Credit Response', "creditmemo");
                        $sc_order_id = '';
                        if (array_key_exists("response", $postSalesCreditResponse)) {
                            $sc_order_id = $postSalesCreditResponse['response'];
                            $row = ['sc_order_id'=> $sc_order_id,'status'=> 1 ];
                            $this->_mgtcreditmemo->updateRecord($crReportId, $row);
                        }
                        $sc_payment_status = 0;
                        if ($sc_order_id) {
                            /* --------------- Update parent order status ------------ */
                                $this->updateParentOrderStatus($creditmemo, $sc_order_id, $order, $bpOrder);
                            /* --------------- Update parent order status ------------ */
                            /* ---------- Post Sales Credit Received ---------- */
                            // $res = $this->postSalesCreditReceived($creditmemo, $sc_order_id, $order, $bpOrder);
                            if (!$this->isPostPaymentToCreditmemo($creditmemo)) {
                                if ($creditmemo->getCustomerBalTotalRefunded() > 0) {
                                    $customer_bal_total_refunded = 'yes';
                                    $postPaymentRefund = $this->postCustomerStoreCreditRefund($order, $bpOrder, $sc_order_id, $creditmemo, $customer_bal_total_refunded);
                                    $msg = 'Store Credit Payment Refund to Customer Response - '.json_encode($postPaymentRefund, true) ;
                                    $this->_log->recordLog($msg, 'Post Payment Refund to Customer', "paymentrefund");
                                    if ($postPaymentRefund) {
                                        $sc_payment_status = 1;
                                    }
                                } else {
                                    if ($creditmemo->getGrandTotal() > 0) {
                                        /*Post Payment Refund to Customer*/
                                        $postPaymentRefund = $this->postCustomerPaymentRefund($order, $bpOrder, $sc_order_id, $creditmemo);
                                        $msg = 'Post Payment Refund to Customer Response - '.json_encode($postPaymentRefund, true) ;
                                        $this->_log->recordLog($msg, 'Post Payment Refund to Customer', "paymentrefund");
                                        if ($postPaymentRefund) {
                                            $sc_payment_status = 1;
                                        }
                                    }
                                    /* --------------------- post store credit refund to customer ---------------*/
                                    if ($creditmemo->getCustomerBalanceAmount() != 0) {
                                        $postPaymentRefund = $this->postCustomerStoreCreditRefund($order, $bpOrder, $sc_order_id, $creditmemo);
                                        $msg = 'Store Credit Payment Refund to Customer Response - '.json_encode($postPaymentRefund, true) ;
                                        $this->_log->recordLog($msg, 'Post Payment Refund to Customer', "paymentrefund");
                                        if ($postPaymentRefund) {
                                            $sc_payment_status = 1;
                                        }
                                    }
                                    /* --------------------- post store credit refund to customer ---------------*/
                                }
                                if ($sc_payment_status) {
                                    $row = ['status'=> '1'];
                                    $this->_mgtcreditmemo->updateRecord($crReportId, $row);
                                }
                            } else {
                                $msg = 'Skip Payment pos for web order #'.$creditmemo->getIncrementId().' return in pos ';
                                $this->_log->recordLog($msg, 'Check skip Payment for Weborder', "creditmemo");
                            }
                            /* ------------ Post goods in note for stock return item ---------*/
                            if (count($returnStockItemArray) > 0) {
                                $salesCreditRowId = [];
                                $salesCreditRowIds = $this->getSalesCreditRowIds($sc_order_id) ;
                                if (!count($salesCreditRowId)>0) {
                                    usleep(300000); /* 1 sec = 1000000 microseconds  */
                                    $salesCreditRowIds = $this->getSalesCreditRowIds($sc_order_id) ;
                                }
                                foreach ($returnStockItemArray as $returnStockItem) {
                                    $scpid = $returnStockItem['productId'];
                                    if (array_key_exists($scpid, $salesCreditRowIds)) {
                                        $purchaseOrderRowId = $salesCreditRowIds[$scpid];
                                        $returnStockItem['purchaseOrderRowId'] = $purchaseOrderRowId;
                                        $warehouseId = $returnStockItem['destinationLocationId'];
                                        $returnStockItem['destinationLocationId'] = $this->getWarehouseDefaultLocation($warehouseId);
                                        /* ---- create good in note in base currency ------ */
                                        $bpOrderCurrency = $bpOrder['currency'];
                                        if ($bpOrderCurrency['accountingCurrencyCode'] != $bpOrderCurrency['orderCurrencyCode']) {
                                            $returnStockItem['productValue']['currency'] = $bpOrderCurrency['accountingCurrencyCode'];
                                            $returnStockItem['productValue']['value'] = $returnStockItem['productValue']['value'] / $bpOrderCurrency['exchangeRate'];
                                        }
                                        $goodsMoved = [];
                                        $goodsMoved[] = $returnStockItem;
                                        $data = [
                                            'orderId'     => $sc_order_id,
                                            'transfer'    => false,
                                            'warehouseId' => $warehouseId,
                                            'goodsMoved'  => $goodsMoved,
                                            'receivedOn'  => date('c'),
                                        ];
                                        $msg = 'Good In Note Stock Post '.json_encode($data, true) ;
                                        $this->_log->recordLog($msg, 'Good In Note', "Good In Note");
                                        $result     = $this->_api->postGoodsInNote($sc_order_id, $data);
                                        if ($result !="") {
                                            $msg = 'Good In Note Post response  - '.$result ;
                                            $this->_log->recordLog($msg, 'Post Payment Refund to Customer', "paymentrefund");
                                        } else {
                                            $msg = 'Error to post the Good In Note againest the sales credit '.$sc_order_id;
                                            $this->_log->recordLog($msg, 'Good In Note', "Good In Note");
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $msg = 'Brightpearl spi Authorisation Token Failed';
                    $this->_log->recordLog($msg, 'Post Sales Credit Data', "creditmemo");
                }
            } else {
                $msg = 'Order not found in sent order report';
                $this->_log->recordLog($msg, 'Post Sales Credit Data', "creditmemo");
            }
        } else {
            $msg = 'Functionality disable in configuration';
            $this->_log->recordLog($msg, 'Post Sales Credit Data', "creditmemo");
        }
        return $returnResponse;
    }
    
    /* -------------- get sales credit row Ids -------------------------*/
    public function getSalesCreditRowIds($sc_order_id)
    {
        $salesCredit = $this->_api->getSalesCreditById($sc_order_id) ;
        $salesCrediRows = [];
        if (array_key_exists('response', $salesCredit)) {
            if (array_key_exists('0', $salesCredit['response'])) {
                $sc  = $salesCredit['response'][0];
                foreach ($sc['rows'] as $row) {
                    $rowId = $row['id'];
                    $productId = $row['productId'];
                    $salesCrediRows[$productId] = $rowId;
                }
            }
        }
        return $salesCrediRows;
    }
    
    /* ----------update parent order status ----------- */
    public function updateParentOrderStatus($creditmemo, $sc_order_id, $order, $bpOrder)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $fully_refunded     = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/scf_status_id', $storeScope);
        $partially_refunded = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/scp_status_id', $storeScope);
        $crmTotal = 0;
        $creditMemos = $order->getCreditmemosCollection();
        foreach ($creditMemos as $item) { //go through all the credit memos for the current order.
            if ($item->getCustomerBalTotalRefunded() > 0) {
                $crmTotal = $crmTotal + $item->getCustomerBalTotalRefunded();
            } else {
                $crmTotal = $crmTotal + ( $item->getGrandTotal() + $item->getCustomerBalanceAmount() ) ;
            }
        }
        $finalOrderStatus = $partially_refunded;
        $crmTotal = floatval($crmTotal);
        $orderGrandTotal = floatval($order->getGrandTotal() + $order->getCustomerBalanceAmount());
        if ($crmTotal == $orderGrandTotal) {
            $finalOrderStatus = $fully_refunded;
        }
        if ($finalOrderStatus !="") {
            $bpOrderStatusArray = [];
            $bpOrderStatusArray['orderStatusId'] = $finalOrderStatus;
            $bpOrderId = $bpOrder['id'];
            $this->_api->updateOrderStatus($bpOrderId, $bpOrderStatusArray) ;
            $msg = 'crefitmemo total '.$crmTotal.' order total #'.$orderGrandTotal ;
            $msg .= 'Order #'.$bpOrderId.' status update due to sales credit #'.$sc_order_id.' Final Status'.$finalOrderStatus;
            $this->_log->recordLog($msg, 'Update Paent Order Status', 'creditmemo');
        }
    }
    
    /*Post Sales Credit Received*/
    public function postSalesCreditReceived($creditmemo, $sc_order_id, $order, $bpOrder)
    {
        if ($sc_order_id > 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helpersalesorder = $objectManager->create('Bsitc\Brightpearl\Helper\SalesOrder');
            $webhookcontroller = $objectManager->create('Bsitc\Brightpearl\Controller\Index\Webhook');
            $orderTotal = number_format($creditmemo->getGrandTotal(), '2', '.', '');
            $description = 'Payment Received form  Magento against the Sales Credit #'.$order->getIncrementId();
            $bankAccountNominalCode = '';
            $currencyId = '';
            $bpOrderId = $bpOrder['id'];
            $orderPayment = $webhookcontroller->getPaymentFormOrderId($bpOrderId, $this->_api);
            $currencyId = $orderPayment['currencyId'];
            $payment = $order->getPayment();
            $paymentcode =  $payment->getMethod();
            $ordeCurrencyCode =  $order->getOrderCurrencyCode();
            if ($paymentcode) {
                 // $bankAccountNominalCode = $helpersalesorder->getMapPaymentMethod($paymentcode);
                 $bankAccountNominalCode = $helpersalesorder->getPaymentBankAccountNominalCode($paymentcode, $ordeCurrencyCode);
            }
            $invoiceRef = "#SCR".$bpOrderId;
            $salesreceiptsArray = [];
            $headerArray = [];
            $paymentsArray = [];
            $headerArray ['contactId'] = $bpOrder['parties']['customer']['contactId'];
            $headerArray ['bankAccountNominalCode'] = $bankAccountNominalCode;
            $headerArray ['description'] = $description ;
            $headerArray ['taxDate'] = date("Y-m-d");
            $headerArray ['currencyId'] = $currencyId;
            $headerArray ['exchangeRate'] = $bpOrder['currency']['exchangeRate'];
            $paymentsArray[0]['orderId'] = $bpOrderId;
            $paymentsArray[0]['value'] = $orderTotal;
            $paymentsArray[1]['invoiceRef'] = $invoiceRef;
            $paymentsArray[1]['orderId'] = $bpOrderId;
            $paymentsArray[1]['value'] = "-".$orderTotal;
            $paymentsArray[1]['adjustment']['value'] = 0;
            $paymentsArray[1]['adjustment']['nominalCode'] = $bankAccountNominalCode;
            $salesreceiptsArray['header'] =  $headerArray;
            $salesreceiptsArray['payments'] =  $paymentsArray;
            $msg = 'Sales Credit receipt array for order #'.$bpOrderId.' Array  : '.json_encode($salesreceiptsArray, true);
            $this->_log->recordLog($msg, 'Post Sales Credit Receipt', "creditmemo");
            $postSalesReceiptResponse = $this->_api->postSalesReceipt($salesreceiptsArray);
            $msg = 'Post Sales Credit Response - '.json_encode($postSalesReceiptResponse, true) ;
            $this->_log->recordLog($msg, 'Post Sales Credit Receipt', "creditmemo");
            $responsedata = '';
            if (array_key_exists('response', $postSalesReceiptResponse)) {
                $responsedata = $postSalesReceiptResponse['response'];
            }
            return $responsedata;
        }
    }

    public function postCustomerPaymentRefund($order, $bpOrder, $sc_order_id, $creditmemo)
    {
        $responsedata = '';
        if ($bpOrder) {
            $paymentsRefundArray = [];
            $bpOrderId = $bpOrder['id'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helpersalesorder = $objectManager->create('Bsitc\Brightpearl\Helper\SalesOrder');
            $payment = $order->getPayment();
            $paymentcode  =  $payment->getMethod();
            $bp_paymentcode = '';
            if ($paymentcode) {
                $bp_paymentcode = $helpersalesorder->getMapPaymentMethod($paymentcode);
            }
            $paymentsRefundArray['paymentMethodCode'] = $bp_paymentcode;
            $paymentsRefundArray['paymentType'] = 'PAYMENT';
            $paymentsRefundArray['orderId'] = $sc_order_id;
            $paymentsRefundArray['currencyIsoCode'] = $bpOrder['currency']['orderCurrencyCode'];
            $paymentsRefundArray['exchangeRate'] = $bpOrder['currency']['exchangeRate'];
            $paymentsRefundArray['amountPaid'] = $creditmemo->getGrandTotal();
            $paymentsRefundArray['paymentDate'] = date("Y-m-d");
            $paymentsRefundArray['journalRef'] = "#Refund-".$bpOrderId;
            $msg = 'Sales Credit Customer Payment Refund dharmender #'.$bpOrderId.' Array  : '.json_encode($paymentsRefundArray, true);
            $this->_log->recordLog($msg, 'Sales Credit Customer Payment Refund ', "paymentrefund");
            /*Post Data to Bightpearl for Refunds*/
            $postSalesReceiptResponse = $this->_api->postCustomerPayment($paymentsRefundArray);
            $postSalesReceiptResponse = json_decode($postSalesReceiptResponse, true);
            $msg = 'Payment refund  #'.$bpOrderId.' Array  : '.json_encode($postSalesReceiptResponse, true);
            $this->_log->recordLog($msg, 'Payment Refund', "paymentrefund");
            if (array_key_exists('response', $postSalesReceiptResponse)) {
                $responsedata = $postSalesReceiptResponse['response'];
            }
        }
        return $responsedata;
    }

    public function postCustomerStoreCreditRefund($order, $bpOrder, $sc_order_id, $creditmemo, $customer_bal_total_refunded = "")
    {
        $responsedata = '';
        if ($bpOrder) {
            $paymentsRefundArray = [];
            $bpOrderId = $bpOrder['id'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helpersalesorder = $objectManager->create('Bsitc\Brightpearl\Helper\SalesOrder');
            $postAmount        = number_format($creditmemo->getCustomerBalanceAmount(), '2', '.', '');
            if ($customer_bal_total_refunded == 'yes') {
                $postAmount = number_format($creditmemo->getCustomerBalTotalRefunded(), '2', '.', '');
            }
            $paymentcode =  'store_credit';
            $bp_paymentcode = '';
            if ($paymentcode) {
                $bp_paymentcode = $helpersalesorder->getMapPaymentMethod($paymentcode);
            }
            $paymentsRefundArray['paymentMethodCode'] = $bp_paymentcode;
            $paymentsRefundArray['paymentType'] = 'PAYMENT';
            $paymentsRefundArray['orderId'] = $sc_order_id;
            $paymentsRefundArray['currencyIsoCode'] = $bpOrder['currency']['orderCurrencyCode'];
            $paymentsRefundArray['exchangeRate'] = $bpOrder['currency']['exchangeRate'];
            $paymentsRefundArray['amountPaid'] = $postAmount;
            $paymentsRefundArray['paymentDate'] = date("Y-m-d");
            $paymentsRefundArray['journalRef'] = "#Refund-".$bpOrderId;
            
             $msg = 'Sales Credit Customer Payment Refund dharmender #'.$bpOrderId.' Array  : '.json_encode($paymentsRefundArray, true);
            $this->_log->recordLog($msg, 'Sales Credit Customer Payment Refund ', "paymentrefund");
 
            /*Post Data to Bightpearl for Refunds*/
            $postSalesReceiptResponse     = $this->_api->postCustomerPayment($paymentsRefundArray);
            $postSalesReceiptResponse     = json_decode($postSalesReceiptResponse, true);
             
            $msg = 'Sales Credit Payment refund  #'.$bpOrderId.' Array  : '.json_encode($postSalesReceiptResponse, true);
            $this->_log->recordLog($msg, 'Sales Credit Payment Refund', "paymentrefund");
            
            if (array_key_exists('response', $postSalesReceiptResponse)) {
                $responsedata = $postSalesReceiptResponse['response'];
            }
        }
        return $responsedata;
    }
     
    public function getDestinationLocationId($item, $bpWarehouseId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderWarehouseMapFactory = $objectManager->create('Bsitc\Brightpearl\Model\OrderwarehousemapFactory');
        if ($item->getWarehouseId()) {
            $res =  $orderWarehouseMapFactory->findRecord('mgt_pos', $item->getWarehouseId());
            if ($res) {
                $bpWarehouseId = $res->getBpWarehouse();
            }
        }
        return $bpWarehouseId;
    }
    
    public function getWarehouseDefaultLocation($warehouseId)
    {
        $destinationLocationId = 2 ;
        $result     = $this->_api->getWarehouseDefaultLocation($warehouseId);
        if (array_key_exists("response", $result)) {
            $destinationLocationId =  (int) $result['response'];
        }
        return $destinationLocationId;
    }
    
    public function addCreditMemoInQueue($creditmemo)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/enable', $storeScope);
        $sc_status_id = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/sc_status_id', $storeScope);
        $sc_return_wh = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/sc_return_wh', $storeScope);
        if ($enable) {
            $order = $creditmemo->getOrder();
            $row = [];
            $row['cm_id']                 = $creditmemo->getId();
            $row['cm_increment_id']     = $creditmemo->getIncrementId();
            $row['order_id']             = $order->getId();
            $row['order_increment_id']     = $order->getIncrementId();
             $jsonData = [];
            foreach ($creditmemo->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                } else {
                    if ($item->hasBackToStock()) {
                        if ($item->getBackToStock() && $item->getQty()) {
                            $jsonData[$item->getSku()] =  $item->getQty();
                        }
                    }
                }
            }
            $row['status'] = 1;
            $row['json'] =  json_encode($jsonData, true);
              $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $MgtcreditmemoqueueFactory = $objectManager->create('Bsitc\Brightpearl\Model\MgtcreditmemoqueueFactory');
            $MgtcreditmemoqueueFactory->addRecord($row);
        } else {
            $msg = 'Functionality disable in configuration';
            $this->_log->recordLog($msg, 'Post Sales Credit Data', "creditmemo");
        }
        return true;
    }
    
    public function isPostPaymentToCreditmemo($creditmemo)
    {
        $flag = false;
        $skipPayment = [];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sc_skip_payment = $this->_scopeConfig->getValue('bpconfiguration/bp_sc_config/sc_skip_payment', $storeScope);
        if ($sc_skip_payment) {
            $skipPayment = explode(",", $sc_skip_payment);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->create('\Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $order = $creditmemo->getOrder();
            if (!$order->getPosId()) {
                $this->_log->recordLog('POS #'.$order->getPosId(), 'Check skip Payment for Weborder', "creditmemo");
                $cr_increment_id = $creditmemo->getIncrementId();
                $scgrid = $resource->getTableName('sales_creditmemo_grid');
                $sql = $connection->select()->from(['scgrid' => $scgrid])->where('increment_id = ?', $cr_increment_id);
                $response = $connection->query($sql);
                $result = $response->fetch(\PDO::FETCH_ASSOC);
                if ($result && array_key_exists("pos_location_id", $result)) {
                    $pos_location_id     = $result['pos_location_id'];
                    if ($pos_location_id > 0) {
                        $payment_method = $result['payment_method'];
                        if (in_array($payment_method, $skipPayment)) {
                            $flag = true;
                        }
                    }
                }
            }
        }
        return $flag;
    }

    public function getAssignedSource(int $stockId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $searchCriteriaBuilder = $objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder') ;
        $getStockSourceLinks = $objectManager->create('Magento\InventoryApi\Api\GetStockSourceLinksInterface') ;
         $searchCriteria = $searchCriteriaBuilder->addFilter('stock_id', $stockId)->create();
        $result = [];
        foreach ($getStockSourceLinks->execute($searchCriteria)->getItems() as $link) {
            $result[$link->getSourceCode()] = $link;
        }
        return $result ;
    }
    
    public function getStockSourceForShipment($storeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface') ;
        $websiteId = (int)$storeManager->getStore($storeId)->getWebsiteId();
        $websiteCode = $storeManager->getWebsite($websiteId)->getCode();
        $source = 'default';
        $stockId = $this->getAssignedStockIdForWebsite($websiteCode);
        if ($stockId) {
            $source_code = $this->getInventorySourceFromStockId($stockId);
            if ($source_code) {
                $source = $source_code;
            }
        }
        return $source;
    }
 
    public function getInventorySourceFromStockId($stockId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->create('\Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('inventory_source_stock_link');
        $select = $connection->select()->from($tableName, ['source_code'])->where('stock_id = ?', $stockId);
        $result = $connection->fetchCol($select);
        if (count($result) === 0) {
            return null;
        }
        return reset($result);
    }
    
    public function getAssignedStockIdForWebsite($websiteCode)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->create('\Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('inventory_stock_sales_channel');
        $type = 'website';
        $select = $connection->select()->from($tableName, ['stock_id'])->where('code = ?', $websiteCode) ->where('type = ?', $type);
        $result = $connection->fetchCol($select);
        if (count($result) === 0) {
            return null;
        }
        return (int)reset($result);
    }
}
