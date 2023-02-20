<?php

namespace Bsitc\Brightpearl\Helper;

class SalesOrder extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_directoryList;
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_storeManager;
    protected $_order;
    protected $attributeRepository;
    protected $attributeValues;
    protected $tableFactory;
    protected $_pricelist;
    protected $associatedproduct;
    protected $moduleManager;
    protected $_webhookinventory;
    protected $_api;
    protected $_logManager;
    protected $_webhookupdate;
    protected $productResourceModel;
    protected $productFactory;
    protected $_customerRepositoryInterface;
    protected $_countryFactory;
    protected $_salesorderreportFactory;
    protected $_orderqueue;
    protected $_bpshippingmapFactory;
    protected $_bpleadsource;
    protected $_bppaymentmapFactory;
    protected $_bptaxmapFactory;
    protected $_orderwarehousemap;
    protected $_bppricelistmap;
    protected $_isProductTaxable;
    protected $_extraFeeTaxPercent;
    protected $_skureplacementFactory;
    protected $_freeproductFactory;
    protected $_bpReservationProductArray;
    public $_swedishArray;
    public $_svPercent;
    public $_svTaxclass;
    public $_swedish;
    public $_swedishVatInfo;
    public $_splitSkuFactory;	
 
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory,
        \Magento\Sales\Model\Order $order,
        \Bsitc\Brightpearl\Model\PricelistFactory $pricelist,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Bsitc\Brightpearl\Model\AssociateproductFactory $associatedproduct,
        \Bsitc\Brightpearl\Model\OrderqueueFactory $orderqueue,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\Logs $logManager,
        \Bsitc\Brightpearl\Model\SalesorderreportFactory $salesorderreportFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Bsitc\Brightpearl\Model\BpshippingmapFactory $BpshippingmapFactory,
        \Bsitc\Brightpearl\Model\BpleadsourcemapFactory $bpleadsource,
        \Bsitc\Brightpearl\Model\BppaymentmapFactory $BppaymentmapFactory,
        \Bsitc\Brightpearl\Model\BptaxmapFactory $BptaxmapFactory,
        \Bsitc\Brightpearl\Model\OrderwarehousemapFactory $orderwarehousemap,
        \Bsitc\Brightpearl\Model\Bppricelistmap $bppricelistmap,
        \Bsitc\Brightpearl\Model\SkureplacementFactory $skureplacementFactory,
        \Bsitc\Brightpearl\Model\FreeproductFactory $freeproductFactory,
        \Bsitc\Brightpearl\Model\SwedishFactory $swedish,
		\Bsitc\Brightpearl\Model\SplitskuFactory $splitSkuFactory
    ) {
        
        $this->_directoryList = $directoryList;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->attributeRepository  = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->_pricelist = $pricelist;
        $this->_order = $order;
        $this->productFactory = $productFactory;
        $this->associatedproduct = $associatedproduct;
        $this->moduleManager = $moduleManager;
        $this->_logManager = $logManager;
        $this->_api = $api;
        $this->productResourceModel = $productResourceModel;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_countryFactory = $countryFactory;
        $this->_salesorderreportFactory = $salesorderreportFactory;
        $this->_orderqueue = $orderqueue;
        $this->_bpshippingmapFactory = $BpshippingmapFactory;
        $this->_bpleadsource = $bpleadsource;
        $this->_bppaymentmapFactory = $BppaymentmapFactory;
        $this->_bptaxmapFactory = $BptaxmapFactory;
        $this->_orderwarehousemap = $orderwarehousemap;
        $this->_bppricelistmap = $bppricelistmap;
        $this->_skureplacementFactory = $skureplacementFactory;
        $this->_freeproductFactory = $freeproductFactory;
        $this->_swedish = $swedish;
		$this->_splitSkuFactory = $splitSkuFactory;
		
        parent::__construct($context);
    }

    /**
     * Get store config
     */
    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
    
    public function getBrightpearl($store)
    {
        $apiObj = '';
        $bpConfigData  =  (array) $this->getConfig('bpconfiguration/api', $store);
        if (isset($bpConfigData['enable']) && isset($bpConfigData['bp_useremail']) && isset($bpConfigData['bp_password']) && isset($bpConfigData['bp_account_id']) && isset($bpConfigData['bp_dc_code']) && isset($bpConfigData['bp_api_version'])) {
            $apiObj = $this->_objectManager->create('\Bsitc\Brightpearl\Model\Api', ['data' => $bpConfigData]);
        }
        return $apiObj;
    }
    
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Bpproducts', $arguments, false);
    }

    public function createpricelist(array $arguments = [])
    {
        return $this->_objectManager->create('Bsitc\Brightpearl\Model\Pricelist', $arguments, false);
    }

    /*Get configuration for Pricelist*/
    public function getPricelist()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/order_price_list');
    }
    
    /*Get configuration for Channel*/
    public function getChannel()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/mgt_order_channel');
    }

    /*Get configuration for POS Channel*/
    public function getPosChannel()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/pos_order_channel');
    }
	
    /*Get configuration for POS Channel*/
    public function getTradeChannel()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/trade_order_channel');
    }

    /*Get configuration for Nominal Code*/
    public function getNominalCode()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/order_product_nominal');
    }
    
    /*Get configuration for Shipping Nominal Code*/
    public function getShippingNominalCode()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/order_shipping_nominal');
    }
    
    /*Get configuration for Discount Nominal Code*/
    public function getDiscountNominalCode()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/order_discount_nominal');
    }
    
    /*Get configuration for store_credit_nominal Nominal Code*/
    public function getStoreCreditNominalCode()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/store_credit_nominal');
    }

    /*Get configuration for rounding_sku_nominal Nominal Code*/
    public function getRoundingSkuNominalCode()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/rounding_sku_nominal');
    }
    
    public function getRoundingThreshold()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/rounding_threshold');
    }
    
    public function getRoundingEnable()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/rounding_enable');
    }
    
    /*Get configuration for Adjustment Refunds Nominal Code*/
    public function getAdjustmentRefundsNominalCode()
    {
        return $pricelist  =  $this->getConfig('bpconfiguration/bp_sc_config/adjustment_refunds_nominal');
    }

    /*Get configuration for Order Status*/
    public function getMgtOrderStatus()
    {
        return $orderstatus  =  $this->getConfig('bpconfiguration/bp_orderconfig/mgt_order_status');
    }
    
    /*Get configuration for Trade Order Status*/
    public function getTradeOrderStatus()
    {
        return $orderstatus  =  $this->getConfig('bpconfiguration/bp_orderconfig/trade_order_status');
    }

    /*Get configuration for Bespoke Status*/
    public function getBespokOrderStatus()
    {
        return $orderstatus  =  $this->getConfig('bpconfiguration/bp_orderconfig/bespok_order_status');
    }

    /*Get configuration for POS Order Status*/
    public function getPosOrderStatus()
    {
        return $posstatus  =  $this->getConfig('bpconfiguration/bp_orderconfig/pos_order_status');
    }

    /*Get configuration for POS Order Status*/
    public function getPreOrderStatus()
    {
        $status  =  $this->getConfig('bpconfiguration/bp_orderconfig/pre_order_status');
        return $status;
    }

    /*Get configuration for MAde To Order Status*/
    public function getMtoStatus()
    {
        $status  =  $this->getConfig('bpconfiguration/bp_orderconfig/mto_order_status');
        return $status;
    }

    /*Get configuration for Print To Order Status*/
    public function getPtoStatus()
    {
        $status  =  $this->getConfig('bpconfiguration/bp_orderconfig/pto_order_status');
        return $status;
    }

    /*Get configuration for POS Order Status*/
    public function getConfigPosStaffName()
    {
        $posstaff  =  $this->getConfig('bpconfiguration/bp_orderconfig/posstaff');
        return $posstaff;
    }


    /*Get configuration for Channel*/
    public function getBpleadsource($customergroupid)
    {
        $enable  =  $this->getConfig('bpconfiguration/bp_orderconfig/enable');
        $customergroupid = trim($customergroupid);
        $mpleadsource = '';
        if ($enable) {
            $shippingcollections = $this->_bpleadsource->create()->getCollection();
            $shippingcollections = $shippingcollections->addFieldToFilter('code', $customergroupid);
            $shippingcollections = $shippingcollections->getData();
            if (count($shippingcollections)) {
                foreach ($shippingcollections as $shippingcollection) {
                    $mpleadsource = $shippingcollection['bpcode'];
                    break;
                }
            } else {
                $mpleadsource  =  $this->getConfig('bpconfiguration/bp_orderconfig/bpleadsource');
            }
        }
        return $mpleadsource;
    }

    
    public function getStoreIdFromLocationId($warehouse_id)
    {
        $data = '';
        /*
        $collection = $this->_objectManager->create('\Magestore\InventorySuccess\Model\WarehouseStoreViewMap')->getCollection();
        $collection->addFieldToFilter('warehouse_id', $warehouse_id);
        if ($collection->getSize()) {
            $data  = $collection->getFirstItem();
        }
        return $data;
        */
    }
    
    
    /*Get Order configuration for Pricelist*/
    public function getMappedPricelist($order)
    {
        $pricelist  =  $this->getConfig('bpconfiguration/bp_orderconfig/order_price_list');
        $storeid = $order->getStoreId();
        $pos_location_id = $order->getPosLocationId();
        if ($pos_location_id) {
             $wareHouseStoreViewMap = $this->getStoreIdFromLocationId($pos_location_id);
            if ($wareHouseStoreViewMap) {
                $storeid = $wareHouseStoreViewMap->getStoreId();
            }
        }
        $data = $this->_bppricelistmap->findRecord('store_id', $storeid);
        if ($data) {
            $pricelist = $data->getBpPrice();
        }
		
		/* ------ implement custom price list here --------- */
		$custom_pricelist_mapping  =  $this->getConfig('bpconfiguration/bp_orderconfig/custom_pricelist_mapping');
		$orderCustomerGroupId = $order->getCustomerGroupId();
		if($custom_pricelist_mapping and $orderCustomerGroupId !="" )
		{
			$cplArray = [];
			$step1 = explode("#",$custom_pricelist_mapping);
			foreach($step1  as $step2)
			{
				$step3 =  explode(":",$step2);
				$storeId = $step3[0];
				$customerGroupId = $step3[1];
				$pricelistId = $step3[2];
				$cplArray[$storeId][$customerGroupId] = $pricelistId;
 			}
			
			if( count($cplArray)>0)
			{
				if (array_key_exists($order->getStoreId(), $cplArray)) 
				{
					$step4 = $cplArray[$order->getStoreId()];
					if (array_key_exists($orderCustomerGroupId, $step4))
					{
						$pricelist =  $step4[$orderCustomerGroupId];
					}					 
				}
				
			}
		}
		/* ------ implement custom price list here --------- */

        return $pricelist ;
    }
    
    /*Get configuration for Warehouse*/
    /*First check first piority from order then from mapping then from selected store or pos*/
    public function getBpWarehouse($order)
    {
        /*If Order Placed from Magento frontend*/
        $warehouse = $order->getWarehouseStore();
        $storeid = $order->getStoreId();
        $pos = $order->getPosLocationId();
        if (!$warehouse) {
            $warehousecollections = $this->_orderwarehousemap->create()->getCollection();
            if ($pos) {
                $warehousecollections = $warehousecollections->addFieldToFilter('mgt_pos', $pos);
            } else {
                $warehousecollections = $warehousecollections->addFieldToFilter('mgt_store', $storeid);
            }
            $warehousecollections = $warehousecollections->getData();
            if (count($warehousecollections)) {
                foreach ($warehousecollections as $warehousecollection) {
                    $warehouse = $warehousecollection['bp_warehouse'];
                    break;
                }
            } else {
                $warehouse  =  $this->getConfig('bpconfiguration/bp_orderconfig/mgt_order_warehouse');
            }
        }
        return $warehouse;
    }
    
    /*Get configuration for Shipping Methods*/
    public function getShippingMapping($order)
    {
		$shippingmethod = trim($order->getShippingMethod());
        $mpsshippingmethods  =  $this->getConfig('bpconfiguration/bp_orderconfig/bpshippingmethod');
		$find  = $this->findShippingMapping($order);
		if ($find) 
		{
			$mpsshippingmethods = $find->getBpcode();
		}			 
        return $mpsshippingmethods;
    }
	
	public function findShippingMapping($order)
	{
		$data = '';
		$shippingmethod = trim($order->getShippingMethod());
		if ( strstr( $shippingmethod, 'matrixrate' ) ) 
		{
			$shippingdescription = trim($order->getShippingDescription());
			$smArray = explode("-",$shippingdescription);
			$shippingmethod = trim($smArray[1]);
			$shippingmethod = preg_replace('/\s+/', '_', $shippingmethod);
			$shippingmethod = 'matrixrate_'.strtolower($shippingmethod);
		}
		
		$country = $order->getShippingAddress()->getCountryId();
		$store_id = $order->getStoreId();
		$collection = $this->_bpshippingmapFactory->create()->getCollection();
		$collection->addFieldToFilter('code', $shippingmethod);
		$collection->addFieldToFilter('store_id', $store_id);
		$collection->addFieldToFilter('country', $country);
		if ($collection->getSize()) {
			$data  = $collection->getFirstItem();
		}
		return $data;
	}
	
    public function getShippingMappingDeliveryDays($order)
    {
		$delDays = 1 ;
		$find  = $this->findShippingMapping($order);
		if ($find) 
		{
			$delDays = $find->getDeliveryday();
		}			 
        return $delDays;
    }	
    
    /*Get configuration for Shipping Methods*/
    public function getMapPaymentMethod($paymentcode)
    {
        $enable  =  $this->getConfig('bpconfiguration/bp_orderconfig/enable');
        $paymentcode = trim($paymentcode);
        $mpnominalcode = '';
        if ($enable) {
            $paymentcollections = $this->_bppaymentmapFactory->create()->getCollection();
            $paymentcollections = $paymentcollections->addFieldToFilter('code', $paymentcode);
            $paymentcollections = $paymentcollections->getData();

            if (count($paymentcollections)) {
                foreach ($paymentcollections as $paymentcollection) {
                    $mpnominalcode = $paymentcollection['bpcode'];
                    break;
                }
            } else {
                    $mpnominalcode  =  $this->getConfig('bpconfiguration/bp_orderconfig/order_product_nominal');
            }
        }
        return $mpnominalcode;
    }

    /*Get configuration for Shipping Methods*/
    public function getTaxable($taxclassid)
    {
        $enable  =  $this->getConfig('bpconfiguration/bp_orderconfig/enable');
        $taxablecode = '';
        $taxclassid = $taxclassid;
        if ($enable) {
            $taxcollections = $this->_bptaxmapFactory->create()->getCollection();
            $taxcollections = $taxcollections->addFieldToFilter('code', $taxclassid);
            $taxcollections = $taxcollections->getData();
            if (count($taxcollections)) {
                foreach ($taxcollections as $taxcollection) {
                    $taxablecode = $taxcollection['bpcode'];
                    break;
                }
            } else {
                    $taxablecode  =  $this->getConfig('bpconfiguration/bp_orderconfig/taxcode');
            }
        }
        return $taxablecode;
    }

    public function getNonTaxable()
    {
        $enable  =  $this->getConfig('bpconfiguration/bp_orderconfig/enable');
        $taxablecode = '';
        if ($enable) {
                    $taxablecode  =  $this->getConfig('bpconfiguration/bp_orderconfig/notaxcode');
        }
        return $taxablecode;
    }
    
    /*Insert Data in log tables*/
    public function recordLog($cat, $log_data, $title)
    {
        $logArray = [];
        if (!$cat) {
            $cat = "Global";
        }
         $logArray['category'] = $cat;
         $logArray['title'] =  $title;
         $logArray['store_id'] =  0;
         $logArray['error'] =  json_encode($log_data, true);
         $this->_logManager->addLog($logArray);
         return true;
    }
     
     
     /*Insert Data in Sales Order Report tables*/
    public function setFirstSalesReportData($coloumn, $value)
    {
        $updatesaleslogs = $this->_salesorderreportFactory->create();
        $updatesaleslog = $updatesaleslogs->getCollection();
        $updatesaleslog = $updatesaleslog->addFieldToFilter($coloumn, $value);
        $updatesaleslog = $updatesaleslog->getData();
        $updateid = '';
        if (empty($updatesaleslog)) {
            $updatemgtid = $updatesaleslogs->setData($coloumn, $value);
            $savedata = $updatemgtid->save();
            $updateid = $updatemgtid->getId();
        } else {
            foreach ($updatesaleslog as $updatesales) {
                $updateid = $updatesales['id'];
                break;
            }
        }
        return $updateid;
    }
    
     /*Update Sales Order Report tables*/
    public function updateSalesReportData($id, $coloumn, $value)
    {
           $updatesaleslog = $this->_salesorderreportFactory->create();
           $updateid = '';
           $updatesaleslog = $updatesaleslog->load($id);
           $updatesaleslog = $updatesaleslog->setData($coloumn, $value);
           $updatesaleslog = $updatesaleslog->save();
           $updateid = $updatesaleslog->getId();
           return $updateid;
    }

     /*Check and Update Sales Order Report tables*/
    public function ChecklogData($id, $coloumn)
    {
        $updatesaleslogs = $this->_salesorderreportFactory->create();
        $updatesaleslog = $updatesaleslogs->getCollection();
        $updatesaleslog = $updatesaleslog->addFieldToFilter('id', $id);
        $updatesaleslog = $updatesaleslog->getData();
        $value = '';
        foreach ($updatesaleslog as $updatesales) {
            $custid = $updatesales['bp_customer_id'];
            if ($custid) {
                $value = 'true';
            } else {
                $value = 'false';
            }
        }
        return $value;
    }

    
    /*Send Order from MGT to BP*/
    public function CreateBpSalesOrder()
    {
        /*Fetch Pending status from Order Queue table*/
        $collections = $this->_orderqueue->create()->getCollection();
        $collections = $collections->addFieldToFilter('state', 'pending');
        foreach ($collections as $collection) {
            $orderId = $collection->getOrderId();
            $incrementId = $collection->getIncrementId();
            if ($orderId) {
                $id = $collection->getId();
                $queueorder = $collection->load($id);
                $data = $queueorder->setState("processing");
                $queueorder->save();
                /*Call a BP customer functions and set shipping and billing address initially*/
                $response = $this->CreateOrder($orderId, $incrementId);
                if ($response) {
                    $data = $queueorder->setState("complete");
                    $queueorder->save();
                } else {
                    $data = $queueorder->setState("error");
                    $queueorder->save();
                }
            }
        }
    }

    /*Create Customer and Address in BP*/
    public function CreateOrder($orderId, $incrementId)
    {
		/* ---- check if new method option enable for posting the order */
		$use_new_order_api  =  $this->getConfig('bpconfiguration/bp_orderconfig/use_new_order_api');
		if($use_new_order_api){
			$successresp = '';
			$successresp = $this->CreateOrderNew($orderId, $incrementId);
			return $successresp;	
		}
		/* ---- check if new method option enable for posting the order */

        $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
        $billingAdress = $order->getBillingAddress()->getData();
        // $shippingAdress = $order->getShippingAddress()->getData();
		if($order->getShippingAddress()) {
			$shippingAdress = $order->getShippingAddress()->getData();
		}else{
			$shippingAdress = $order->getBillingAddress()->getData();
			$order->setShippingAddress($order->getBillingAddress());
        }
		
        /*Create log for reports*/
        $salesreportid = '';
        if ($orderId) {
            $salesreportid = $this->setFirstSalesReportData('mgt_order_id', $incrementId);
        } else {
            $log_data = json_encode("Order Id does not exits.");
            $this->recordLog("Order", $log_data, "Order not exits");
        }
        /*If Not Shipping Address set billing as shipping address*/
        if (!$shippingAdress) {
            $shippingAdress = $order->getBillingAddress()->getData();
            $log_data = json_encode("There is no shipping address with this order id = ".$incrementId);
            $this->recordLog("Order", $log_data, $incrementId);
        }
        /*Guest Customer*/
        if (!$order->getCustomerId()) {
			$billingAdress['website_id'] = $order->getStoreId();
            $customerInfo = $this->getBpCustomerInfoArray($billingAdress, $telephone = "");
            $email = $billingAdress['email'];
            /*For Guest Customer Logs*/
            if ($salesreportid) {
                $this->updateSalesReportData($salesreportid, 'mgt_customer_id', $value = 0);
            }
        } else {
            /*Register Customer*/
            $customerId = $order->getCustomerId();
            $customerFactory = $this->_objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
            $customer = $customerFactory->load($customerId);
            $customer = $customer->getData();
            $telephone = '';
            if (array_key_exists('telephone', $billingAdress)) {
                $telephone = $billingAdress['telephone'];
            }
            $customerInfo = $this->getBpCustomerInfoArray($customer, $telephone);
            $customerInfo['financialDetails']['priceListId'] = $this->getMappedPricelist($order);
            $email = $customer['email'];
            /*For Register Customer Logs*/
            if ($salesreportid) {
                $this->updateSalesReportData($salesreportid, 'mgt_customer_id', $customerId);
            }
        }
        $Bil_Address = $this->getAddressArray($billingAdress, $order->getBillingAddress());
        $Ship_Address = $this->getAddressArray($shippingAdress, $order->getShippingAddress());
        if ($this->_api->authorisationToken) {
            /*Search Customer are alreday exits*/
            $res = $this->_api->searchCustomerByEmail($email, $return_type = "object") ;
            $result = [];
            $resArray =  json_decode(json_encode($res), true);
            if (array_key_exists('response', $resArray)) {
                $responseArray = $resArray['response'];
                if (array_key_exists('results', $responseArray)) {
                    $result = $res->response->results;
                }
            }
            //$log_data = "Check Customer Exits".json_encode($res);
            //$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
            usleep(1000000);
            $flagCustomerExist = false;
            $brightpearlUserId = '';
            if (count($result)) {
                foreach ($result as $_result) {
                    $response = $this->_api->getCustomerById($_result[0]);
                    $res = $response['response'];
                    /*Check error for customer exits at BP*/
                    if (isset($res['errors'])) {
                        $log_data = "get customer from bp".json_encode($res['errors']);
                        $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                    } else {
                        $log_data = "get customer from bp".json_encode($res);
                        $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                    }
                    if (strpos(json_encode($res), 'isSupplier') > 0 && strpos(json_encode($res), 'isStaff') > 0) {
                        if ($res[0]['relationshipToAccount']['isStaff'] != 1 && $res[0]['relationshipToAccount']['isSupplier'] != 1) {
                            $flagCustomerExist = true;
                            $brightpearlUserId = $res[0]['contactId'];
                            /*Log tables*/
                            $log_data = "BP customer already exits with id".json_encode($brightpearlUserId);
                            $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                            //echo 'already exits!';
                            break;
                        }
                    }
                }
            }
            /*Customer does not exits*/
            if ($flagCustomerExist == false) {
                /*POST Customer Address First*/
                /*POST Customer address in Log tables*/
                //$log_data = "Customer address request".json_encode($Bil_Address);
                //$this->recordLog($cat = "Order", $log_data, $title=$incrementId);
                $billId = '';
                $response     = $this->_api->postCustomerAddress($Bil_Address);
                if (array_key_exists('response', $response)) {
                    $billId = $response['response'];
                }
                /* ---------- add retry code if not received response --------*/
                if (!$billId && $billId == "") {
                    $response     = $this->_api->postCustomerAddress($Bil_Address);
                    if (array_key_exists('response', $response)) {
                        $billId = $response['response'];
                    }
                }
                /* ---------- add retry code if not received response --------*/
                /*Error log for customer Address*/
                $log_data = "Customer address response ".json_encode($response);
                $this->recordLog("Order", $log_data, $incrementId);
                /*Check if billing and shipping address are same*/
                $delId = '';
                if (array_diff($Bil_Address, $Ship_Address)) {
                    $response = $this->_api->postCustomerAddress($Ship_Address);
                    if (array_key_exists('response', $response)) {
                        $delId = $response['response'];
                    }
                    /* ---------- add retry code if not received response --------*/
                    if (!$delId && $delId == "") {
                        $response = $this->_api->postCustomerAddress($Ship_Address);
                        if (array_key_exists('response', $response)) {
                            $delId = $response['response'];
                        }
                    }
                    /* ---------- add retry code if not received response --------*/
                } else {
                    $delId = $billId;
                }
                /*Log for Customer bill id*/
                if (!$billId && $billId == "") {
                    $log_data = ' Add Billing API Call Fail ';
                    $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                } else {
                    $log_data = 'BP Billing address id '.$billId;
                    $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                }
                            /* Update address id in customer info array */
                            $customerInfo['postAddressIds'] = [
                                'DEF' => $billId,
                                'BIL' => $billId,
                                'DEL' => $delId
                            ];
                                

                            /*POST Customer to brightpearl*/
                            $log_data = "Post customer to BP request ".json_encode($customerInfo);
                            $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                                                                
                            /*Post Customer to Bright Pearls*/
                            $CreateUserResponse = $this->_api->postCustomer($customerInfo);
                            if (array_key_exists('response', $CreateUserResponse)) {
                                $brightpearlUserId = $CreateUserResponse['response'];

                                    $log_data = "Post customer to BP response ".json_encode($CreateUserResponse);
                                    $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                            } else {
                            /*Log get Customer BP ID*/
                                $log_data = "Customer BP id ".json_encode($CreateUserResponse['errors']);
                                $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                            }
            }
                        
                        /*Set Customer for BP IN logs*/
            if ($salesreportid) {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_customer_id', $brightpearlUserId);
            }

                        /*Log table*/
                            $bpcustlog = $this->ChecklogData($salesreportid, 'bp_customer_id');
            if ($bpcustlog == 'true') {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_customer_status', $value = "success");
            } else {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_customer_status', $value = "error");
            }

                        
            /*Send data to create Order*/
            $bporderId = $this->getBpOrderData($order, $brightpearlUserId, $email, $shippingAdress);
                        
            if ($bporderId) {
                $log_data = "BP Order id ".json_encode($bporderId);
                $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                // ------------- Update Brightpearl order id in Order PO Relation table ------------
                    $oprf = $this->_objectManager->create('\Bsitc\Brightpearl\Model\BporderporelationFactory');
                    $condition = [ 'order_id'=>$order->getId() ];
                    $oprf->updateOrderPoRelationColumn('bp_order_id', $bporderId, $condition);
                    $oprf->updateOrderPoRelationColumn('state', '1', $condition);
                // ------------- Update Brightpearl order id in Order PO Relation table ------------
            } else {
                $log_data = "BP Order id does not exits.";
                $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
            }
                        
            /*Set Customer for BP IN logs*/
            if ($salesreportid) {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_order_id', $bporderId);

                /*Update Order Comments in Magento*/
                /*$Obj_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $custom_order = $Obj_objectManager->create('\Magento\Sales\Model\Order')->load($orderId);*/

                $custom_order =  $this->_order->load($orderId);
                $custom_order->addStatusHistoryComment('This Brightpearl Order id - '.$bporderId);
                $custom_order->save();
            }
                        
                        
                            $bporderstatus = $this->ChecklogData($salesreportid, 'bp_order_status');

            if ($bporderstatus == 'true') {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_order_status', $value = "success");
            } else {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_order_status', $value = "error");
            }
                        
                        
                        /*Set Rows in Orders*/
                            $data = $this->getOrderRow($order, $bporderId);

            if ($data) {
                $log_data = "BP Order row data ".json_encode($data);
                $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
            } else {
                $log_data = "BP Order Row data API Response";
                $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
            }
                            usleep(1000000);

                        /*Set Inventory to Order Rows*/
            if ($data) {
                    $salesOrderRowId = $data['salesOrderRowId'];
                    $reservationProductArray = $data['reservationProductArray'];
                    $res = $this->setInventoryToOrderRow($order, $incrementId, $bporderId, $salesOrderRowId, $reservationProductArray);
                if (!isset($res->errors)) {
                        $this->updateSalesReportData($salesreportid, 'bp_inventory_status', $value = "success");
                } else {
                        $this->updateSalesReportData($salesreportid, 'bp_inventory_status', $value = "error");
                }
            }
                                
                        /*Set Discount amount with Coupon code*/
                            $this->CouponCodeDiscount($order, $bporderId);

                        /*Order Shipping charges*/
                        /*+++++++++++++++++++++++++++++++++++++++++++*/
                            $this->OrderShippingCharge($order, $bporderId);
							
                         /* -------------- Oversize Handling Fee --------------- */
                            $this->postOversizeHandlingFee($order, $bporderId);
							
                         /* -------------- Extra Shipping Fee --------------- */
                            $this->postExtraShippingFee($order, $bporderId);
						
                        /* --- Reduce Store Credit / Customer Balance Amount from Order -------- */
                        // $this->CustomerBalanceAmount($order, $bporderId);
 
                        /* ---  add rounding fix row  Order -------- */
                            $this->addRoundingDifferencAmountRow($order, $bporderId);
							
                        /* ---  post free product  if configure -------- */
                            $this->postFreeProducts($order, $bporderId);
 
                        /*POS Staff Name*/
                            $posid = $order->getPosStaffId();
            if ($posid) {
                $this->postPosStaffName($order, $bporderId);
            }


                            usleep(1000000);
                        
                        /*Check If Order are paid and has Invoiced then send payment to Brightpearl*/
                            $bpPaymentPaid  = '';
            if (($order->getBaseTotalDue() == 0) && ($order->getInvoiceCollection()->getSize() > 0)) {
                $Bpapi = $this->_api;
                $response_data = $this->postCustomerPayment($Bpapi, $order, $bporderId, $brightpearlUserId);
                $paymentassign = json_decode($response_data);

                $bpPaymentPaid = $paymentassign->response;
                            
                if ($paymentassign->response) {
                    $log_data = "Order Payment Response ".json_encode($paymentassign->response);
                    $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                    $this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "success");
                } else {
                    $log_data = "Order Payment Response ".json_encode($paymentassign);
                    $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                    $this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "error");
                }
            } else {
                $bpPaymentPaid  = 'notpaid';
                $log_data = "Order Payment Response - payment are not paid yet";
                $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                $this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "not paid");
            }
                        
                        /* --------- post store credit or customer balance as  a payment ---------------*/
            if ($order->getCustomerBalanceAmount() > 0) {
                $Bpapi = $this->_api;
                $response_data = $this->postStoreCreditPayment($Bpapi, $order, $bporderId, $brightpearlUserId);
                $paymentassign = json_decode($response_data);

                $bpPaymentPaid = $paymentassign->response;
                            
                if ($paymentassign->response) {
                    $log_data = "Order store credit Payment Response ".json_encode($paymentassign->response);
                    $this->recordLog("Order", $log_data, $incrementId);
                    $this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "success");
                } else {
                     $log_data = "Order store credit Payment Response ".json_encode($paymentassign);
                    $this->recordLog("Order", $log_data, $incrementId);
                    $this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "error-Store-credit");
                }
            }
                        /* --------- post store credit or customer balance as  a payment ---------------*/
                        
                        /* --------- post  Gift Cards Payment as a payment ---------------*/
            if ($order->getGiftCardsAmount() > 0) {
                 $Bpapi = $this->_api;
                $response_data = $this->postGiftCardsPayment($Bpapi, $order, $bporderId, $brightpearlUserId);
                $paymentassign = json_decode($response_data);

                $bpPaymentPaid = $paymentassign->response;
                            
                if ($paymentassign->response) {
                    $log_data = "Order Gift Cards Payment Response ".json_encode($paymentassign->response);
                    $this->recordLog("Order", $log_data, $incrementId);
                    $this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "success");
                } else {
                     $log_data = "Order Gift Cards Payment Response ".json_encode($paymentassign);
                    $this->recordLog("Order", $log_data, $incrementId);
                    $this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "error-Store-credit");
                }
            }
                        /* --------- post  Gift Cards Payment as a payment  ---------------*/
        } else {
                /*BP API Authentication failed*/
                $log_data = "API Authentication fails";
                $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
        }

		/*Check If Order are POS order then generate Fullfillments*/
		//$goodsoutnote  = $this->PostGoodsOutNote($Bpapi, $order, $bporderId, $brightpearlUserId);
		//$paymentassign = json_decode($response_data);
		$pos                 = $order->getPosLocationId();
		$shippingmethod     = $order->getShippingMethod();
		$shippingMethodId   = $this->getShippingMapping($order);
		$collectfrom_store = $order->getWarehouseStore();

		/*Assign error msg for status*/
		$successresp = $bpPaymentPaid;

        if (($pos) || ($collectfrom_store)) {
            // ---------- skip pos order if order in processing state due to home delivery in pos
            if (($pos) && ($order->getState() == 'processing')) {
                return $successresp;
            }
                    
            if ($data and count($data['reservationProductArray']) >0) {
                $products = $data['reservationProductArray'];
                  $prodata = '[ '.implode(",", $products).' ]';
                 $warehouseid = $this->getBpWarehouse($order);
                $goodsdata = '{"warehouses": [{"releaseDate": '.date("Y-m-d").',"warehouseId": '.$warehouseid.',"transfer": false,"products": '.$prodata.'}],"priority": false,"shippingMethodId": '.$shippingMethodId.',"labelUri": ""}';

                $this->recordLog("Order", 'Order Goods Out note POST Data'.$goodsdata, $incrementId);
                            
                 $goodsoutnote  = $this->_api->PostGoodsOutNote($bporderId, $goodsdata);
                             
                $log_data = "Order Goods Out note Response ".$goodsoutnote;
                $this->recordLog("Order", $log_data, $incrementId);
                $successresp = json_decode($goodsoutnote, true);
                if (array_key_exists("response", $successresp)) {
                            $response = $successresp['response'];
                             $this->postShipmentEvent($response, $incrementId);
                }
            }
        } else {
            $successresp = $bpPaymentPaid;
        }
         return $successresp;
    }

     /*
    * Post POS Staff Name
    */
    public function postPosStaffName($order, $bporderId)
    {
		$posstaffname = $order->getPosStaffName();
        if ($posstaffname) {
			$path = $this->getConfigPosStaffName();
			$data = [['op'=> 'add', 'path' => '/'.$path, 'value' => $posstaffname]];
			$response = $this->_api->postOrderCustomAttribute($bporderId, $data);
			$log_data = "Post POS Staff Name ".json_encode($data);
			$this->recordLog($cat = "POS Staff Logs", $log_data, '');
        }
    }

     /*
    * Post Shipment Event
    */
    public function postShipmentEvent($response, $incrementId)
    {
        if (count($response) > 0) {
            foreach ($response as $goodnoteID) {
                $result        = $this->_api->getGoodsOutNote($goodnoteID) ; // get good out note
                if (array_key_exists("response", $result)) {
                    $gonData         = $result['response'][$goodnoteID];
                    $eventOwnerId     = $gonData['createdBy'];
                    $tmp = [];
                    $tmp['eventCode']         = 'SHW';
                    $tmp['occured']         = date("c", time());
                    $tmp['eventOwnerId']     = $eventOwnerId;
                    $eventData = [];
                    $eventData['events'][] = $tmp;
                    $log_data = "Post Shipment Event Data ".json_encode($eventData);
                    $this->recordLog($cat = "Shipment Event", $log_data, '');
                    $response = $this->_api->postShipmentEvent($goodnoteID, $eventData);
                    $log_data = "Post Shipment Event Response ".json_encode($response);
                    $this->recordLog($cat = "Shipment Event", $log_data, '');
                }
            }
        }
    }
    
    /*Order Data for Delivery date*/
    public function getOrderDeliveryDate($order, $delday, $type = 'pre_order')
    {
        /*  getItemTypeInfo        lead_time_days item_type_info
            ItemTypeInfo :  1 => Pre Order
            ItemTypeInfo :  2 => Made To Order
            ItemTypeInfo :  3 => Print To Order
            ItemTypeInfo :  4 => Trade Order
            ItemTypeInfo :  5 => Bespok Order
        */
        $deliverydate = '';
        $oprObj = $this->_objectManager->create('Bsitc\Brightpearl\Model\Bporderporelation');
        $collection = $oprObj->getCollection()->addFieldToFilter('order_id', $order->getId())->setOrder('deliverydate', 'desc');
        if ($collection->getSize()) {
            $item =  $collection->getFirstItem();
            $deliverydate  = $item->getDeliverydate();
        } else {
            $deliverydate = date('Y-m-d h:m:s', strtotime($order->getCreatedAt() . '+' . $delday . 'days'));
        }
        if ($type =='parent') {
            $deliverydate = date('Y-m-d h:m:s', strtotime($order->getCreatedAt() . '+' . $delday . 'days'));
            return $deliverydate;
        }
        return $deliverydate;
    }
    
        
    public function getMtoDeliveryDate($order)
    {
        /*  getItemTypeInfo        lead_time_days item_type_info
            ItemTypeInfo :  1 => Pre Order
            ItemTypeInfo :  2 => Made To Order
            ItemTypeInfo :  3 => Print To Order
            ItemTypeInfo :  4 => Trade Order
            ItemTypeInfo :  5 => Bespok Order
        */
        $deliverydate = $order->getCreatedAt();
        $lead_time_days = 0;
        $lead_time_days_array = [];
        $order_items = $order->getAllVisibleItems();
        foreach ($order_items as $item) {
            if ($item->getItemTypeInfo() == 2 and $item->getLeadTimeDays() != "") {
                $lead_time_days_array[] = $item->getLeadTimeDays();
            }
        }
         rsort($lead_time_days_array);
        if (count($lead_time_days_array) > 0) {
            $lead_time_days = $lead_time_days_array[0];
            $deliverydate = date('Y-m-d h:m:s', strtotime($order->getCreatedAt() . '+' . $lead_time_days . 'days'));
        }
        return $deliverydate;
    }
    
    public function getPtoDeliveryDate($order)
    {
        /* getItemTypeInfo        lead_time_days item_type_info
            ItemTypeInfo :  1 => Pre Order
            ItemTypeInfo :  2 => Made To Order
            ItemTypeInfo :  3 => Print To Order
            ItemTypeInfo :  4 => Trade Order
            ItemTypeInfo :  5 => Bespok Order
        */
        $deliverydate = $order->getCreatedAt();
        $lead_time_days = 0;
        $lead_time_days_array = [];
        $order_items = $order->getAllVisibleItems();
        foreach ($order_items as $item) {
            if ($item->getItemTypeInfo() == 3 and $item->getLeadTimeDays() != "") {
                $lead_time_days_array[] = $item->getLeadTimeDays();
            }
        }
         rsort($lead_time_days_array);
        if (count($lead_time_days_array) > 0) {
            $lead_time_days = $lead_time_days_array[0];
            $deliverydate = date('Y-m-d h:m:s', strtotime($order->getCreatedAt() . '+' . $lead_time_days . 'days'));
        }
        return $deliverydate;
    }
 
    /*Create Order and Pass the data in Orders*/
    public function getBpOrderData($order, $brightpearlUserId, $email, $shippingAdress)
    {
		$shippingmethod = $order->getShippingMethod(); /*Magento Shipping Methods*/
		$shippingMethodId = $this->getShippingMapping($order); /*Fetch Shipping Method from mapping or config*/
		$customergroupid = $order->getCustomerGroupId(); /*Magento Customer Group Id*/
		$leadsourceid = $this->getBpleadsource($customergroupid); /*Fetch Lead Source from mapping or config*/
		$warehouseid = $this->getBpWarehouse($order);
		$channelId = $this->getChannel();
		$delday = $this->getShippingMappingDeliveryDays($order);  # set default id Delivery in Days
		$deldate = date('Y-m-d h:m:s', strtotime($order->getCreatedAt() . '+' . $delday . 'days'));
		$deliveryDate = date(DATE_ISO8601, strtotime($deldate));
		$pos = $order->getPosLocationId(); /*Check If order types for Magento or POS*/
		/*
		if ($pos) {
			$order_status = $this->getPosOrderStatus();
			$channelId     = $this->getPosChannel();
			// Set POS Warehouse
		   //$warehouseid = $this->getPosWarehouse($pos);
		} elseif ($order->getOrderType() == 1) {
			$order_status = $this->getPreOrderStatus();
		} else {
			$order_status = $this->getMgtOrderStatus();
		}
		*/
		 /*
			ItemTypeInfo :  1 => Pre Order
			ItemTypeInfo :  2 => Made To Order
			ItemTypeInfo :  3 => Print To Order
			ItemTypeInfo :  4 => Trade Order
			ItemTypeInfo :  5 => Bespok Order
		*/
        if ($pos) {
            $order_status = $this->getPosOrderStatus();
            $channelId     = $this->getPosChannel();
        } elseif ($order->getItemTypeInfo() == 1) {
            $order_status         	= $this->getPreOrderStatus(); // 1 => Pre Order
            $deldate            	= $this->getOrderDeliveryDate($order, $delday, 'pre_order');
            $deliveryDate        	= date(DATE_ISO8601, strtotime($deldate));
        } elseif ($order->getItemTypeInfo() == 2) {
            $order_status       	= $this->getMtoStatus();        // 2 => Made Order
            $deldate            	= $this->getMtoDeliveryDate($order);
            $deliveryDate        	= date(DATE_ISO8601, strtotime($deldate));
        } elseif ($order->getItemTypeInfo() == 3) {
            $order_status        	= $this->getPtoStatus();        // 3 => Print Order
            $deldate            	= $this->getPtoDeliveryDate($order);
            $deliveryDate        	= date(DATE_ISO8601, strtotime($deldate));
        } elseif ($order->getItemTypeInfo() == 4) {
            $order_status     		= $this->getTradeOrderStatus();  // 4 => Trade Order
            $deldate         		= $this->getOrderDeliveryDate($order, $delday, 'trade_order');
            $deliveryDate    		= date(DATE_ISO8601, strtotime($deldate));
			$channelId     			= $this->getTradeChannel();
        } elseif ($order->getItemTypeInfo() == 5) {
            $order_status    		= $this->getBespokOrderStatus(); // 5 => Bespoke Order
            $deldate        		= $this->getOrderDeliveryDate($order, $delday, 'bespoke_order');
            $deliveryDate    		= date(DATE_ISO8601, strtotime($deldate));
        } elseif ($order->getItemTypeInfo() == 6) {
            $order_status    		= $this->getTradeOrderStatus(); // 6 => Trade Order
            $deldate         		= $this->getOrderDeliveryDate($order, $delday, 'trade_order');
            $deliveryDate    		= date(DATE_ISO8601, strtotime($deldate));
			$channelId     			= $this->getTradeChannel();
        } else {
            $order_status     		= $this->getMgtOrderStatus();
            $deldate         		= $this->getOrderDeliveryDate($order, $delday, 'normal_order');
            $deliveryDate   		= date(DATE_ISO8601, strtotime($deldate));
        }
		
		$specailOrderStatus = $this->getSpecialSkuOrderStatus($order);
		if($specailOrderStatus and trim($specailOrderStatus) != "" )
		{
			$log_data = 'Order Status Id '.$order_status.' replace by Special Order status id '.$specailOrderStatus;
			$this->recordLog("Order", $log_data, $order->getIncrementId());
			$order_status  = $specailOrderStatus;
		}
		
		

		/*Get Mapping for shipping methods and Data*/
		$reference            = $order->getIncrementId();
		$placedOn            = date(DATE_ISO8601, strtotime($order->getCreatedAt()));
		$currency            = $order->getOrderCurrencyCode();
		$priceListId        = $this->getMappedPricelist($order);
		$addressFullName    = $shippingAdress['firstname'] . ' ' . $shippingAdress['lastname'];
		$companyName        = $shippingAdress['company'];
		$telephone            = $shippingAdress['telephone'];
		$mobileTelephone    = '';
		$orderStatusId        = $order_status;
		 //  -------- prepare array to post data on BP -----------
		$step1 = [];
		$step1['orderTypeCode'] = 'SO';
		$step1['reference'] = $reference;
		$step1['priceListId'] = $priceListId;
		$step1['placedOn'] = $placedOn;
		$step1['orderStatus']['orderStatusId'] = $orderStatusId;
		$step1['delivery']['deliveryDate'] = $deliveryDate;
		$step1['delivery']['shippingMethodId'] = $shippingMethodId;
		$step1['currency']['orderCurrencyCode'] = $currency;
		$step1['currency']['fixedExchangeRate'] = 'true';
		//$step1['currency']['exchangeRate'] = $this->getMgtExchangeRate($order);
		//$step1['currency']['exchangeRate'] = '';
		$step1['assignment']['current']['channelId'] = $channelId;
		$step1['parties']['customer']['contactId'] = $brightpearlUserId;
		$step1['parties']['delivery']['addressFullName'] = $addressFullName;
		$step1['parties']['delivery']['companyName'] = $companyName;
		$step1['parties']['delivery'] = $this->getAddressArray($shippingAdress, $order->getShippingAddress());
		$step1['parties']['delivery']['telephone'] = $telephone;
		$step1['parties']['delivery']['mobileTelephone'] = $mobileTelephone;
		$step1['parties']['delivery']['email'] = $email;
		$step1['parties']['delivery']['addressFullName'] = $addressFullName;
		$step1['parties']['delivery']['companyName'] = $companyName;

		/*Advance data*/
		$step1['assignment']['current']['leadSourceId'] = $leadsourceid;
		
		//$step1['warehouseId'] = $this->getBpWarehouse();

		$step1['warehouseId'] = $warehouseid;
		
		
		/* ------------  custom filed --------------------*/
		//$data = [['op'=> 'add', 'path' => '/PCF_POSSTAFF', 'value' => 'VASVIJAY']];

		//$step1['customFields'][]['PCF_POSSTAFF'] = 'VASVIJAY';
		//$this->recordLog("Order", json_encode($step1), 'Order Data');
		
		/* ------------  custom filed --------------------*/
		

		 /*if($this->warehouseId > 0) {
			 $step1['warehouseId'] = $this->warehouseId;
		}*/

		/*Post Order Data to logs*/
		$log_data = json_encode($step1);
		$this->recordLog($cat = "Order Data", $log_data, $title = $reference);

		 $response = $this->_api->postOrder($step1);
		$orderId = '';
        if (array_key_exists('errors', $response)) {
            /*Logging Data*/
            $log_data = json_encode($response);
            $this->recordLog($cat = "Order", $log_data, $title = $reference);
        } else {
            $orderId = $response['response'];
        }
		return $orderId;
    }

	public function getFinalOrderItemsList($order)
	{
		$enabletopostchild =  $this->getConfig('bpconfiguration/bp_orderconfig/enabletopostchild');
 		if($enabletopostchild)
		{
			$attributesettopostchild =  $this->getConfig('bpconfiguration/bp_orderconfig/attributesettopostchild');
 			$configureAttributesets = array();
			if($attributesettopostchild){
				$configureAttributesets = explode(",",$attributesettopostchild);
			}
			
			$parentChildArray = array();
			$items = array();
			foreach ($order->getAllItems() as $item) {
				if($item->getParentItemId()) {
					$parentChildArray[$item->getParentItemId()][$item->getId()] = $item;
				}
			}
			if(count($parentChildArray)>0)
			{
				foreach ($order->getAllVisibleItems() as $item)	{
					$productAttributeSetId = $item->getProduct()->getAttributeSetId();
					if (in_array($productAttributeSetId, $configureAttributesets)) {
						if (array_key_exists($item->getId(), $parentChildArray)) {
							$childItems = $parentChildArray[$item->getId()];
							foreach($childItems as $childId => $childItem) {
								$items[$childId] = $childItem;
							}
						}else{
							$items[$item->getId()] = $item; /* -------- add this to fix the missing sku due to wrong attribute set --------- */
						}
					}else{
						$items[$item->getId()] = $item;
					}
				}
			}else{
				foreach ($order->getAllVisibleItems() as $item)	{
					$items[$item->getId()] = $item;
				}
 			}
			
		}else{
			$items = $order->getAllVisibleItems();
		}
		return $items;	
	}  
	
    /* ---- Add Row to Orders(Add product to Order) ------ */
    public function getOrderRow($order, $orderId)
    {
        $data = [];
        // $items = $order->getAllVisibleItems();
        $items = $this->getFinalOrderItemsList($order);
		$this->_swedishArray = $this->getSwedishItemsArray($order);
		$productAttributeSetsOption = $this->getProductAttributeSets();
		
		$this->recordLog("productAttributeSetsOption",  $productAttributeSetsOption, $order->getIncrementId());
		$enableskureplacement = $this->getConfig('bpconfiguration/bp_orderconfig/enableskureplacement');
		
        $reservationProductArray = [];
        $reference = $order->getIncrementId();
        foreach ($items as $itemId => $item) 
		{
			/*Get Product Id*/
			$productid = $item['product_id'];
			$productRepository = $this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
			$product = $productRepository->getById($productid);
			$taxclassid = $product->getTaxClassId();
			$final_sku = $item->getSku();
			if($enableskureplacement) {
				$final_sku = trim( $this->_skureplacementFactory->getReplacedSku($order->getStoreId(), $order->getShippingAddress()->getCountryId(), $item->getSku()) );
				$this->recordLog("Final SKU After Repalce",  $item->getSku().' => '.$final_sku, $reference);
			}

            $discount_tax_compensation_amount =  0;
            if ($item->getDiscountTaxCompensationAmount()  > 0) {
                $discount_tax_compensation_amount = $item->getDiscountTaxCompensationAmount();
            }

			/* ----------------- builder product setup -----------------*/
			if($item->getItemTypeInfo() == 5 and $order->getItemTypeInfo() == 5 )
			{
				$productAttributeSetId = $item->getProduct()->getAttributeSetId();
				$this->recordLog("productAttributeSetId",  $productAttributeSetId, $order->getIncrementId());
				$prfix = '';
				if($productAttributeSetsOption[$productAttributeSetId] == 'Curtains'){
					$prfix = $this->getConfig('bpconfiguration/builders/curtain_prfix');
				}
				if($productAttributeSetsOption[$productAttributeSetId] == 'Blinds'){ 
					$prfix = $this->getConfig('bpconfiguration/builders/blind_prfix');
				}
				if($productAttributeSetsOption[$productAttributeSetId] == 'Furniture'){
					$prfix = $this->getConfig('bpconfiguration/builders/furniture_prfix');
				}
				
				if($prfix){
					$final_sku = $prfix.'-'.$final_sku; 
					$final_sku =  str_replace("&","%26",$final_sku);
				}
				
				$pid = $this->_api->getProductIDFromSku($final_sku);  //  Check Product exist on Brightpearl   
				if(!$pid) /* ----------- if product not found that check with orginal sku */
				{
					$this->recordLog("Order row", 'Bespoke SKU NOt found '.$final_sku,  $reference);
					$final_sku = $item->getSku();
					$final_sku =  str_replace("&","%26",$final_sku);
					$pid = $this->_api->getProductIDFromSku($final_sku);  //  Check Product exist on Brightpearl   
				}
				$final_name = $this->getItemFinalName($item);
			}
			else
			{
				$final_name = $item->getName();
				//$final_sku =  str_replace("&","%26",$item->getSku());
				$final_sku =  str_replace("&","%26",$final_sku);
				$pid = $this->_api->getProductIDFromSku($final_sku);  //  Check Product exist on Brightpearl   
			}
			/* ----------------- builder product setup -----------------*/
                 
            if ($pid) {
                $productId = (int) $pid;
                $nominalcode = '';
                $taxCode = '';
                $rowTax = '';
                    
               /* ---------- get product data from brightpearl ---------*/
                $bpProduct = [];
                $bpProductData = $this->_api->getProductById($productId);
                if (array_key_exists('response', $bpProductData)) {
                    if (array_key_exists('0', $bpProductData['response'])) {
                        $bpProduct = $bpProductData['response'][0];
                    }
                }
                 /* ---------- get product data from brightpearl ---------*/
                if (array_key_exists('nominalCodeSales', $bpProduct) and $bpProduct['nominalCodeSales']!= "") {
                     $nominalcode = $bpProduct['nominalCodeSales'];
                } else {
                    $nominalcode = $this->getNominalCode();
                }
                    
                if ($item->getTaxAmount()) {
                    $taxCode = $this->getTaxable($taxclassid); #  T For Taxable
                    $rowTax = number_format($item->getTaxAmount(), '2', '.', '') ;
                } else {
                    $taxCode = $this->getNonTaxable();  # N for Non Taxable
                    $rowTax = '0.00';
                }
                    
				$rowTax = $rowTax + $discount_tax_compensation_amount;

				$rowTotal = number_format($item->getRowTotal(), '2', '.', '');
				$quantity = (int)$item->getQtyOrdered();
				$prow                                 = [];
				$prow['productId']                     = $productId;
				$prow['quantity']['magnitude']         = $quantity;
				$prow['rowValue']['taxCode']         = $taxCode;
				$prow['rowValue']['rowNet']['value'] = $rowTotal;
				$prow['rowValue']['rowTax']['value'] = $rowTax;
				$prow['nominalCode']                  = $nominalcode;
 
				if($item->getItemTypeInfo() == 5 ){
					$prow['productName'] = $final_name; // 5 => bespoke item curtain or blind or furniture
				}
				
				$prow = $this->refineBpRow($order, $prow); // ------- Replace the Tax class code with T0 
				// ------- Refine Swedish Row 
				if( $this->isSwedishProduct( $order, $item->getProduct() ) )
				{
					$prow = $this->refineSwedishRow($order, $prow , $item, 'oldapi');  
					$this->recordLog("Order", json_encode($prow,true),  'refineSwedishRow');
				}
				// ------- Refine Swedish Row 

				$log_data = 'Post Order Data request : ' . json_encode($prow);
				$this->recordLog($cat = "Order", $log_data, $title = $reference);  /*  ---- Request logs ---- */
				$responses = $this->_api->postOrderRow($orderId, $prow);
				$salesOrderRowId = '';
                if (array_key_exists('response', $responses)) {
                    $salesOrderRowId = $responses['response'];
                }
                    
                if (preg_match("/\bmany requests\b/i", $salesOrderRowId) || preg_match("/\bYou have sent too many requests\b/i", $salesOrderRowId)) {
                    usleep(1000000);
                    $responses = $this->_api->postOrderRow($orderId, $prow);
                    $salesOrderRowId = '';
                    if (array_key_exists('response', $responses)) {
                        $salesOrderRowId = $responses['response'];
                    }
                }
                     
                  /*Start Assigning data to arrays*/

                  /*Response logs*/
                  $log_data = 'Post Order Data response : ' . json_encode($responses);
                  $this->recordLog($cat = "Order", $log_data, $title = $reference);


                  $data['salesOrderRowId'] = $salesOrderRowId;
                  /*Ends Assigning data to arrays*/

                if ($salesOrderRowId > 0) {
                    # ---- Prepare array  for inventory reservation --------
                    $reservationProductArray[] = '{productId:"' . $productId . '",salesOrderRowId:"' . $salesOrderRowId . '",quantity:"' . $quantity . '"}';
                }
                  $data['reservationProductArray'] = $reservationProductArray;
            } else {
                /*!!!! If products does not exits !!!!*/
                 //  ---- start add missing sku in row ----
                $taxCode = '';
                $rowTax = '';
                if ($item->getTaxAmount()) {
                      $taxCode = $this->getTaxable($taxclassid); #  T For Taxable
                      $rowTax = number_format($item->getTaxAmount(), '2', '.', '') ;
                } else {
                    $taxCode = $this->getNonTaxable();  # N for Non Taxable
                    $rowTax = '0.00';
                }
                    
				$rowTax = $rowTax + $discount_tax_compensation_amount;

				$nominalcode = $this->getNominalCode();
				$rowTotal     = number_format($item->getRowTotal(), '2', '.', '');
				$quantity     = (int)$item->getQtyOrdered();
				$prow = [];
				$prow['productName'] = 'Missing SKU : ' . $final_sku . 'Missing Product : ' . $item->getProduct()->getName();
				$prow['quantity']['magnitude'] = $quantity;
				$prow['rowValue']['taxCode'] = $taxCode;
				$prow['rowValue']['rowNet']['value'] = $rowTotal;
				$prow['rowValue']['rowTax']['value'] = $rowTax;
				$prow['nominalCode']                  = $nominalcode;

				if($item->getItemTypeInfo() == 5 ){
					$prow['productName'] = $final_name; // 5 => bespoke item curtain or blind or furniture
				}
				
				$prow = $this->refineBpRow($order, $prow); // ------- Replace the Tax class code with T0 
				// ------- Refine Swedish Row 
				if( $this->isSwedishProduct( $order, $item->getProduct() ) )
				{
					$prow = $this->refineSwedishRow($order, $prow , $item, 'oldapi');  
					$this->recordLog("Order", json_encode($prow,true),  'refineSwedishRow');
				}
				// ------- Refine Swedish Row 

				$response = $this->_api->postOrderRow($orderId, $prow);
				$salesOrderRowId = '';
                    
                if (array_key_exists('response', $response)) {
                    $salesOrderRowId = $response['response'];
                    if (preg_match("/\bmany requests\b/i", $salesOrderRowId) || preg_match("/\bYou have sent too many requests\b/i", $salesOrderRowId)) {
                           usleep(1000000);
                           $response = $this->_api->postOrderRow($orderId, $prow);
                           $salesOrderRowId = $response['response'];
                    }
                } else {
                    usleep(1000000);
                    $response = $this->_api->postOrderRow($orderId, $prow);
                    if (array_key_exists('response', $response)) {
                        $salesOrderRowId = $response['response'];
                    }
                }
                     
                 $log_data = 'Response from missing sku : ' . json_encode($response);
                 $this->recordLog($cat = "Order", $log_data, $title = $reference);
                 //  ---- start add missing sku in row ----
            }
        }
            return $data;
    }
     
	/*Reduce Coupon Code Amount from Order*/
    public function CouponCodeDiscount($order, $bpOrderId , $return_type = "")
    {
		$reference = $order->getIncrementId();
		$discount_amount         = $order->getDiscountAmount();
		$discount_description     = $order->getDiscountDescription();
		
		if ($order->getGiftCardsAmount() > 0) {
			$applyGiftCards = $this->getApplyGiftCards($order);
			if($applyGiftCards){
				$discount_description = $discount_description . ' Used GiftCrad :'.$applyGiftCards; 
			}
 		}
		
                     
        if ($order->getDiscountAmount() && $order->getDiscountAmount() < 0) {
            if ($order->getDiscountTaxCompensationAmount() > 0) {
				$discount_amount =  $discount_amount + $order->getDiscountTaxCompensationAmount();
				$taxCode =  $this->getConfig('bpconfiguration/bp_orderconfig/taxcode');
				$rowTax =  $order->getDiscountTaxCompensationAmount() * -1 ;
				$rowTotal = $discount_amount;
            } else 
			{
				
				$taxCode = $this->getNonTaxable(); # N for Non Taxable
				$rowTax = '0.00';
				$rowTotal = $discount_amount;
				
				$taxAmount = $this->calculateTaxForExtraFee($order, $discount_amount);
				if($taxAmount)
				{
					$taxCode =  $this->getConfig('bpconfiguration/bp_orderconfig/taxcode');
					$rowTax = $taxAmount;
					$rowTotal = $discount_amount + ( $taxAmount * -1 );
				}				
            }
			// discount_tax_compensation_amount
			 
			$discount_row = [];
			$discount_row['productName']                     = 'Discount : ' . $discount_description;
			$discount_row['quantity']['magnitude']             = 1;
			$discount_row['rowValue']['taxCode']             = $taxCode;
			$discount_row['rowValue']['rowNet']['value']     = $rowTotal;
			$discount_row['rowValue']['rowTax']['value']     = $rowTax;
			$nominalCode = $this->getDiscountNominalCode();
			if ($nominalCode != "" && $nominalCode != null) {
				$discount_row['nominalCode'] = trim($nominalCode);
			}
			
			$discount_row = $this->refineBpRow($order, $discount_row); // ------- Replace the Tax class code with T0 
			
			$log_data = 'Discount refineBpRow : ' .json_encode($discount_row);
			$this->recordLog("Order", $log_data, $reference);
			
			
			$discount_row = $this->refineSwedishDiscountRow($order, $discount_row); // ------- apply  Swedish vat on discount if applicable 
			
			$log_data = 'Discount refineSwedishDiscountRow : ' . json_encode($discount_row);
			$this->recordLog("Order", $log_data, $reference);
			
			if($return_type == 'rowArray'){
				return $discount_row;
			}			

			$responses = $this->_api->postOrderRow($bpOrderId, $discount_row);  //  call add order row api
			$salesOrderRowId = $responses['response'];

            if (isset($responses['response']) and $salesOrderRowId > 0) {
                $log_data = 'Discount row added successfully : '.$discount_description.' Discount Price : '.$rowTotal;      //  Insert Log
                $this->recordLog($cat = "Order", $log_data, $title = $reference);
            } else {
                $log_data = 'There are no discount row';      //  Insert Log
                $this->recordLog($cat = "Order", $log_data, $title = $reference);
            }
        }
    }
	
 	/*Reduce Store Credit / Customer Balance Amount from Order*/
    public function CustomerBalanceAmount($order, $bpOrderId, $return_type = "")
    {
            
        $reference             = $order->getIncrementId();
        $postAmount            = $order->getCustomerBalanceAmount();
        $postDescription     = 'Store Credit';
            
        if ($postAmount && $postAmount > 0) {
            $taxCode                 = $this->getNonTaxable();
            $rowTax                 = '0.00';
            $rowTotal                = - $postAmount;
                 
            $postRow = [];
            $postRow['productName']                     = $postDescription;
            $postRow['quantity']['magnitude']             = 1;
            $postRow['rowValue']['taxCode']             = $taxCode;
            $postRow['rowValue']['rowNet']['value']     = $rowTotal;
            $postRow['rowValue']['rowTax']['value']     = $rowTax;
                
            $nominalCode = $this->getStoreCreditNominalCode();
                
            if ($nominalCode != "" && $nominalCode != null) {
                $postRow['nominalCode'] = trim($nominalCode);
            }
			
			$postRow = $this->refineBpRow($order, $postRow); // ------- Replace the Tax class code with T0 
			
            $log_data = json_encode($postRow);
            $this->recordLog("Order", $log_data, $reference);

            $responses = $this->_api->postOrderRow($bpOrderId, $postRow);  //  call add order row api
            $salesOrderRowId = $responses['response'];
                
            if (isset($responses['response']) and $salesOrderRowId > 0) {
                $log_data = 'Store Credit or Customer Balance row added successfully. Price : '.$rowTotal; //-Insert Log
                $this->recordLog("Order", $log_data, $reference);
            } else {
                $log_data = 'There are no discount row';      //  Insert Log
                $this->recordLog("Order", $log_data, $reference);
            }
        }
    }

	/* add rounding difference row in order */
    public function addRoundingDifferencAmountRow($order, $bpOrderId, $return_type = "")
    {
        $postAmount = 0 ;
        $orderTotal = $order->getGrandTotal() ;
        $invoicedTotal = $order->getTotalInvoiced();
        if ($invoicedTotal != $orderTotal) {
            $postAmount = $orderTotal - $invoicedTotal ;
        }
        $postAmount = number_format($postAmount, '2', '.', '');
        $isRoundingEnable = $this->getRoundingEnable();
        $roundingThresholdLimit = $this->getRoundingThreshold();
        $nominalCode = $this->getRoundingSkuNominalCode();
		$this->recordLog("Order", $postAmount, $order->getIncrementId());
        $postAmount1         = $postAmount ;
        $absRounbdingAmount = abs($postAmount1);
        if ($isRoundingEnable and $absRounbdingAmount < $roundingThresholdLimit) {
            if ($postAmount != 0) {
                  $reference = $order->getIncrementId();
                  $postDescription     = 'Rounding Adjustment';
                  $taxCode = $this->getNonTaxable();
                  $rowTax = '0.00';
                  $rowTotal = $postAmount;
                     
                  $postRow = [];
                  $postRow['productName']                     = $postDescription;
                  $postRow['quantity']['magnitude']             = 1;
                  $postRow['rowValue']['taxCode']             = $taxCode;
                  $postRow['rowValue']['rowNet']['value']     = $rowTotal;
                  $postRow['rowValue']['rowTax']['value']     = $rowTax;
                if ($nominalCode != "" && $nominalCode != null) {
                    $postRow['nominalCode'] = trim($nominalCode);
                }

				$postRow = $this->refineBpRow($order, $postRow); // ------- Replace the Tax class code with T0 
				$log_data = json_encode($postRow);
				$this->recordLog("Order", $log_data, $reference);
				
				if($return_type == 'rowArray'){
					return $postRow;
				}

				$responses = $this->_api->postOrderRow($bpOrderId, $postRow);  //  call add order row api
				$salesOrderRowId = $responses['response'];
                    
                if (isset($responses['response']) and $salesOrderRowId > 0) {
                    $log_data = 'Rounding Adjustment row added successfully. Price : '.$rowTotal; //-Insert Log
                    $this->recordLog("Order", $log_data, $reference);
                } else {
                    $log_data = 'There are no discount row';      //  Insert Log
                    $this->recordLog("Order", $log_data, $reference);
                }
            }
        }
    }

    /*Reduce Coupon Code Amount from Order*/
    public function OrderShippingCharge($order, $bpOrderId, $return_type = "")
    {
        if ($order->getShippingTaxAmount()) 
		{
			$reference = $order->getIncrementId();
			$nominalCode     = $this->getShippingNominalCode(); # Get from configuratiom
            if ($order->getShippingTaxAmount() && $order->getShippingTaxAmount() > 0) {
                $taxCode     = $this->getConfig('bpconfiguration/bp_orderconfig/taxcode');  #  T For Taxable
                $rowTax     = number_format($order->getShippingTaxAmount(), '2', '.', '');
            }
			else 
			{
				$taxCode = $this->getNonTaxable(); # N for Non Taxable
				$rowTax = '0.00';
				/*
				$taxAmount = $this->calculateTaxForExtraFee( $order, $order->getShippingAmount() );
				if($taxAmount > 0 )
				{
					$taxCode =  $this->getConfig('bpconfiguration/bp_orderconfig/taxcode');
					$rowTax = '0.00';
				}
				*/
 			}
			$rowTotal = number_format($order->getShippingAmount(), '2', '.', '');
			$prow = [];
			$prow['productName'] = 'Shipping Method - '.$order->getShippingDescription();
			$prow['quantity']['magnitude'] = 1;
			$prow['rowValue']['taxCode'] = $taxCode;
			$prow['rowValue']['rowNet']['value'] = $rowTotal;
			$prow['rowValue']['rowTax']['value'] = $rowTax;
			if ($nominalCode != "" && $nominalCode != null) {
				$prow['nominalCode'] = trim($nominalCode);
			}
			$prow = $this->refineBpRow($order, $prow); // ------- Replace the Tax class code with T0 

			if($return_type == 'rowArray'){
				return $prow;
			}
			$prow = $this->refineSwedishOtherRow($order, $prow, 'oldapi'); // -------  SwedishRow
			$responses = $this->_api->postOrderRow($bpOrderId, $prow);  //  call add order row api
			$salesOrderRowId = '';
            if (array_key_exists('response', $responses)) {
                $salesOrderRowId = $responses['response'];
            }

            if (isset($responses['response']) and $salesOrderRowId > 0) {
                $log_data = 'Shipping Method row added succeesfully title : '.$order->getShippingDescription().' Price : '.$rowTotal;      //  Insert Log
                $this->recordLog($cat = "Order", $log_data, $title = $reference);
            } else {
                $log_data = 'Shipping charges are not applied';      //  Insert Log
                $this->recordLog($cat = "Order", $log_data, $title = $reference);
            }
        }
    }

	/*code inventory reservation for ordered product*/
    public function setInventoryToOrderRow($order, $incrementid, $bporderId, $salesOrderRowId, $reservationProductArray)
    {
        $responses = '';
        if (count($reservationProductArray) > 0) {
            $param = '{	products:[ '.implode(",", $reservationProductArray).' ] } ';
                
            $warehouse = $this->getBpWarehouse($order);
            /*Reserve Inventory for Order Row and Assign in products*/
                
            $response = $this->_api->postInventoryReservation($bporderId, $warehouse, $param); //call inventroty reservation api
            /*Log for Inventory*/
            $log_data = "ReserverInverntory ". json_encode($response);
            $this->recordLog($cat = "Order", $log_data, $title = $incrementid);

            /*Check Inventory for products*/
            $responses = $this->_api->checkInventoryReservation($bporderId, $param);
            $log_data = "Check ReserverInverntory ". json_encode($responses);
            $this->recordLog($cat = "Order", $log_data, $title = $incrementid);
        }
        return $responses;
    }

    public function getPosOrderPayment($order)
    {
        $data = '';
         $paymentsFactory = $this->_objectManager->create('\Magestore\Webpos\Model\ResourceModel\Sales\Order\Payment\CollectionFactory');
        $paymentsCollection = $paymentsFactory->create()->addFieldToFilter('order_id', $order->getId())
        ->addFieldToFilter('type', \Magestore\Webpos\Api\Data\Payment\PaymentInterface::ORDER_TYPE);
        if ($paymentsCollection->getSize()) {
              $data  = $paymentsCollection->getFirstItem();
        }
            return $data;
    }

	/*Payment Received for Order*/
    public function postCustomerPayment($Bapp, $order, $bpOrderId, $brightpearlUserId)
    {
        /*Get payment Methods in Magento 2*/
        $payment       = $order->getPayment();
        $paymentcode  =  $payment->getMethod();
        /* ------------- if pos order then then get method from pos object ------*/
        $posid = $order->getPosStaffId();
        if ($posid) {
            $pos_payment = '';
             $pos_payment = $this->getPosOrderPayment($order);
            if ($pos_payment) {
                 $paymentcode  = $pos_payment->getMethod();
            }
        }
        /* ------------- if pos order then then get method from pos object ------*/
        $reference = $order->getIncrementId();
        $bankAccountNominalCode = '';
        if ($paymentcode) {
            $bankAccountNominalCode = $this->getMapPaymentMethod($paymentcode);
        }
        /// --------- get exchange rate ---------------
        $orderTotal = number_format($order->getGrandTotal(), '2', '.', '');
        $description = 'Payment Received against the Magento Order #'.$order->getIncrementId();
        /// -------- create sales receipt array   --------------------
		$paymentsArray = [];
		$paymentsArray['paymentMethodCode'] = $bankAccountNominalCode;
		$paymentsArray['paymentType'] = 'RECEIPT';
		$paymentsArray['orderId'] = $bpOrderId;
		$paymentsArray['currencyIsoCode'] = $order->getOrderCurrencyCode();
		//$paymentsArray['exchangeRate'] = $exchangeRate;
		$paymentsArray['amountPaid'] = $orderTotal;
		$paymentsArray['paymentDate'] = date("Y-m-d");
		$paymentsArray['journalRef'] = $description;
        /*Post Payment Data in logs files*/
        $log_data = "Order Payment Data ". json_encode($paymentsArray);
        $this->recordLog($cat = "Order", $log_data, $title = $reference);
        /// -------- post sales receipt array   --------------------
        $response = $Bapp->postCustomerPayment($paymentsArray);
        return $response ;
    }
        
	/* ------------------- Payment Received for Order from store credit or customer balance ---------------------- */
    public function postStoreCreditPayment($Bapp, $order, $bpOrderId, $brightpearlUserId)
    {
        $reference = $order->getIncrementId();
        $postAmount = number_format($order->getCustomerBalanceAmount(), '2', '.', '');
        $bankAccountNominalCode = '';
        if ($postAmount && $postAmount > 0) {
            $paymentcode            =  'store_credit';
            $description             = 'Store Credit Payment Received against the Magento Order #'.$reference;
            $bankAccountNominalCode = $this->getMapPaymentMethod($paymentcode);
             /// -------- create sales receipt array   --------------------
            $paymentsArray = [];
            $paymentsArray['paymentMethodCode'] = $bankAccountNominalCode;
            $paymentsArray['paymentType'] = 'RECEIPT';
            $paymentsArray['orderId'] = $bpOrderId;
            $paymentsArray['currencyIsoCode'] = $order->getOrderCurrencyCode();
             $paymentsArray['amountPaid'] = $postAmount;
            $paymentsArray['paymentDate'] = date("Y-m-d");
            $paymentsArray['journalRef'] = $description;
            /*Post Payment Data in logs files*/
            $log_data = "Order Store Credit Payment Data ". json_encode($paymentsArray);
            $this->recordLog("Order", $log_data, $reference);
             /// -------- post sales receipt array   --------------------
            $response = $Bapp->postCustomerPayment($paymentsArray);
            return $response ;
        }
    }

	/* ------------------- Payment Received for Order from Gift Crds Amount or customer balance ---------------------- */
    public function postGiftCardsPayment($Bapp, $order, $bpOrderId, $brightpearlUserId)
    {
        $reference = $order->getIncrementId();
        $postAmount = number_format($order->getGiftCardsAmount(), '2', '.', '');
        $bankAccountNominalCode     = '';
        if ($postAmount && $postAmount > 0) {
			$paymentcode =  'gift_voucher';
			$description = 'Gift Crds Payment Received against the Magento Order #'.$reference;
			$bankAccountNominalCode = $this->getMapPaymentMethod($paymentcode);
			/// -------- create sales receipt array   --------------------
			$paymentsArray = [];
			$paymentsArray['paymentMethodCode'] = $bankAccountNominalCode;
			$paymentsArray['paymentType'] = 'RECEIPT';
			$paymentsArray['orderId'] = $bpOrderId;
			$paymentsArray['currencyIsoCode'] = $order->getOrderCurrencyCode();
			$paymentsArray['amountPaid'] = $postAmount;
			$paymentsArray['paymentDate'] = date("Y-m-d");
			$paymentsArray['journalRef'] = $description;
			/*Post Payment Data in logs files*/
			$log_data = "Order Gift Crds Payment Data ". json_encode($paymentsArray);
			$this->recordLog("Order", $log_data, $reference);
			/// -------- post sales receipt array   --------------------
			$response = $Bapp->postCustomerPayment($paymentsArray);
			return $response ;
        }
    }
            
    public function getBpCustomerInfoArray($obj1, $telephone = "")
    {
		/* ------ Call pricelist from configurations ----------- */
		$currencyId =  $this->getConfig('bpconfiguration/bp_customerconfig/defaultcurrency', $obj1['website_id'] );
		$priceListId = '';
        if ($this->getPricelist()) {
            $priceListId = $this->getPricelist();
        } else {
            $priceListId = '';
        }
        if ($telephone == "") {
            if (array_key_exists('telephone', $obj1)) {
                $telephone = $obj1['telephone'];
            }
        }
		$custInfo = [];
		$custInfo['salutation'] = '';
		$custInfo['firstName'] = $obj1['firstname'];
		$custInfo['lastName'] = $obj1['lastname'];
		$custInfo['postAddressIds'] = '';
		$custInfo['communication']['emails']['PRI'] = ['email' => $obj1['email']];
		$custInfo['communication']['telephones']['PRI'] = $telephone;
		$custInfo['communication']['websites']['PRI'] = '';
		$custInfo['marketingDetails']['isReceiveEmailNewsletter'] = true;
		$custInfo['financialDetails']['priceListId'] = $priceListId;
		if($currencyId){
			$custInfo['financialDetails']['currencyId'] = $currencyId;		
		}
		return $custInfo;
    }

    /* Create a BP Address Arrays */
    public function getAddressArray($objAddress, $tmpObj = "")
    {
            $country = $this->_countryFactory->create()->loadByCode($objAddress['country_id']);
            $country = $country->getData();
            $isocode = $country['iso3_code'];
            $bpAddress     = [];
            
        if ($tmpObj) { 
             $street =  $tmpObj->getStreet();
            if (is_array($street)) {
                $street1 = '';
                $street2 = '';
                $street3 = '';
                if (array_key_exists("0", $street)) {
                    $street1 = trim($street[0]);
                }
                if (array_key_exists("1", $street)) {
                    $street2 = $street[1];
                }
                if (array_key_exists("2", $street)) {
                    $street3 = $street[2];
                }
                if ($street1 =="") {
                    $street1 = $objAddress['street'];
                }
                    
                $bpAddress['addressLine1'] = $street1;
                $bpAddress['addressLine2'] = $street2.' '.$street3;
            } else {
                $bpAddress['addressLine1'] = $objAddress['street'];
            }
        } else {
             # Street
            $bpAddress['addressLine1'] = $objAddress['street'];
            # Suburb
            //$bpAddress['addressLine2'] = $objAddress->getStreet(2);
        }
            # City
            $bpAddress['addressLine3'] = $objAddress['city'];
            # County/State
            $bpAddress['addressLine4'] = $objAddress['region'];
            # Postcode/Zipcode
            $bpAddress['postalCode'] = $objAddress['postcode'];
            # LookupCountry
            $bpAddress['countryIsoCode'] = $isocode;
            return $bpAddress;
    }
    
    /*Get payment Methods Bank Account Nominal Code */
    public function getPaymentBankAccountNominalCode($paymentcode, $currencyCode)
    {
        $enable  =  $this->getConfig('bpconfiguration/bp_orderconfig/enable');
        $paymentcode = trim($paymentcode);
        $mpnominalcode = '';
        if ($enable) {
            $paymentcollections = $this->_bppaymentmapFactory->create()->getCollection();
            $paymentcollections = $paymentcollections->addFieldToFilter('code', $paymentcode);
            if ($paymentcollections->getSize()) {
                $data  = $paymentcollections->getFirstItem();
                $bpcode = $data->getBpcode();
                if ($bpcode) {
                     $bpPayment         = $this->_objectManager->create('Bsitc\Brightpearl\Model\BppaymentFactory');
                    $collections     = $bpPayment->create()->getCollection();
                    $collections     = $collections->addFieldToFilter('code', $bpcode);
                    if ($collections->getSize()) {
                        $result          = $collections->getFirstItem();
                        $bankAccounts     = json_decode($result->getBankAccounts(), true);
                        foreach ($bankAccounts as $bankAccount) {
                            if ($bankAccount['currencyIsoCode'] == $currencyCode) {
                                $mpnominalcode =     $bankAccount['bankAccountNominalCode'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $mpnominalcode;
    }
	
    public function getProductAttributeSets()
    {
		$option = array();
		$obj = \Magento\Framework\App\ObjectManager::getInstance();
		$searchCriteriaBuilder = $this->_objectManager->create('\Magento\Framework\Api\SearchCriteriaBuilder');
		$attributeSetRepository = $this->_objectManager->create('\Magento\Catalog\Api\AttributeSetRepositoryInterface');
		$searchCriteria = $searchCriteriaBuilder->create();
		$attributeSet = $attributeSetRepository->getList($searchCriteria);
		if ($attributeSet->getTotalCount()) 
		{
			$attributeSetList = $attributeSet;  
			foreach ($attributeSetList->getItems() as $list) 
			{
				$option[$list->getAttributeSetId()] = $list->getAttributeSetName();
			}	
		}
        return $option;
    }
	
	
	public function getItemFinalName($item)
	{
		$final_name = $item->getName();
		$optionArray = $item->getProductOptions();
		if ( array_key_exists("options",$optionArray) )
		{
			foreach($optionArray['options'] as  $key=>$option) {
$final_name .='
'.$option['label'].' : '. $option['value'];	
			}
		}
		if ( array_key_exists("bundle_options",$optionArray) )
		{
			foreach($optionArray['bundle_options'] as  $key=>$option) {
				
				$optQty = $option['value'][0]['qty'];
				$optTitle = $option['value'][0]['title'];
				$optPrice = $option['value'][0]['price'];
$final_name .='
'.$option['label'].' : '. $optQty.' x '.$optTitle.'  '.$optPrice ;
			}
		}
		if ( array_key_exists("additional_options",$optionArray) )
		{
			foreach($optionArray['additional_options'] as  $key=>$option) {
 $final_name .='
'.$option['label'].' : '. $option['value'];
			}
		}
		return $final_name;
	}
	
    /* -------------------- Post Oversize handling fee -------------------- */
    public function postOversizeHandlingFee($order, $bpOrderId, $return_type = "")
    {
		$reference = $order->getIncrementId();
		$amount = $order->getFee();
		$title = 'Oversize Handling Fee';
		$this->recordLog("Order", 'Oversize Handling Fee'.$amount, $reference);
 		if ($amount > 0) 
		{
			$taxCode = $this->getNonTaxable(); # N for Non Taxable
			$rowTax = '0.00';
			$rowTotal = $amount;

			$taxAmount = $this->calculateTaxForExtraFee($order, $amount);
			if($taxAmount)
			{
				$taxCode =  $this->getConfig('bpconfiguration/bp_orderconfig/taxcode');
				$rowTax = $taxAmount;
				$rowTotal = $amount - $taxAmount;
 			}

			$bpRow = [];
			$bpRow['productName']                     = $title;
			$bpRow['quantity']['magnitude']             = 1;
			$bpRow['rowValue']['taxCode']             = $taxCode;
			$bpRow['rowValue']['rowNet']['value']     = $rowTotal;
			$bpRow['rowValue']['rowTax']['value']     = $rowTax;
			$nominalCode = $this->getOversizeHandlingFeeNominalCode();
			if ($nominalCode != "" && $nominalCode != null) {
				$bpRow['nominalCode'] = trim($nominalCode);
			}
			$bpRow = $this->refineBpRow($order, $bpRow); // ------- Replace the Tax class code with T0 
			$log_data = $title . ' '. json_encode($bpRow);
			$this->recordLog("Order", $log_data, $reference);
			if($return_type == 'rowArray'){
				return $bpRow;
			}
			
			$responses = $this->_api->postOrderRow($bpOrderId, $bpRow);  //  call add order row api
			
			$salesOrderRowId = $responses['response'];
			if (isset($responses['response']) and $salesOrderRowId > 0) {
				$log_data = $title.' row added successfully.';      //  Insert Log
				$this->recordLog("Order", $log_data, $reference);
			} else {
				$log_data = 'Unable to post '.$title.' row';      //  Insert Log
				$this->recordLog("Order", $log_data, $reference);
			}
		}
	}
	
    /* ----------- Get configuration for Nominal Code --------------- */
    public function getOversizeHandlingFeeNominalCode()
    {
        return $this->getConfig('bpconfiguration/bp_orderconfig/over_size_handling_nominal');
    }
	
    /* -------------------- Post Extra Shipping fee -------------------- */
    public function postExtraShippingFee($order, $bpOrderId, $return_type = "")
    {
		$reference = $order->getIncrementId();
		$amount = $order->getExtrashippingfee();
		$title = 'Extra Shipping Fee';
		if( $order->getExtrashippingfeelabel() != "") {
			$title = $order->getExtrashippingfeelabel();
		}
		if ($amount > 0) 
		{
			$taxCode = $this->getNonTaxable(); # N for Non Taxable
			$rowTax = '0.00';
			$rowTotal = $amount;
			$taxAmount = $this->calculateTaxForExtraFee($order, $amount);
			if($taxAmount)
			{
				$taxCode =  $this->getConfig('bpconfiguration/bp_orderconfig/taxcode');
				$rowTax = $taxAmount;
				$rowTotal = $amount - $taxAmount;
 			}
			$bpRow = [];
			$bpRow['productName']                     = $title;
			$bpRow['quantity']['magnitude']             = 1;
			$bpRow['rowValue']['taxCode']             = $taxCode;
			$bpRow['rowValue']['rowNet']['value']     = $rowTotal;
			$bpRow['rowValue']['rowTax']['value']     = $rowTax;
			$nominalCode = $this->getExtraShippingFeeNominalCode();
			if ($nominalCode != "" && $nominalCode != null) {
				$bpRow['nominalCode'] = trim($nominalCode);
			}
			$bpRow = $this->refineBpRow($order, $bpRow); // ------- Replace the Tax class code with T0 
			$log_data = $title . ' '. json_encode($bpRow);
			$this->recordLog("Order", $log_data, $reference);
			
			if($return_type == 'rowArray'){
				return $bpRow;
			}
			
			$responses = $this->_api->postOrderRow($bpOrderId, $bpRow);  //  call add order row api
			$salesOrderRowId = $responses['response'];
			if (isset($responses['response']) and $salesOrderRowId > 0) {
				$log_data = $title.' row added successfully.';      //  Insert Log
				$this->recordLog("Order", $log_data, $reference);
			} else {
				$log_data = 'Unable to post '.$title.' row';      //  Insert Log
				$this->recordLog("Order", $log_data, $reference);
			}
		}
	}

    /* ----------- Get configuration for Nominal Code --------------- */
    public function getExtraShippingFeeNominalCode()
    {
        return $this->getConfig('bpconfiguration/bp_orderconfig/extra_shipping_nominal');
    }

    /* ----------- If delivery not UK and EU then tax should be T0 and Same for handling fee --------------- */
 	public function refineBpRow($order, $prow)
	{
		$enablezerotax  =  $this->getConfig('bpconfiguration/bp_orderconfig/enablezerotax');
		$zerotaxcountry  =  $this->getConfig('bpconfiguration/bp_orderconfig/zerotaxcountry');
 		if($enablezerotax)
		{
			$configureCountry = explode(",",$zerotaxcountry);
 			$shippingCountry = $order->getShippingAddress()->getCountryId();
 			if (in_array($shippingCountry, $configureCountry)) 
			{
				$taxCode = $this->getNonTaxable();  # N for Non Taxable
				$rowTax = '0.00';
				$rowTotal = $prow['rowValue']['rowNet']['value'] +  $prow['rowValue']['rowTax']['value'];
				$prow['rowValue']['taxCode']         = $taxCode;
				$prow['rowValue']['rowNet']['value'] = $rowTotal;
				$prow['rowValue']['rowTax']['value'] = $rowTax;
			}
		}
		return $prow;
	}
	
	public function getTaxPercentForExtraFee($order)
	{
		$taxPercent = 0 ;
		if($this->_isProductTaxable and $this->_extraFeeTaxPercent > 0 )
		{
			$taxPercent = $this->_extraFeeTaxPercent;
		}
		else
		{
			$items = $order->getAllVisibleItems();
			foreach ($items as $itemId => $item) 
			{
				if ($item->getTaxAmount() > 0 ) 
				{
					$this->_isProductTaxable = true;
					$this->_extraFeeTaxPercent = $item->getTaxPercent();
					$taxPercent = $item->getTaxPercent();
					break;
				}
			}
		}
		return $taxPercent;
	}
	
	public function calculateTaxForExtraFee($order, $fee){
		$taxPercent = $this->getTaxPercentForExtraFee($order);
		$baseFeeAmount = 0;
		$taxAmount = '';
		if($taxPercent > 0 )
		{
			$baseFeeAmount = ( $fee * 100 ) / ( 100 + $taxPercent );
			$taxAmount = $fee - $baseFeeAmount;
			$taxAmount = round($taxAmount,2);
			/*
			$taxAmount = ( $fee * $taxPercent ) / 100 ;
			*/
		}
		return $taxAmount ;
	}

    /* -------------------- Add Free Products in brightpearl if they exist  -------------------- */
	public function postFreeProducts($order, $bpOrderId, $return_type = "")
	{
		$rowArray = [];
		$enablefreeproduct  =  $this->getConfig('bpconfiguration/bp_orderconfig/enablefreeproduct');
		$nominalCode 		=  $this->getConfig('bpconfiguration/bp_orderconfig/freeproduct_nominal');
		$enableShippingCountryCheck =  $this->getConfig('bpconfiguration/bp_orderconfig/enable_shipping_country_check');

		if($enablefreeproduct)
		{
			$reference = $order->getIncrementId();
			$store_id =$order->getStoreId();
			if(!$enableShippingCountryCheck){
				$country_id = 0;
			}else{
				$country_id = $order->getShippingAddress()->getCountryId();
			}
			$items = $order->getAllVisibleItems();
			foreach ($items as $itemId => $item) 
			{
				$sku = $item->getSku();
				$freeSku = $this->_freeproductFactory->getProductFreeSku($store_id, $country_id, $sku);
				if($freeSku)
				{
					$productName = 'Free Adapter';
					$freeProduct = $this->getProductBySKu($freeSku);
					if($freeProduct){
						$productName = $freeProduct->getName();
					}
					$freeProductqty = $item->getQtyOrdered();
					$rowNet 		= '0.00';
					$rowTax 		= '0.00';
					
					$bpRow = [];
					$bpRow['productName']                     = $productName;
					$bpRow['quantity']['magnitude']           = $freeProductqty;
					$bpRow['rowValue']['taxCode']             = $this->getNonTaxable();
					$bpRow['rowValue']['rowNet']['value']     = $rowNet;
					$bpRow['rowValue']['rowTax']['value']     = $rowTax ;
					if ($nominalCode != "" && $nominalCode != null) {
						$bpRow['nominalCode'] = trim($nominalCode);
					}
					
 					$bpRow = $this->refineBpRow($order, $bpRow); // ------- Replace the Tax class code with T0 
					
					if($return_type == 'rowArray'){
						$rowArray[] = $bpRow; // --------- prepare array for return the free product array for new method 
						continue;
					}
  					
					$log_data = 'Free Product '. json_encode($bpRow);
					$this->recordLog("Order", $log_data, $reference);
					$responses = $this->_api->postOrderRow($bpOrderId, $bpRow);  //  call add order row api
					$salesOrderRowId = $responses['response'];
					if (isset($responses['response']) and $salesOrderRowId > 0) {
						$log_data = 'Free Product row added successfully.';      //  Insert Log
						$this->recordLog("Order", $log_data, $reference);
					} else {
						$log_data = 'Unable to post Free Product row';      //  Insert Log
						$this->recordLog("Order", $log_data, $reference);
					}
				}
			}
 		}
		
		if($return_type == 'rowArray'){
			return $rowArray;
		}
		return true;		
	}
	
	public function getProductBySKu($sku)
    {
		$product = '';
        $productModel = $this->productFactory->create();
        $productId = (int) $productModel->getIdBySku($sku);
		if($productId)
		{
			 $product = $productModel->load($productId);
		}
        return $product;
    }

	public function getSpecialSkuOrderStatus($order) {
		$status = '';
		$flagorderenable  	=  $this->getConfig('bpconfiguration/flagorder/enable');
		if($flagorderenable)
		{
			$flagskus =  $this->getConfig('bpconfiguration/flagorder/flagskus');
 			if($flagskus)
			{
				$flagskusArray = explode("#",$flagskus);
				$items = $order->getAllVisibleItems();
				foreach ($items as $itemId => $item) 
				{
					$itemSku  = $item->getSku();	
					if (in_array($itemSku, $flagskusArray))
					{
						$status = $this->getConfig('bpconfiguration/flagorder/flagsorderstatus');
						break;
					}
				}
			}
		}
		return $status;
	}

    public function CreateOrderNew($orderId, $incrementId)
    {
        $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
        $billingAdress = $order->getBillingAddress()->getData();
		if($order->getShippingAddress()) {
			$shippingAdress = $order->getShippingAddress()->getData();
		}else{
			$shippingAdress = $order->getBillingAddress()->getData();
			$order->setShippingAddress($order->getBillingAddress());
        }
        $salesreportid = '';
        if ($orderId) {
            $salesreportid = $this->setFirstSalesReportData('mgt_order_id', $incrementId);
        } else {
            $log_data = json_encode("Order Id does not exits.");
            $this->recordLog($cat = "Order", $log_data, $title = "Order not exits");
        }
        /*If Not Shipping Address set billing as shipping address*/
        if (!$shippingAdress) {
            $shippingAdress = $order->getBillingAddress()->getData();
            $error = "There is no shipping address with this order id = ".$incrementId;
            $log_data = json_encode($error);
            $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
        }
        /*Guest Customer*/
        if (!$order->getCustomerId()) {
			$billingAdress['website_id'] = $order->getStoreId();
            $customerInfo = $this->getBpCustomerInfoArray($billingAdress, $telephone = "");
            $email = $billingAdress['email'];
            /*For Guest Customer Logs*/
            if ($salesreportid) {
                $this->updateSalesReportData($salesreportid, 'mgt_customer_id', $value = 0);
            }
        } else {
            /*Register Customer*/
            $customerId = $order->getCustomerId();
            $customerFactory = $this->_objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
            $customer = $customerFactory->load($customerId);
            $customer = $customer->getData();
            $telephone = '';
            if (array_key_exists('telephone', $billingAdress)) {
                $telephone = $billingAdress['telephone'];
            }
            $customerInfo = $this->getBpCustomerInfoArray($customer, $telephone);
            $customerInfo['financialDetails']['priceListId'] = $this->getMappedPricelist($order);
            $email = $customer['email'];
            if ($salesreportid) {
                $this->updateSalesReportData($salesreportid, 'mgt_customer_id', $customerId);
            }
        }
        $Bil_Address = $this->getAddressArray($billingAdress, $order->getBillingAddress());
        $Ship_Address = $this->getAddressArray($shippingAdress, $order->getShippingAddress());
        
		if ($this->_api->authorisationToken) 
		{
            $res = $this->_api->searchCustomerByEmail($email, $return_type = "object") ;
            $result = [];
            $resArray =  json_decode(json_encode($res), true);
            if (array_key_exists('response', $resArray)) {
                $responseArray = $resArray['response'];
                if (array_key_exists('results', $responseArray)) {
                    $result = $res->response->results;
                }
            }
            //$log_data = "Check Customer Exits".json_encode($res);
            //$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
            usleep(1000000);
            $flagCustomerExist = false;
            $brightpearlUserId = '';
            if (count($result)) {
                foreach ($result as $_result) {
                    $response = $this->_api->getCustomerById($_result[0]);
                    $res = $response['response'];
                    if (isset($res['errors'])) {
                        $log_data = "get customer from bp".json_encode($res['errors']);
                        $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                    } else {
                        $log_data = "get customer from bp".json_encode($res);
                        $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                    }
                    if (strpos(json_encode($res), 'isSupplier') > 0 && strpos(json_encode($res), 'isStaff') > 0) {
                        if ($res[0]['relationshipToAccount']['isStaff'] != 1 && $res[0]['relationshipToAccount']['isSupplier'] != 1) {
                            $flagCustomerExist = true;
                            $brightpearlUserId = $res[0]['contactId'];
                            $log_data = "BP customer already exits with id".json_encode($brightpearlUserId);
                            $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                            break;
                        }
                    }
                }
            }
            /*Customer does not exits*/
            if ($flagCustomerExist == false) {
                $billId = '';
                $response     = $this->_api->postCustomerAddress($Bil_Address);
                if (array_key_exists('response', $response)) {
                    $billId = $response['response'];
                }
                /* ---------- add retry code if not received response --------*/
                if (!$billId && $billId == "") {
                    $response     = $this->_api->postCustomerAddress($Bil_Address);
                    if (array_key_exists('response', $response)) {
                        $billId = $response['response'];
                    }
                }
                /* ---------- add retry code if not received response --------*/
                /*Error log for customer Address*/
                $log_data = "Customer address response ".json_encode($response);
                $this->recordLog("Order", $log_data, $incrementId);
                /*Check if billing and shipping address are same*/
                $delId = '';
                if (array_diff($Bil_Address, $Ship_Address)) {
                    $response = $this->_api->postCustomerAddress($Ship_Address);
                    if (array_key_exists('response', $response)) {
                        $delId = $response['response'];
                    }
                    /* ---------- add retry code if not received response --------*/
                    if (!$delId && $delId == "") {
                        $response = $this->_api->postCustomerAddress($Ship_Address);
                        if (array_key_exists('response', $response)) {
                            $delId = $response['response'];
                        }
                    }
                    /* ---------- add retry code if not received response --------*/
                } else {
                    $delId = $billId;
                }
                /*Log for Customer bill id*/
                if (!$billId && $billId == "") {
                    $log_data = ' Add Billing API Call Fail ';
                    $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                } else {
                    $log_data = 'BP Billing address id '.$billId;
                    $this->recordLog($cat = "Order", $log_data, $title = $incrementId);
                }
				/* Update address id in customer info array */
				$customerInfo['postAddressIds'] = [ 'DEF' => $billId, 'BIL' => $billId, 'DEL' => $delId];

				/*POST Customer to brightpearl*/
				$log_data = "Post customer to BP request ".json_encode($customerInfo);
				$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
													
				/*Post Customer to Bright Pearls*/
				$CreateUserResponse = $this->_api->postCustomer($customerInfo);
				if (array_key_exists('response', $CreateUserResponse)) {
					$brightpearlUserId = $CreateUserResponse['response'];
					$log_data = "Post customer to BP response ".json_encode($CreateUserResponse);
					$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
				} else {
				/*Log get Customer BP ID*/
					$log_data = "Customer BP id ".json_encode($CreateUserResponse['errors']);
					$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
				}
            }

			/*Set Customer for BP IN logs*/
            if ($salesreportid) {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_customer_id', $brightpearlUserId);
            }

			/*Log table*/
			$bpcustlog = $this->ChecklogData($salesreportid, 'bp_customer_id');
            if ($bpcustlog == 'true') {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_customer_status', $value = "success");
            } else {
                $brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_customer_status', $value = "error");
            }

		/* ------ Send data to create Order ------- */
			$bporderId = $this->getBpOrderDataNew($order, $brightpearlUserId, $email, $shippingAdress);
			if ($bporderId) 
			{
				$log_data = "BP Order id ".json_encode($bporderId);
				$this->recordLog("Order", $log_data, $incrementId);
			// ------------- Update Brightpearl order id in Order PO Relation table ------------
				$oprf = $this->_objectManager->create('\Bsitc\Brightpearl\Model\BporderporelationFactory');
				$condition = [ 'order_id'=>$order->getId() ];
				$oprf->updateOrderPoRelationColumn('bp_order_id', $bporderId, $condition);
				$oprf->updateOrderPoRelationColumn('state', '1', $condition);
			// ------------- Update Brightpearl order id in Order PO Relation table ------------
			
			/* ------ Set Customer for BP IN logs ------ */
				if ($salesreportid) {
					$brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_order_id', $bporderId);
					$custom_order =  $this->_order->load($orderId);
					$custom_order->addStatusHistoryComment('This Brightpearl Order id - '.$bporderId);
					$custom_order->save();
				}
				$bporderstatus = $this->ChecklogData($salesreportid, 'bp_order_status');
				if ($bporderstatus == 'true') {
					$brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_order_status', $value = "success");
				} else {
					$brightpearluser = $this->updateSalesReportData($salesreportid, 'bp_order_status', $value = "error");
				}
				usleep(1000000);
			/* -------- Set Inventory to Order Rows ----------*/
				$data=[];
				$data = $this->_bpReservationProductArray;
 				if ($data)
				{
					$res = $this->postInventoryToOrderRow($order, $bporderId,$data);
					if($res){
						$this->updateSalesReportData($salesreportid, 'bp_inventory_status', $value = "success");
					}else{
						$this->updateSalesReportData($salesreportid, 'bp_inventory_status', $value = "error");
					}
 				}
			/* ----- POS Staff Name ----- */
				$posid = $order->getPosStaffId(); 
				if ($posid) {
					$this->postPosStaffName($order, $bporderId);
				}
				usleep(1000000);
			/* ----- Check If Order are paid and has Invoiced then send payment to Brightpearl ----- */
				$bpPaymentPaid  = '';
				if (($order->getBaseTotalDue() == 0) && ($order->getInvoiceCollection()->getSize() > 0)) {
					$Bpapi = $this->_api;
					$response_data = $this->postCustomerPayment($Bpapi, $order, $bporderId, $brightpearlUserId);
					$paymentassign = json_decode($response_data);
					$bpPaymentPaid = $paymentassign->response;
					if ($paymentassign->response) {
						$log_data = "Order Payment Response ".json_encode($paymentassign->response);
						$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
						$this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "success");
					} else {
						$log_data = "Order Payment Response ".json_encode($paymentassign);
						$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
						$this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "error");
					}
				} else {
					$bpPaymentPaid  = 'notpaid';
					$log_data = "Order Payment Response - payment are not paid yet";
					$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
					$this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "not paid");
				}
			/* --------- post store credit or customer balance as  a payment ---------------*/
				if ($order->getCustomerBalanceAmount() > 0) {
					$Bpapi = $this->_api;
					$response_data = $this->postStoreCreditPayment($Bpapi, $order, $bporderId, $brightpearlUserId);
					$paymentassign = json_decode($response_data);
					$bpPaymentPaid = $paymentassign->response;
					if ($paymentassign->response) {
						$log_data = "Order store credit Payment Response ".json_encode($paymentassign->response);
						$this->recordLog("Order", $log_data, $incrementId);
						$this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "success");
					} else {
						$log_data = "Order store credit Payment Response ".json_encode($paymentassign);
						$this->recordLog("Order", $log_data, $incrementId);
						$this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "error-Store-credit");
					}
				}
			/* --------- post  Gift Cards Payment as a payment ---------------*/
				if ($order->getGiftCardsAmount() > 0) {
					$Bpapi = $this->_api;
					$response_data = $this->postGiftCardsPayment($Bpapi, $order, $bporderId, $brightpearlUserId);
					$paymentassign = json_decode($response_data);
					$bpPaymentPaid = $paymentassign->response;
					if ($paymentassign->response) {
						$log_data = "Order Gift Cards Payment Response ".json_encode($paymentassign->response);
						$this->recordLog("Order", $log_data, $incrementId);
						$this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "success");
					} else {
						 $log_data = "Order Gift Cards Payment Response ".json_encode($paymentassign);
						$this->recordLog("Order", $log_data, $incrementId);
						$this->updateSalesReportData($salesreportid, 'bp_payment_status', $value = "error-Store-credit");
					}
				}
				/* --------- post  Gift Cards Payment as a payment  ---------------*/
				
					
			/* --------- Check If Order are POS order then generate Fullfillments --------- */
				//$goodsoutnote  = $this->PostGoodsOutNote($Bpapi, $order, $bporderId, $brightpearlUserId);
				//$paymentassign = json_decode($response_data);
				$pos = $order->getPosLocationId();
				$shippingmethod = $order->getShippingMethod();
				$shippingMethodId = $this->getShippingMapping($order);
				$collectfrom_store = $order->getWarehouseStore();
				/*Assign error msg for status*/
				$successresp = $bpPaymentPaid;

				if (($pos) || ($collectfrom_store)) {
					// ---------- skip pos order if order in processing state due to home delivery in pos
					if (($pos) && ($order->getState() == 'processing')) {
						return $successresp;
					}
					if ($data and count($data['reservationProductArray']) >0) {
						$products = $data['reservationProductArray'];
						$prodata = '[ '.implode(",", $products).' ]';
						$warehouseid = $this->getBpWarehouse($order);
						$goodsdata = '{"warehouses": [{"releaseDate": '.date("Y-m-d").',"warehouseId": '.$warehouseid.',"transfer": false,"products": '.$prodata.'}],"priority": false,"shippingMethodId": '.$shippingMethodId.',"labelUri": ""}';
						$this->recordLog("Order", 'Order Goods Out note POST Data'.$goodsdata, $incrementId);
						$goodsoutnote  = $this->_api->PostGoodsOutNote($bporderId, $goodsdata);
						$log_data = "Order Goods Out note Response ".$goodsoutnote;
						$this->recordLog("Order", $log_data, $incrementId);
						$successresp = json_decode($goodsoutnote, true);
						if (array_key_exists("response", $successresp)) {
							$response = $successresp['response'];
							$this->postShipmentEvent($response, $incrementId);
						}
					}
				} else {
					$successresp = $bpPaymentPaid;
				}
			/* ------------------------------------------------------------ */
				return $successresp;								
			} else {
				$log_data = "BP Order id does not exits.";
				$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
			}		
        } else {
			$log_data = "API Authentication fails";
			$this->recordLog($cat = "Order", $log_data, $title = $incrementId);
        }
    }

    /*Create Order and Pass the data in Orders*/
    public function getBpOrderDataNew($order, $brightpearlUserId, $email, $shippingAdress)
    {
		$shippingmethod = $order->getShippingMethod(); /*Magento Shipping Methods*/
		$shippingMethodId = $this->getShippingMapping($order); /*Fetch Shipping Method from mapping or config*/
		$customergroupid = $order->getCustomerGroupId(); /*Magento Customer Group Id*/
		$leadsourceid = $this->getBpleadsource($customergroupid); /*Fetch Lead Source from mapping or config*/
		$warehouseid = $this->getBpWarehouse($order);
		$channelId = $this->getChannel();
		$delday = $this->getShippingMappingDeliveryDays($order);  # set default id Delivery in Days
		$deldate = date('Y-m-d h:m:s', strtotime($order->getCreatedAt() . '+' . $delday . 'days'));
		$deliveryDate = date(DATE_ISO8601, strtotime($deldate));
		$pos = $order->getPosLocationId(); /*Check If order types for Magento or POS*/
		/* ItemTypeInfo :  1 => Pre Order, 2 => Made To Order , 3 => Print To Order , 4 => Trade Order , 5 => Bespok Order */
        if ($pos) {
            $order_status = $this->getPosOrderStatus();
            $channelId     = $this->getPosChannel();
        } elseif ($order->getItemTypeInfo() == 1) {
            $order_status         	= $this->getPreOrderStatus(); // 1 => Pre Order
            $deldate            	= $this->getOrderDeliveryDate($order, $delday, 'pre_order');
            $deliveryDate        	= date(DATE_ISO8601, strtotime($deldate));
        } elseif ($order->getItemTypeInfo() == 2) {
            $order_status       	= $this->getMtoStatus();        // 2 => Made Order
            $deldate            	= $this->getMtoDeliveryDate($order);
            $deliveryDate        	= date(DATE_ISO8601, strtotime($deldate));
        } elseif ($order->getItemTypeInfo() == 3) {
            $order_status        	= $this->getPtoStatus();        // 3 => Print Order
            $deldate            	= $this->getPtoDeliveryDate($order);
            $deliveryDate        	= date(DATE_ISO8601, strtotime($deldate));
        } elseif ($order->getItemTypeInfo() == 4) {
            $order_status     		= $this->getTradeOrderStatus();  // 4 => Trade Order
            $deldate         		= $this->getOrderDeliveryDate($order, $delday, 'trade_order');
            $deliveryDate    		= date(DATE_ISO8601, strtotime($deldate));
			$channelId     			= $this->getTradeChannel();
        } elseif ($order->getItemTypeInfo() == 5) {
            $order_status    		= $this->getBespokOrderStatus(); // 5 => Bespoke Order
            $deldate        		= $this->getOrderDeliveryDate($order, $delday, 'bespoke_order');
            $deliveryDate    		= date(DATE_ISO8601, strtotime($deldate));
        } elseif ($order->getItemTypeInfo() == 6) {
            $order_status     		= $this->getTradeOrderStatus();  // 6 => Trade Order
            $deldate         		= $this->getOrderDeliveryDate($order, $delday, 'trade_order');
            $deliveryDate    		= date(DATE_ISO8601, strtotime($deldate));
			$channelId     			= $this->getTradeChannel();		
		} else {
            $order_status     		= $this->getMgtOrderStatus();
            $deldate         		= $this->getOrderDeliveryDate($order, $delday, 'normal_order');
            $deliveryDate   		= date(DATE_ISO8601, strtotime($deldate));
        }
		
		$specailOrderStatus = $this->getSpecialSkuOrderStatus($order);
		if($specailOrderStatus and trim($specailOrderStatus) != "" )
		{
			$log_data = 'Order Status Id '.$order_status.' replace by Special Order status id '.$specailOrderStatus;
			$this->recordLog("Order", $log_data, $order->getIncrementId());
			$order_status  = $specailOrderStatus;
		}

		/*Get Mapping for shipping methods and Data*/
		$reference          = $order->getIncrementId();
		$placedOn           = date(DATE_ISO8601, strtotime($order->getCreatedAt()));
		$currency           = $order->getOrderCurrencyCode();
		$priceListId        = $this->getMappedPricelist($order);
		$addressFullName    = $shippingAdress['firstname'] . ' ' . $shippingAdress['lastname'];
		$companyName        = $shippingAdress['company'];
		$telephone          = $shippingAdress['telephone'];
		$mobileTelephone    = '';
		$orderStatusId        = $order_status;
	//  -------- prepare array to post data on BP -----------
		$step1 = [];
 		$step1['customer']['id'] = $brightpearlUserId;
		$step1['ref'] = $reference;
		$step1['placedOn'] = $placedOn;
		$step1['statusId'] = $orderStatusId;
		$step1['warehouseId'] = $warehouseid;
		$step1['channelId'] = $channelId;
		$step1['leadSourceId'] = $leadsourceid;
		$step1['priceListId'] = $priceListId;
		$step1['currency']['code'] = $currency;
		$step1['currency']['fixedExchangeRate'] = 'true';
		$step1['delivery']['date'] = $deliveryDate;
		$step1['delivery']['address'] = $this->getAddressArray($shippingAdress, $order->getShippingAddress());
		$step1['delivery']['address']['addressFullName'] = $addressFullName;
		$step1['delivery']['address']['companyName'] = $companyName;
		$step1['delivery']['address']['telephone'] = $telephone;
		$step1['delivery']['address']['mobileTelephone'] = $mobileTelephone;
		$step1['delivery']['address']['email'] = $email;
		$step1['delivery']['shippingMethodId'] = $shippingMethodId;
		//$step1['currency']['exchangeRate'] = $this->getMgtExchangeRate($order);
		//$step1['taxDate'] = '';
		//$step1['parentId'] = '';
		//$step1['staffOwnerId'] = '';
		//$step1['projectId'] = '';
		//$step1['externalRef'] = '';
		//$step1['installedIntegrationInstanceId'] = '';
		//$step1['teamId'] = '';
		//$step1['priceModeCode'] = 'INC';   //  'INC' or 'EXC'. Allows you to override the price list's price mode.
	/* ---- Add Row to Orders(Add product to Order) ------ */
 		$bpRows = [];
        $reservationProductArray = [];
		$productAttributeSetsOption = $this->getProductAttributeSets();
		$productRepository = $this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
        // $items = $order->getAllVisibleItems();
		$items = $this->getFinalOrderItemsList($order);
		$this->_swedishArray = $this->getSwedishItemsArray($order);
		
		$enableskureplacement = $this->getConfig('bpconfiguration/bp_orderconfig/enableskureplacement');
		$enablesplitsku = $this->getConfig('bpconfiguration/bp_orderconfig/enablesplitsku');

		
		foreach ($items as $itemId => $item) 
		{
			$productid = $item['product_id'];
			$product = $productRepository->getById($productid);
			$taxclassid = $product->getTaxClassId();
			$final_sku = $item->getSku();
			if($enableskureplacement) {
				$final_sku = trim( $this->_skureplacementFactory->getReplacedSku($order->getStoreId(), $order->getShippingAddress()->getCountryId(), $item->getSku()) );
				$this->recordLog("Final SKU After Replace",  $item->getSku().' => '.$final_sku, $reference);
			}			
            $discount_tax_compensation_amount =  0;
            if ($item->getDiscountTaxCompensationAmount()  > 0) {
                $discount_tax_compensation_amount = $item->getDiscountTaxCompensationAmount();
            }
			/* ----------------- builder product setup -----------------*/
			if($item->getItemTypeInfo() == 5 and $order->getItemTypeInfo() == 5 )
			{
				$productAttributeSetId = $item->getProduct()->getAttributeSetId();
				$this->recordLog("productAttributeSetId",  $productAttributeSetId, $order->getIncrementId());
				$prfix = '';
				if($productAttributeSetsOption[$productAttributeSetId] == 'Curtains'){
					$prfix = $this->getConfig('bpconfiguration/builders/curtain_prfix');
				}
				if($productAttributeSetsOption[$productAttributeSetId] == 'Blinds'){ 
					$prfix = $this->getConfig('bpconfiguration/builders/blind_prfix');
				}
				if($productAttributeSetsOption[$productAttributeSetId] == 'Furniture'){
					$prfix = $this->getConfig('bpconfiguration/builders/furniture_prfix');
				}
				if($prfix){
					$final_sku = $prfix.'-'.$final_sku; 
					$final_sku =  str_replace("&","%26",$final_sku);
				}
				$pid = $this->_api->getProductIDFromSku($final_sku);  //  Check Product exist on Brightpearl   
				if(!$pid) /* ----------- if product not found that cehck with orginal sku */
				{
					$this->recordLog("Order row", 'Bespoke SKU NOt found '.$final_sku,  $reference);
					$final_sku = $item->getSku();
					$final_sku =  str_replace("&","%26",$final_sku);
					$pid = $this->_api->getProductIDFromSku($final_sku);  //  Check Product exist on Brightpearl   
				}
				$final_name = $this->getItemFinalName($item);
			}
			else
			{
				$final_name = $item->getName();
				$final_sku =  str_replace("&","%26",$item->getSku());
				$pid = $this->_api->getProductIDFromSku($final_sku);  //  Check Product exist on Brightpearl   
			}
			/* ----------------- builder product setup -----------------*/
			
			$nominalcode = $this->getNominalCode();
			$taxCode = $this->getNonTaxable();  # N for Non Taxable
			$rowTax = '0.00';
			if ($item->getTaxAmount()) {
				$taxCode = $this->getTaxable($taxclassid); #  T For Taxable
				$rowTax = number_format($item->getTaxAmount(), '2', '.', '') ;
			}
			$rowTax = $rowTax + $discount_tax_compensation_amount;
			$rowTotal = number_format($item->getRowTotal(), '2', '.', '');
			$quantity = (int)$item->getQtyOrdered();
			$prow = [];
            if ($pid) 
			{
                $productId = (int) $pid;
                /* ---------- get product data from brightpearl ---------*/
                $bpProduct = [];
                $bpProductData = $this->_api->getProductById($productId);
                if (array_key_exists('response', $bpProductData)) {
                    if (array_key_exists('0', $bpProductData['response'])) {
                        $bpProduct = $bpProductData['response'][0];
                    }
                }
                /* ---------- get product data from brightpearl ---------*/
                if (array_key_exists('nominalCodeSales', $bpProduct) and $bpProduct['nominalCodeSales']!= "") {
                     $nominalcode = $bpProduct['nominalCodeSales'];
                }
 				$prow['productId'] = $productId;
				
				# ---- Prepare array  for inventory reservation --------
				$reservationProductArray[$productId] = $productId;
              } else {
				$prow['name'] = 'Missing SKU : ' . $final_sku . 'Missing Product : ' . $item->getProduct()->getName();
            }
			$prow['quantity'] = $quantity;
			$prow['taxCode'] = $taxCode;
			$prow['net'] = $rowTotal;
			$prow['tax'] = $rowTax;
			$prow['nominalCode'] = $nominalcode;
			if($item->getItemTypeInfo() == 5 ){
				$prow['name'] = $final_name; // 5 => bespoke item curtain or blind or furniture
			}
			$prow = $this->refineBpRowNew($order, $prow); // ------- Replace the Tax class code with T0 

			// ------- Refine Swedish Row 
			if( $this->isSwedishProduct( $order, $item->getProduct() ) )
			{
				$prow = $this->refineSwedishRow($order, $prow ,$item);  
				$this->recordLog("Order", json_encode($prow,true),  'refineSwedishRow');
			}
			// ------- Refine Swedish Row 
			
			// ------- Split SKU  Row  -------
			if( $enablesplitsku )
			{
				$splitSkuRows = [];
				$splitSkuRows = $this->refineSplitSkuRow($order, $prow, $final_sku, $item->getProduct()->getName() );  
				$this->recordLog("Order", json_encode($splitSkuRows,true),  'refineSplitSkuRow');
				if(count($splitSkuRows) > 0 ) 
				{
					foreach($splitSkuRows as $splitSkuRow){
						$step1['rows'][] = $splitSkuRow;
					}
					continue;
				}
			}
			// ------- Split SKU  Row  -------
 			
			$step1['rows'][] = $prow;
        }
		
		/* ---------- set */
		$this->_bpReservationProductArray = $reservationProductArray;
		
		
		$bporderId = '';
		/* ------------------ Reduce Coupon Code Amount from Order ------------------ */
		$couponCodeDiscount = $this->CouponCodeDiscount($order, $bporderId, 'rowArray');
		if($couponCodeDiscount)
		{
			$step1['rows'][] = $this->prepareRows($couponCodeDiscount);
		}
		/* ------------------ Order Shipping charges ------------------ */
		$orderShippingCharge = $this->OrderShippingCharge($order, $bporderId, 'rowArray');
		if($orderShippingCharge)
		{
			// $step1['rows'][] = $this->prepareRows($orderShippingCharge);
			$shippingRow = $this->prepareRows($orderShippingCharge);
			$shippingRow = $this->refineSwedishOtherRow($order, $shippingRow, 'shipping');
			$step1['rows'][] = $shippingRow;
			
		}
		/* ------------------ Oversize Handling Fee ------------------ */
		$oversizeHandlingFee = $this->postOversizeHandlingFee($order, $bporderId, 'rowArray');
		if($oversizeHandlingFee)
		{
			$step1['rows'][] = $this->prepareRows($oversizeHandlingFee);
		}
		/* ------------------ Extra Shipping Fee ------------------ */
		$extraShippingFee = $this->postExtraShippingFee($order, $bporderId, 'rowArray');
		if($extraShippingFee)
		{
			$step1['rows'][] = $this->prepareRows($extraShippingFee);
		}
		/* ------------------ add rounding fix row Order ------------------ */
		$addRoundingDifferencAmount = $this->addRoundingDifferencAmountRow($order, $bporderId, 'rowArray');
		if($addRoundingDifferencAmount)
		{
			$step1['rows'][] = $this->prepareRows($addRoundingDifferencAmount);
		}
		
		/* ------------------ add free product if configure ------------------ */
		$freeProductArray = $this->postFreeProducts($order, $bporderId, 'rowArray');
		if($freeProductArray and count($freeProductArray) > 0 )
		{
			foreach($freeProductArray as $freeProduct) {
				$step1['rows'][] = $this->prepareRows($freeProduct);
			}
		}
 		/* ------------------ Post Order ------------------ */

		$this->recordLog("Order", json_encode($step1), $reference);
		$response = $this->_api->postOrderWithItems($step1);
		$orderId = '';
        if (array_key_exists('errors', $response)) {
             $this->recordLog($cat = "Order", json_encode($response), $reference);
        } else {
            $orderId = $response['response'];
        }
		return $orderId;
     }
	
	public function prepareRows($row)
	{
		$tmpRow = [];
		$tmpRow['name'] = $row['productName'];
		$tmpRow['quantity'] = $row['quantity']['magnitude'];
		$tmpRow['taxCode'] = $row['rowValue']['taxCode'];
		$tmpRow['net'] = $row['rowValue']['rowNet']['value'];
		$tmpRow['tax'] = $row['rowValue']['rowTax']['value'];
		$tmpRow['nominalCode'] = $row['nominalCode'];
		return $tmpRow;	
	}

 	public function refineBpRowNew($order, $prow)
	{
		$enablezerotax  =  $this->getConfig('bpconfiguration/bp_orderconfig/enablezerotax');
		$zerotaxcountry  =  $this->getConfig('bpconfiguration/bp_orderconfig/zerotaxcountry');
 		if($enablezerotax)
		{
			$configureCountry = explode(",",$zerotaxcountry);
 			$shippingCountry = $order->getShippingAddress()->getCountryId();
 			if (in_array($shippingCountry, $configureCountry)) 
			{
				$taxCode = $this->getNonTaxable();  # N for Non Taxable
				$rowTax = '0.00';
				$rowTotal = $prow['net'] +  $prow['tax'];
 				$prow['taxCode'] = $taxCode;
				$prow['net'] = $rowTotal;
				$prow['tax'] = $rowTax;
			}
		}
		return $prow;
	}
	
	public function postInventoryToOrderRow($order, $bporderId, $data)
	{
		$result = false;
		$getBpOrder = $this->_api->orderById($bporderId);
		if (is_array($getBpOrder)) 
		{
			if( is_array($getBpOrder) and  array_key_exists("response", $getBpOrder) )
			{
				$postedOrderDetail = $getBpOrder['response'];
				if (array_key_exists("0", $postedOrderDetail)) 
				{
					$orderRows = $postedOrderDetail['0']['orderRows'];
					$soRowId = [];
					$finalReservation = [];
					foreach($orderRows as $salesOrderRowId=>$row)
					{
 						$productId = $row['productId'];
 						$quantity = $row['quantity']['magnitude'];
 						if (in_array($productId, $data))
						{
							$soRowId[] = $salesOrderRowId;
							$finalReservation[] = '{productId:"' . $productId . '",salesOrderRowId:"' . $salesOrderRowId . '",quantity:"' . $quantity . '"}';
						}
					}
  					$res = $this->setInventoryToOrderRow($order, $order->getIncrementId(), $bporderId, $soRowId, $finalReservation);
					if (!isset($res->errors)) {
							$result = true;
					}
				}
			}
		}		
		return $result;
	}

	public function refineSwedishDiscountRow($order, $prow, $type = '')
	{
		$sv_enable = $this->getConfig('bpconfiguration/bp_orderconfig/sv_enable');
		if($sv_enable)
		{
			$swedishArray = $this->_swedishArray;
			if (is_array($swedishArray))
			{
				if (array_key_exists("swedish_items",$swedishArray))
				{
					$swedish_items = $swedishArray['swedish_items'];
					$items = $this->getFinalOrderItemsList($order);
					foreach($items as $item)
					{
						if( count($swedish_items) > 0  and array_key_exists($item->getId() ,$swedish_items) )
						{
							$configureTaxPercents = $this->_swedishVatInfo;
							$shipping_country = $swedish_items[$item->getId()]['shipping_country'];
							$swedish_category = $swedish_items[$item->getId()]['swedish_category'];
							$svPercent = $configureTaxPercents[$shipping_country][$swedish_category]['tax_percent'];
							$svTaxclass = $configureTaxPercents[$shipping_country][$swedish_category]['tax_class'];
							if( $svPercent > $this->_svPercent  ) {  //-------- Calculate biggest percent for shipping -------
								$this->_svPercent = $svPercent;
								$this->_svTaxclass = $svTaxclass;
							}
							if($type == 'oldapi')
							{
								$rowTotal = $prow['rowValue']['rowNet']['value'];
								$rowTax = $prow['rowValue']['rowTax']['value'];					
								$total = $rowTotal + $rowTax;
								$rowTotal = $total / ( ( 100 + $svPercent ) / 100 ) ;
								$rowTax = $total - $rowTotal;
								$taxCode = $svTaxclass ;
								$prow['rowValue']['taxCode'] = $taxCode;
								$prow['rowValue']['rowNet']['value'] = round($rowTotal,4);
								$prow['rowValue']['rowTax']['value'] = round($rowTax,4);
							} 
							else 
							{
								$new_prow = [];
								$new_prow['productName'] = $prow['productName'];
								$new_prow['quantity']['magnitude'] = $prow['quantity']['magnitude']; 
								$new_prow['nominalCode'] = $prow['nominalCode'];
								
								$rowTotal = $prow['rowValue']['rowNet']['value'];
								$rowTax = $prow['rowValue']['rowTax']['value'];
								$total = $rowTotal + $rowTax;
								$rowTotal = $total / ( ( 100 + $svPercent ) / 100 ) ;
								$rowTax = $total - $rowTotal;
								$taxCode = $svTaxclass ;
								
								$new_prow['rowValue']['taxCode']= $taxCode;
								$new_prow['rowValue']['rowNet']['value'] = round($rowTotal,4);
								$new_prow['rowValue']['rowTax']['value'] = round($rowTax,4);
								$prow = $new_prow;
							}
							break;
						}						
					}
				}
			}
		}
		return $prow;
	}
	
	public function refineSwedishRow($order, $prow, $item, $type = '')
	{
		$sv_enable = $this->getConfig('bpconfiguration/bp_orderconfig/sv_enable');
		if($sv_enable)
		{
			$swedishArray = $this->_swedishArray;
			if (is_array($swedishArray))
			{
				if (array_key_exists("swedish_items",$swedishArray))
				{
					$swedish_items = $swedishArray['swedish_items'];
					if( count($swedish_items) > 0  and array_key_exists($item->getId() ,$swedish_items) )
					{
						$configureTaxPercents = $this->_swedishVatInfo;
						$shipping_country = $swedish_items[$item->getId()]['shipping_country'];
						$swedish_category = $swedish_items[$item->getId()]['swedish_category'];
						$svPercent = $configureTaxPercents[$shipping_country][$swedish_category]['tax_percent'];
						$svTaxclass = $configureTaxPercents[$shipping_country][$swedish_category]['tax_class'];
						if( $svPercent > $this->_svPercent  ) {  //-------- Calculate biggest percent for shipping -------
							$this->_svPercent = $svPercent;
							$this->_svTaxclass = $svTaxclass;
						}
						if($type == 'oldapi')
						{
							$rowTotal = $prow['rowValue']['rowNet']['value'];
							$rowTax = $prow['rowValue']['rowTax']['value'];					
							$total = $rowTotal + $rowTax;
							$rowTotal = $total / ( ( 100 + $svPercent ) / 100 ) ;
							$rowTax = $total - $rowTotal;
							$taxCode = $svTaxclass ;
							$prow['rowValue']['taxCode'] = $taxCode;
							$prow['rowValue']['rowNet']['value'] = round($rowTotal,4);
							$prow['rowValue']['rowTax']['value'] = round($rowTax,4);
						} 
						else 
						{
							$rowTotal = $prow['net'];
							$rowTax = $prow['tax'];
							$total = $rowTotal + $rowTax;
							$rowTotal = $total / ( ( 100 + $svPercent ) / 100 ) ;
							$rowTax = $total - $rowTotal;
							$taxCode = $svTaxclass ;
							$prow['taxCode'] = $taxCode;
							$prow['net'] = round($rowTotal,4);
							$prow['tax'] = round($rowTax,4);
						}
					}				
				}
			}
		}
		return $prow;
	}
	 
	public function refineSwedishOtherRow($order, $prow,  $type = '')
	{
		$sv_enable = $this->getConfig('bpconfiguration/bp_orderconfig/sv_enable');
		if($sv_enable)
		{
			$swedishArray = $this->_swedishArray;
			if (is_array($swedishArray))
			{
				$totalSwedishIems = 0;
				$totalNonSwedishIems = 0;
				if (array_key_exists("swedish_items",$swedishArray)) {
					$totalSwedishIems = count($swedishArray['swedish_items']);
				}
				if (array_key_exists("non_swedish_items",$swedishArray)) {
					$totalNonSwedishIems = count($swedishArray['non_swedish_items']);
				}
				if(  $totalSwedishIems > 0 and $totalSwedishIems >= $totalNonSwedishIems )
				{
					if($type == 'oldapi')
					{
						$rowTotal = $prow['rowValue']['rowNet']['value'];
						$rowTax = $prow['rowValue']['rowTax']['value'];					
						$total = $rowTotal + $rowTax;
						$rowTotal = $total / ( ( 100 + $this->_svPercent ) / 100 ) ;
						$rowTax = $total - $rowTotal;
						$taxCode = $this->_svTaxclass ;
						$prow['rowValue']['taxCode'] = $taxCode;
						$prow['rowValue']['rowNet']['value'] = round($rowTotal,4);
						$prow['rowValue']['rowTax']['value'] = round($rowTax,4);
					}
					else
					{
						$rowTotal = $prow['net'];
						$rowTax = $prow['tax'];
						$total = $rowTotal + $rowTax;
						$rowTotal = $total / ( ( 100 + $this->_svPercent ) / 100 ) ;
						$rowTax = $total - $rowTotal;
						$taxCode = $this->_svTaxclass ;
						$prow['taxCode'] = $taxCode;
						$prow['net'] = round($rowTotal,4);
						$prow['tax'] = round($rowTax,4);
					}
				}
				$this->recordLog("Order", json_encode($prow,true),  'refineSwedishOtherRow');
			}
		}
		return $prow;
	}

	public function isSwedishProduct($order, $_product)
	{
		$result = false;
 		$sv_enable = $this->getConfig('bpconfiguration/bp_orderconfig/sv_enable');
		if($sv_enable)
		{
			$sv_country = $this->_swedish->getConfigureShippingCountryArray();
			$sv_category = $this->_swedish->getConfigureCategoryArray();
			$shippingCountry = $order->getShippingAddress()->getCountryId();
 			if (in_array($shippingCountry, $sv_country)) 
			{
				$productCategoryIds = $_product->getCategoryIds();
				foreach ($sv_category as $swedishCategory) 
				{
					if (in_array($swedishCategory, $productCategoryIds)) {
						 $result = true;
						 break;
					}
				}
 			}
		}
		return $result;
 	}
	 
	public function isProductSwedishCategory($_product)
    {
        $result = false;
		$sv_category = $this->_swedish->getConfigureCategoryArray();
        $productCategoryIds = $_product->getCategoryIds();
        foreach ($sv_category as $swedishCategory) 
		{
            if (in_array($swedishCategory, $productCategoryIds)) {
                 $result = true;
                 break;
            }
        }
        return $result;
    }
	
	public function getProductSwedishCategory($_product)
    {
        $result = '';
		$sv_category = $this->_swedish->getConfigureCategoryArray();
		$exclude_products = $this->_swedish->getExcludeProductsArray(); /* -- use to exclude from Swedish VAT -- */
        $productCategoryIds = $_product->getCategoryIds();
        foreach ($sv_category as $swedishCategory) 
		{
            if ( in_array($swedishCategory, $productCategoryIds)   and !in_array($_product->getSku(), $exclude_products) ) {
                 $result = $swedishCategory;
                 break;
            }
        }
        return $result;
    }
	
	public function getSwedishItemsArray($order)
	{
		$swedish = [];
		$swedish['swedish_items']= [];
		$swedish['non_swedish_items']= [];
		$items = $this->getFinalOrderItemsList($order);
		/* ------------------- Swedish VAT -------------------- */
		$sv_enable = $this->getConfig('bpconfiguration/bp_orderconfig/sv_enable');
		if($sv_enable)
		{
 			$sv_country = $this->_swedish->getConfigureShippingCountryArray();
 			$shippingCountry = $order->getShippingAddress()->getCountryId();
 			if (in_array($shippingCountry, $sv_country)) 
			{
				foreach ($items as $itemId => $item) 
				{
					$swedishCategory = $this->getProductSwedishCategory($item->getProduct());
					if($swedishCategory)
					{
						$swedish['swedish_items'][$item->getId()]['shipping_country'] = $shippingCountry;
						$swedish['swedish_items'][$item->getId()]['swedish_category'] = $swedishCategory;
					}else{
						$swedish['non_swedish_items'][] = $item->getId();
					}
				}
			}
			$this->_swedishVatInfo = $this->_swedish->getSwedishVatInfo();
		}
		$this->recordLog("Order", json_encode($swedish,true),  'swedish');
		return $swedish;
		/* ------------------- Swedish VAT -------------------- */
	}
 
	public function refineSplitSkuRow($order, $prow, $final_sku, $product_name="")
	{
		$skRow = [];  $splitSkuRows = []; $splitedQtyPriceTaxArray = [];
		$splitSkuArray = $this->_splitSkuFactory->getSplitSkuRows($final_sku);
		if( count($splitSkuArray) > 0 )
		{
			$splitedQtyPriceTaxArray = $this->getSplitedQtyPriceTaxData($splitSkuArray, $prow);
			if( count($splitedQtyPriceTaxArray) > 0)
			{
				foreach ($splitSkuArray as $split_sku) 
				{
					$pid = $this->_api->getProductIDFromSku($split_sku);  //  Check Product exist on Brightpearl   
					if ($pid) 
					{
						$productId = (int) $pid;
						/* ---------- get product data from brightpearl ---------*/
						$bpProduct = [];
						$bpProductData = $this->_api->getProductById($productId);
						if (array_key_exists('response', $bpProductData)) {
							if (array_key_exists('0', $bpProductData['response'])) {
								$bpProduct = $bpProductData['response'][0];
							}
						}
						/* ---------- get product data from brightpearl ---------*/
						if (array_key_exists('nominalCodeSales', $bpProduct) and $bpProduct['nominalCodeSales']!= "") {
							 $nominalcode = $bpProduct['nominalCodeSales'];
						}
						$skRow['productId'] = $productId;
						
						# ---- Prepare array  for inventory reservation --------
						$reservationProductArray[$productId] = $productId;
					  } else {
						  if($product_name){
							$skRow['name'] = 'Missing Split SKU : ' . $split_sku;
						  }else{
							$skRow['name'] = 'Missing Split SKU : ' . $split_sku . ' for Product : ' . $product_name;
						  }
						$nominalcode =  $prow['nominalCode'];
					}
					
					$skRow['quantity'] = $splitedQtyPriceTaxArray[$split_sku]['quantity'];
					$skRow['taxCode'] = $prow['taxCode'];
					$skRow['net'] = $splitedQtyPriceTaxArray[$split_sku]['net'];
					$skRow['tax'] = $splitedQtyPriceTaxArray[$split_sku]['tax'];
					$skRow['nominalCode'] = $nominalcode;
					$splitSkuRows[] = $skRow;
				}			
			}
		}
		return $splitSkuRows;

	}
	
	public function getSplitedQtyPriceTaxData($splitSkuArray, $prow)
	{
		$result = [];
		$totalQty = $prow['quantity'];
		$totalNetPrice = $prow['net'];
		$totalTax = $prow['tax'];
		$totalSku = count($splitSkuArray);
		
		if($totalQty >= $totalSku)
		{		
			$qtyRemainder  = 0; $netPriceRemainder  = 0; $taxRemainder  = 0;
			
			$dividedQty		= floor( $totalQty / $totalSku );
			$qtyRemainder	= $totalQty % $totalSku;
			
			$dividedNet		=  $totalNetPrice / $totalQty  ;
			$dividedNet		=  round($dividedNet , 4);
			$netPriceRemainder	= $qtyRemainder * $dividedNet;

			$dividedTax		=  $totalTax / $totalQty ;
			$dividedTax		=  round($dividedTax , 4);
			$taxRemainder	= $qtyRemainder * $dividedTax;
			 
			$i = 0;
			foreach($splitSkuArray as $spsk)
			{
				if($dividedQty > 0)
				{
					if( $i == 0 ) {
						$result[$spsk]['quantity'] = $dividedQty + $qtyRemainder;
						$result[$spsk]['net'] = ( $dividedQty * $dividedNet ) + $netPriceRemainder;
						$result[$spsk]['tax'] = ( $dividedQty * $dividedTax ) + $taxRemainder;
					} else {
						$result[$spsk]['quantity'] = $dividedQty ;
						$result[$spsk]['net'] = ( $dividedQty * $dividedNet ) ;
						$result[$spsk]['tax'] = ( $dividedQty * $dividedTax	);
					}
				}
				$i++;
			}
			
			$tmp = [];
			$tmp['prow'] = $prow;
			$tmp['totalSku'] = $totalSku;
			$tmp['totalQty'] = $totalQty;
			$tmp['qtyRemainder'] = $qtyRemainder;
			$tmp['taxRemainder'] = $taxRemainder;
			$tmp['netPriceRemainder'] = $netPriceRemainder;
			$tmp['result'] =  $result;
			$this->recordLog("Order", json_encode($tmp,true),  'getSplitedQtyPriceTaxData');
			
		}	
		
		return $result;		
	}


	public function getApplyGiftCards( $order) {

		$applyCards = '';
		$giftCardsJson = $order->getGiftCards();
		if($giftCardsJson) {
			$tmpGf = [];
			$giftCards = json_decode($giftCardsJson,true);
			foreach($giftCards as $giftCard){
				$tmpGf[] = $giftCard['c'];
			}
			$applyCards = implode(" , ",$tmpGf );
		}
		return $applyCards;
 	}
 
}
