<?php

namespace Bsitc\Brightpearl\Observer;

class OrderInvoicedPaid implements \Magento\Framework\Event\ObserverInterface
{
    protected $_objectManager;
    protected $_log;
    protected $_helperdata;
    protected $_ordercancel;
    protected $_salesReport;
    protected $_salesOrderhelper;
    protected $_bpLayawayPayment;
    protected $_api;

    protected $_logManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bsitc\Brightpearl\Model\LogsFactory $logsFactory,
        \Bsitc\Brightpearl\Model\BpordercancelFactory $orderCancel,
        \Bsitc\Brightpearl\Model\Salesorderreport $salesReport,
        \Bsitc\Brightpearl\Helper\SalesOrder $salesOrderhelper,
        \Bsitc\Brightpearl\Model\Bplayawaypayment $bpLayawayPayment,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\LogsFactory $logManager,
        \Bsitc\Brightpearl\Helper\Data $helperdata
    ) {
        $this->_objectManager     = $objectManager;
        $this->_log              = $logsFactory;
        $this->_helperdata          = $helperdata;
        $this->_ordercancel      = $orderCancel;
        $this->_salesReport      = $salesReport;
        $this->_salesOrderhelper = $salesOrderhelper;
        $this->_bpLayawayPayment = $bpLayawayPayment;
        $this->_api              = $api;
        $this->_logManager       = $logManager;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice    = $observer->getEvent()->getInvoice();
        if ($invoice->getGrandTotal() > 0) {
            $order             = $invoice->getOrder();
            $incrementid     = $order->getData('increment_id');
            $data             = $this->_salesReport->findRecord('mgt_order_id', $incrementid);
            if ($data) {
                $bporderid = $data[0]['bp_order_id'];
                $row = ['mgt_order_id' => $incrementid, 'so_order_id'=> $bporderid];
                $crReportId = $this->_bpLayawayPayment->addRecord($row);
                $rowid      = $this->_bpLayawayPayment->findRecord('mgt_order_id', $incrementid);
                $reportid   = $rowid->getId();
                
                $orderPaymentStatus = '';
                 /* -------- if order already paid on brightpearl  skip it   ---------------*/
                $getBpOrder        = $this->_api->orderById($bporderid);
                if (array_key_exists("response", $getBpOrder)) {
                    if (array_key_exists("0", $getBpOrder['response'])) {
                        $bpOrder             = $getBpOrder['response'][0];
                        $orderPaymentStatus    = $bpOrder['orderPaymentStatus'] ;
                    }
                }
                /* -------- if order already paid on brightpearl  skip it   ---------------*/

                if ($orderPaymentStatus == 'PAID') {
                    $row = ['bp_payment_id'=> '','status'=> 2];
                    $this->_bpLayawayPayment->updateRecord($reportid, $row);
                } else {
                    $Bapp     = $this->_api;
                    $result   = $this->_salesOrderhelper->postCustomerPayment($Bapp, $order, $bporderid, '');
                    $response = json_decode($result);
                    $responseArray = json_decode($result ,true);
					if (array_key_exists("response", $responseArray)) 
					{
						if ($response->response) {
							$orderpaymentid = $response->response;
							$row = ['bp_payment_id'=> $orderpaymentid,'status'=> 1 ];
							$this->_bpLayawayPayment->updateRecord($reportid, $row);
							$msg = 'After Shipment and Payment Paid'.$orderpaymentid;
							$this->_logManager->recordLog($msg, "Create Shipment", "Shipment");
						} else {
							$row = ['bp_payment_id'=> '','status'=> 0];
							$this->_bpLayawayPayment->updateRecord($reportid, $row);
						}
					}else{
							$row = ['bp_payment_id'=> '','status'=> 0];
							$this->_bpLayawayPayment->updateRecord($reportid, $row);
 					}
                }
            }
        }
    }
}
