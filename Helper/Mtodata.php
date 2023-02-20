<?php

namespace Bsitc\Brightpearl\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Area;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as Products;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory as InvoiceItemCollection;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Mtodata extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var Configurable
     */
    protected $_configurable;

    /**
     * @var \Magento\Catalog\Model\ProductFactor
     */
    protected $_product;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_order;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $_option;

    /**
     * @var Products
     */
    protected $_productCollection;

    /**
     * @var Items
     */
    protected $_itemCollection;

    /**
     * @var CollectionFactory
     */
    protected $_preorderCollection;

    /**
     * @var ProductFactory
     */
    protected $_catalogProduct;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;
    
    
    protected $_orderItemRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\SessionManager $session
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Catalog\Model\Product\OptionFactory $option
     * @param \Magento\Catalog\Model\Product $catalog
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Webkul\Preorder\Model\ItemFactory $item
     * @param \Webkul\Preorder\Model\ProductFactory $preorderProductFactory
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param Configurable $configurable
     * @param Products $productCollection
     * @param Items $itemCollection
     * @param CollectionFactory $preorderCollection
     * @param CompleteCollection $completeCollection
     * @param OrderCollection $orderCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Catalog\Helper\Product\Configuration $configuration
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param InvoiceItemCollection $invoiceItemCollection
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockConfigurationInterface $stockConfiguration
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManager $session,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Catalog\Model\Product\OptionFactory $option,
        \Magento\Catalog\Model\Product $catalog,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\CartFactory $cart,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        Configurable $configurable,
        Products $productCollection,
        // CompleteCollection $completeCollection,
        OrderCollection $orderCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Helper\Product\Configuration $configuration,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        InvoiceItemCollection $invoiceItemCollection,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        LoggerInterface $logger
    ) {
        $this->_request = $context->getRequest();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_resource = $resource;
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        $this->_product = $product;
        $this->_order = $order;
        $this->_option = $option;
        $this->_catalogProduct = $catalog;
        $this->_customerSession = $customerSession;
        $this->_cart = $cart;
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_configurable = $configurable;
        $this->_productCollection = $productCollection;
        // $this->_itemCollection = $itemCollection;
        // $this->_preorderCollection = $preorderCollection;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_priceCurrency = $priceCurrency;
        $this->_state = $state;
        $this->_configuration = $configuration;
        $this->_stockRegistry = $stockRegistry;
        $this->_invoiceItemCollection = $invoiceItemCollection;
        $this->_stockRegistryProvider = $stockRegistryProvider;
        $this->_stockConfiguration = $stockConfiguration;
        $this->_currencyFactory = $currencyFactory;
        $this->_escaper = $escaper;
        $this->_objectManager = $objectManager;
        $this->_customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->_orderItemRepository = $orderItemRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }
	
    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
	

    /**
     * Get Stock Status of Product.
     *
     * @param int $productId
     *
     * @return bool
     */
    public function getStockStatus($productId)
    {
        $stockDetails = $this->getStockDetails($productId);
        // return $stockDetails['is_in_stock'];
        return $stockDetails['manage_stock'];
    }

    /**
     * Check Product is Preorder or Not.
     *
     * @param int  $productId
     * @param bool $stockStatus [optional]
     *
     * @return bool
     */
    public function isPreorder($productId, $stockStatus = '')
    {
        $isProduct = false;
        $productId = (int) $productId;
        if (!$this->isValidProductId($productId)) {
             return false;
        }
        
        $collection = $this->_productCollection->create();
        $collection->addFieldToFilter('entity_id', $productId);
        $collection->addAttributeToSelect('*');

        foreach ($collection as $item) {
            $product = $item;
            $isProduct = true;
            break;
        }

        if (!$isProduct) {
            return false;
        }
          
        
        $productType = $product->getTypeId();
        $productTypeArray = ['configurable', 'bundle', 'grouped', 'downloadable'];
        $allowedProductType = ['simple', 'virtual'];
        if (!in_array($productType, $allowedProductType)) {
            return false;
        }
 
        if ($stockStatus == '') {
            $stockStatus = $this->getStockStatus($productId);
        }
         
        if (!$stockStatus) {
            $pendingQty = 0;
            $preorderStatus = $product->getWkPreorder();
            if ($preorderStatus) {
                return $preorderStatus;
            }
        }
        return false;
    }

    /**
     * Check Whether Product Id is Valid or Not
     *
     * @param int $productId
     *
     * @return bool
     */
    public function isValidProductId($productId)
    {
        if ($productId == '' || $productId == 0) {
            return false;
        }

        return true;
    }
    /**
     * Check Configurable Product is Preorder or Not.
     *
     * @param int $productId
     *
     * @return bool
     */
    public function isConfigPreorder($productId, $stockStatus = '')
    {
        $isProduct = false;
        $collection = $this->_productCollection->create();
        $collection->addFieldToFilter('entity_id', $productId);
        $collection->addAttributeToSelect('*');
        foreach ($collection as $item) {
            $product = $item;
            $isProduct = true;
            break;
        }
        if ($isProduct) {
            $stock = $this->getStockDetails($productId);
            if (!$stock['is_in_stock']) {
                return false;
            }
            $productType = $product->getTypeId();
            if ($productType == 'configurable') {
                $configModel = $this->_configurable;
                $collection = $this->_objectManager->create('\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection');
                $collection->setProductFilter($product);
                foreach ($collection as $key) {
                    $usedProductIds[] = $key->getEntityId();
                }
                // $usedProductIds = $configModel->getUsedProductIds($product);
                foreach ($usedProductIds as $usedProductId) {
                    if ($stockStatus != '') {
                        if ($this->isPreorder($usedProductId, $stockStatus)) {
                            return true;
                        }
                    } else {
                        if ($this->isPreorder($usedProductId)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get Mediad Path.
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * Get Product's Price.
     *
     * @param int $productId
     *
     * @return float
     */
    public function getPrice($product)
    {
        $price = $product->getFinalPrice();

        return $price;
    }

    /**
     * Get Order by Id.
     *
     * @param int $orderId
     *
     * @return object
     */
    public function getOrder($orderId)
    {
        return $this->_order->create()->load($orderId);
    }

    /**
     * Check Order Item is Preorder or Not.
     *
     * @param int $itemId
     * @param bool $useOrder
     *
     * @return bool
     */
    public function isPreorderOrderedItem($itemId, $useOrder = false)
    {
        $item = false;
        //$orderItemRepository = $this->_objectManager->create('\Magento\Sales\Api\OrderItemRepositoryInterface');
        $item = $this->_orderItemRepository->get($itemId);
        /*
            ItemTypeInfo = 1 for Pre order
            ItemTypeInfo = 2 for Made to order
            ItemTypeInfo = 3 for Print to order
            ItemTypeInfo = 4 for Bespoke order
            ItemTypeInfo = 5 for Trade order
        */
        if ($item) {
            if ($item->getItemTypeInfo() == '1') {
                return true;
            }
        }
        

        return false;
    }

    
    /**
     * Check Product is Child Product or Not.
     *
     * @return bool
     */
    public function isChildProduct()
    {
        $productId = $this->_request->getParam('id');
        $productModel = $this->_product->create();
        $product = $productModel->load($productId);
        $productType = $product->getTypeID();
        $productTypeArray = ['bundle', 'grouped'];
        if (in_array($productType, $productTypeArray)) {
            return true;
        }

        return false;
    }

    /**
     * Check Preorder Option is Allowed for Product Type.
     *
     * @return bool
     */
    public function showPreorderOption()
    {
        $productId = $this->_request->getParam('id');
        $productModel = $this->_product->create();
        $product = $productModel->load($productId);
        $productType = $product->getTypeID();
        $productTypeArray = ['configurable', 'bundle', 'grouped', 'downloadable'];
        if (in_array($productType, $productTypeArray)) {
            return 0;
        }

        return 1;
    }
    
    /**
     * Get Url To Check Configurable Product is Preorder or Not.
     *
     * @return string
     */
    public function getCheckConfigUrl()
    {
        return $this->_urlBuilder->getUrl('brightpearl/preorder/check/');
    }
    
    

    /**
     * Get Account Login Url For Customer.
     *
     * @return string
     */
    public function getLogInUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/login/');
    }

    /**
     * Get Product by Id.
     *
     * @param int $productId
     *
     * @return object
     */
    public function getProduct($productId)
    {
        return $this->_product->create()->load($productId);
    }
 
    public function getPayPreOrderHtml($isListPage = false)
    {
        $html = '';
        return $html;
    }
 
 
 
 
     /**
      * Get Stock Details of Product.
      *
      * @param int $productId
      *
      * @return array
      */
    public function getStockDetails($productId)
    {
        $connection = $this->_resource->getConnection();
        $stockDetails = ['is_in_stock' => 0, 'qty' => 0];
        $collection = $this->_productCollection->create()->addAttributeToSelect('name');
        $table = $connection->getTableName('cataloginventory_stock_item');
        $bind = 'product_id = entity_id';
        $cond = '{{table}}.stock_id = 1';
        $type = 'left';
        $alias = 'is_in_stock';
        $field = 'is_in_stock';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $alias = 'qty';
        $field = 'qty';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $alias = 'manage_stock';
        $field = 'manage_stock';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $collection->addFieldToFilter('entity_id', $productId);
        foreach ($collection as $value) {
            $stockDetails['qty'] = $value->getQty();
            $stockDetails['is_in_stock'] = $value->getIsInStock();
            $stockDetails['manage_stock'] = $value->getManageStock();
            $stockDetails['name'] = $value->getName();
        }

        return $stockDetails;
    }

    /**
     * Check Product is Available or Not to Complete Preorder.
     *
     * @param int $productId
     * @param int $qty
     * @param int $isQty
     *
     * @return bool
     */
    public function isAvailable($productId, $qty, $isQty = 0)
    {
        $stockDetails = $this->getStockDetails($productId);
        if ($stockDetails['is_in_stock'] == 1) {
            if ($isQty == 0) {
                if ($stockDetails['qty'] >= $qty) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if Configurable Product is Available or Not to Complete Preorder.
     *
     * @param int $productId
     * @param int $qty
     * @param int $parentId
     *
     * @return bool
     */
    public function isConfigAvailable($productId, $qty, $parentId)
    {
        if ($this->isAvailable($productId, $qty)) {
            if ($this->isAvailable($parentId, $qty, 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Html Block of Preorder Info Block.
     *
     * @param int $productId
     *
     * @return html
     */
    public function getPreOrderInfoBlock($productId)
    {
        
        //$orderItemRepository = $this->_objectManager->create('\Magento\Sales\Api\OrderItemRepositoryInterface');
        
        

        // write logic youre  here
        $html = '';
        $displayDate = false;
        $today = date('m/d/y');
        $product = $this->getProduct($productId);
        $availability = $product->getPreorderAvailability();
        if ($availability != '') {
            $date = date_create($availability);
            $dispDate = date_format($date, 'l jS F Y');
            $date = date_format($date, 'm/d/y');
            if (strtotime($date) > strtotime($today)) {
                $displayDate = true;
            }
        }
        if ($displayDate) {
            $html .= "<div class='wk-msg-box wk-info wk-availability-block'>";
            $html .= "<span class='wk-date-title'>";
            $html .= __('Available On');
            $html .= ' :</span>';
            $html .= "<span class='wk-date'>".$dispDate.'</span>';
            $html .= '</div>';
        }

        return $html;
    }
 
    /**
     * Get All Website Ids.
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        $websiteIds = [];
        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }

    /**
     * Get First Object From Collection
     *
     * @param array | int | string $value
     * @param array | string $field
     * @param object $collection
     *
     * @return object | bool
     */
    public function getDataByField($values, $fields, $collection)
    {
        $item = false;
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $field = $fields[$key];
                $collection = $collection->addFieldToFilter($field, $value);
            }
        } else {
            $collection = $collection->addFieldToFilter($fields, $values);
        }

        foreach ($collection as $item) {
            return $item;
        }

        return $item;
    }

    /**
     * Get Associated Product Id
     *
     * @param string $attribute
     * @param object $product
     *
     * @return int
     */
    public function getAssociatedId($attribute, $product)
    {
        $configModel = $this->_configurable;
        $product = $configModel->getProductByAttributes($attribute, $product);
        $productId = $product->getId();
        return $productId;
    }


    /**
     * Save Cart
     */
    public function saveCart()
    {
        $cartModel = $this->_cart->create();
        $quote = $cartModel->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $parentItem = ($item->getParentItem() ? $item->getParentItem() : $item);
            $parentItem->save();
        }
        $quote->save();
        $cartModel->save();
    }

    /**
     * Check Customer is Logged In or Not
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return true;
        }

        return false;
    }

    /**
     * Check Whether Guest is Logged In or Not
     *
     * @return bool
     */
    public function isGuestLoggedIn()
    {
        $email = trim($this->_session->getGuestEmailId());
        if ($email != "") {
            return true;
        }

        return false;
    }

    /**
     * Check Guest Details
     *
     * @param int $incrementId
     * @param string $emil
     *
     * @return bool
     */
    public function authenticate($incrementId, $email)
    {
        $orders = $this->_orderCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_email', $email)
                        ->addFieldToFilter('increment_id', $incrementId)
                        ->addFieldToFilter('customer_is_guest', 1)
                        ->setPageSize(1);
        if ($orders->getSize()) {
            return true;
        }

        return false;
    }

    /**
     * Login Guest
     *
     * @param string $email
     */
    public function loginGuest($email)
    {
        $this->_session->setGuestEmailId($email);
    }

    /**
     * Get Guest Email Id
     *
     * @return bool
     */
    public function getGuestEmailId()
    {
        return trim($this->_session->getGuestEmailId());
    }

    /**
     * Get Price According To Current Store
     *
     * @param float $price
     *
     * @return float
     */
    public function getCurrentPrice($price)
    {
        $currency = null;
        $store = $this->_storeManager->getStore()->getStoreId();
        $price = $this->_priceCurrency->convert($price, $store, $currency);
        return $price;
    }

    /**
     * Get Price With Currency
     *
     * @param float $price
     *
     * @return string
     */
    public function getPriceWithCurrency($price)
    {
        $price = $this->_priceCurrency->convertAndFormat($price);
        return $price;
    }

    /**
     * Save Quote Item Updates
     *
     * @return string
     */
    public function checkStatus()
    {
        $this->_cart->create()->save();
        $cartModel = $this->_cart->create();
        $quote = $cartModel->getQuote();
        $this->collectTotals($quote);
    }

    /**
     * Get Cart
     *
     * @return object
     */
    public function getCart()
    {
        $cartModel = $this->_cart->create();
        return $cartModel;
    }

    /**
     * Check Whether it is Admin Area
     */
    public function isAdminArea()
    {
        if ($this->_state->getAreaCode() == "adminhtml") {
            return true;
        }

        return false;
    }

    /**
     * Update Stock Data of Product
     *
     * @param int $productId
     * @param int $qty
     * @param bool $decrease
     */
    public function updateStockData($productId, $qty, $decrease = false)
    {
        try {
            $scopeId = $this->_stockConfiguration->getDefaultScopeId();
            $stockItem = $this->_stockRegistryProvider->getStockItem($productId, $scopeId);
            if ($stockItem->getItemId()) {
                if ($decrease) {
                    $qty = $stockItem->getQty(true) - $qty;
                } else {
                    $qty = $qty + $stockItem->getQty(true);
                }

                $stockItem->setQty($qty);
                $stockItem->save();
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * Get Preorder Item Details
     *
     * @param int $itemId
     *
     * @return object
     */
    public function getPreorderItemDetails($itemId)
    {
        $item = false;
        //$orderItemRepository = $this->_objectManager->create('\Magento\Sales\Api\OrderItemRepositoryInterface');
        $item = $this->_orderItemRepository->get($itemId);
        if ($item) {
            return $item;
        }
        return $item;
    }

     /**
      * Get Parameters
      *
      * @return array
      */
    public function getParams()
    {
        return $this->_request->getParams();
    }

    /**
     * Get Order Items With Preorder Info
     *
     * @param object $order
     *
     * @return array
     */
    public function getOrderItemsResult($order)
    {
        $items = $order->getItemsCollection();
        $result = [];
        $info = [];
        $displayMessage = false;
        $count = 0;
        foreach ($items as $item) {
            $itemId = $item->getId();
            $productId = $item->getProductId();
            if ($item->getParentItem()) {
                $preorderItem = $this->getPreorderItemDetails($itemId);
                $parentId = $item->getParentItem()->getProductId();
                $key = $count-1;
                $info[$key]['item_id'] = $itemId;
                $info[$key]['product_id'] = $productId;
                $info[$key]['parent_id'] = $parentId;
                if ($this->isPreorderOrderedItem($item->getId(), true)) {
                    $displayMessage = true;
                    $info[$key]['preorder'] = $this->getPreorderStatus($itemId);
                    if ($this->isConfigAvailable($productId, $qty, $parentId) &&
                        $this->isPreorderItemInvoiced($item, $qty) &&
                        $item->getQtyRefunded() == 0) {
                        if ($info[$key]['preorder'] == self::PREORDER_STATUS_PENDING) {
                            $info[$key]['preorder_completion_allowed'] = true;
                        }
                    }
                    $info[$key]["preorder_option_html"] = $this->getOptionHtml($preorderItem);
                }
            } else {
                $extraInfo = $this->getOptionsInfo($item);
                $preorderItem = $this->getPreorderItemDetails($itemId);
                $qty = $item->getQtyOrdered();
                $name = $item->getName();
                $itemInfo = [
                                'preorder' => null,
                                'item_id' => $itemId,
                                'parent_id' => 0,
                                'product_id' => $productId,
                                'product_name' => $name,
                                'qty' => $qty,
                                'preorder_option_html' => '',
                                'preorder_completion_allowed' => false,
                                'options_info' => $extraInfo
                            ];
                if ($this->isPreorderOrderedItem($item->getId(), true)) {
                    $displayMessage = true;
                    $itemInfo['preorder'] = $this->getPreorderStatus($itemId);
                    if ($this->allowCompletePreorder($item, $qty)) {
                        if ($itemInfo['preorder'] == self::PREORDER_STATUS_PENDING) {
                            $itemInfo['preorder_completion_allowed'] = true;
                        }
                    }
                }

                $itemInfo["preorder_option_html"] = $this->getOptionHtml($preorderItem);
                $info[$count] = $itemInfo;
                $count++;
            }
        }

        $result['info'] = $info;
        $result['display_msg'] = $displayMessage;
        return $result;
    }

    /**
     * Get Product By Product Name
     *
     * @param string $productName
     *
     * @return object
     */
    public function getProductByName($productName)
    {
        $collection = $this->_productCollection
                            ->create()
                            ->addAttributeToFilter("name", $productName);
        foreach ($collection as $product) {
            return $product;
        }

        return false;
    }
 
    public function getOptionHtml($item)
    {
        if ($item) {
            $html = "";
            $html .= "<div class='wk-preorder-info'>";
            $html .= "<div class='wk-preorder-info-title'>Preorder Information</div>";
            $html .= "<div class='wk-preorder-info-items'>";
            $html .= "<div class='wk-preorder-info-item'>";
            $html .= "<div class='wk-preorder-info-item-label'>Type :</div>";
            $html .= "<div class='wk-preorder-info-item-value'>Pre Order</div>";
            $html .= "</div>";
            $html .= "<div class='wk-preorder-info-item'>";
            $html .= "</div>";
            $html .= "</div>";
            $html .= "</div>";
            return $html;
        }

        return "";
    }

    public function getPreorderNote()
    {
        return __("This order contains Preorder Products.");
    }

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Convert Base Price To Currency Price
     *
     * @param float $amount
     * @param string $currency
     *
     * @return float
     */
    public function convertPriceFromBase($amount, $currency)
    {
        $store = $this->_storeManager->getStore();
        return $store->getBaseCurrency()->convert($amount, $currency);
    }

    /**
     * Convert Price To Base Currency Price
     *
     * @param float $amount
     * @param string $currency
     *
     * @return float
     */
    public function convertPriceToBase($amount, $currency)
    {
        $rate = $this->getCurrencyRateToBase($currency);
        $amount = $amount * $rate;
        return $amount;
    }

    /**
     * Get Currency Rate To Base Currency
     *
     * @param string $currency
     *
     * @return float
     */
    public function getCurrencyRateToBase($currency)
    {
        $store = $this->_storeManager->getStore();
        $baseCurrencyCode = $store->getBaseCurrency()->getCode();
        $rate = $this->_currencyFactory->create()
                    ->load($currency)
                    ->getAnyRate($baseCurrencyCode);
        return $rate;
    }

    /**
     * Get Product's Purchased Options Info
     *
     * @param Magento\Sales\Model\Order\Item $item
     *
     * @return array
     */
    public function getOptionsInfo($item)
    {
        $result = "";
        $customOptions = [];
        $data = $item->getProductOptions();
        $configurableOptions = [];
        $hasCustomOptions = false;
        $hasConfigOptions = false;

        try {
            if (array_key_exists("options", $data)) {
                foreach ($data['options'] as $option) {
                    $hasCustomOptions = true;
                    $customOptions[] = $option['label'].":".$option['print_value'];
                }
            }
        } catch (\Exception $e) {
            $hasCustomOptions = false;
        }
        
        try {
            if (array_key_exists("attributes_info", $data)) {
                foreach ($data['attributes_info'] as $option) {
                    $hasConfigOptions = true;
                    $configurableOptions[] = $option['label'].":".$option['value'];
                }
            }
        } catch (\Exception $e) {
            $hasConfigOptions = false;
        }

        if ($hasCustomOptions) {
            $result .= "Custom Options => ".implode(", ", $customOptions);
        }

        if ($hasConfigOptions) {
            if ($hasCustomOptions) {
                $result .= " | Configurable Options => ".implode(", ", $configurableOptions);
            } else {
                $result .= "Configurable Options => ".implode(", ", $configurableOptions);
            }
        }

        return $result;
    }

    /**
     * Check Preorder Product Qty.
     * @param  $item
     * @param  \Magento\Catalog\Model\Product  $product
     * @return bool
     */
    public function getQtyCheck($item, $product)
    {
        $productType = $product->getTypeId();
        if ($productType == 'configurable') {
            $configModel = $this->_configurable;
            $usedProductIds = $configModel->getUsedProductIds($product);
            foreach ($usedProductIds as $usedProductId) {
                return $this->checkPreorderAvailable($usedProductId, $item);
            }
        } else {
            return $this->checkPreorderAvailable($product->getId(), $item);
        }
        return true;
    }

    /**
     * Check Product Availability for Preorder.
     * @int  $productId
     * @return bool
     */
    public function checkPreorderAvailable($productId, $item)
    {
        $product = $this->_catalogProduct->load($productId);
         $preorderStatus = $product->getWkPreorder();
        if ($preorderStatus) {
            return true;
        }
        return false;
    }
  
    /**
     * Get list of last ordered products
     *
     * @return array
     */
    public function getItems($order, $limit)
    {
        $items = [];
        
        if ($order) {
            $website = $this->_storeManager->getStore()->getWebsiteId();
            /*
                * @var \Magento\Sales\Model\Order\Item $item
            */
            foreach ($order->getParentItemsRandomCollection($limit) as $item) {
                try {
                    $product = $this->productRepository->getById(
                        $item->getProductId(),
                        false,
                        $this->_storeManager->getStore()->getId()
                    );
                } catch (NoSuchEntityException $noEntityException) {
                    $this->logger->critical($noEntityException);
                    continue;
                }

                if ($item->hasData('product') && in_array($website, $item->getProduct()->getWebsiteIds())) {
                    $items[] = [
                                'id'          => $item->getId(),
                                'name'        => $item->getName(),
                                'url'         => $item->getProduct()->getProductUrl(),
                                'is_saleable' => $this->isItemAvailableForReorder($item),
                               ];
                }
            }
        }
        return $items;
    }

    public function getProductByRepository($productId)
    {
        return $this->productRepository->getById(
            $productId,
            false,
            $this->_storeManager->getStore()->getId()
        );
    }
 
    public function getPreorderBySku($sku)
    {
        $productModel = $this->_product->create();
        $productId = (int) $productModel->getIdBySku($sku);
        return $this->isPreorder($productId);
    }
	
    public function getAttributeSetByName($name)
    {
		
		 $filterBuilder	= $this->_objectManager->create('\Magento\Framework\Api\FilterBuilder');
		 $searchCriteriaBuilder	= $this->_objectManager->create('\Magento\Framework\Api\SearchCriteriaBuilder');
		 $attributeSetRepository = $this->_objectManager->create('\Magento\Eav\Api\AttributeSetRepositoryInterface');
		
        $filter = $filterBuilder->setField('attribute_set_name')
            ->setConditionType('eq')
            ->setValue($name)
            ->create();

        $searchCriteria = $searchCriteriaBuilder->addFilters([$filter])->create();

        foreach ($attributeSetRepository->getList($searchCriteria)->getItems() as $attributeSet) {
            return $attributeSet;
        }
    }
	
	public function getStockItem($productId)
	{
		$scopeId = $this->_stockConfiguration->getDefaultScopeId();
		$stockItem = $this->_stockRegistryProvider->getStockItem($productId, $scopeId);
		return $stockItem;
	}
	
	public function isBuilderProduct($productAttributeSetId)
	{
		$flag = false;
		$enable_fabric_stock =  $this->getConfig('bpconfiguration/builders/enable_fabric_stock');
		$builders_attribute_set =  $this->getConfig('bpconfiguration/builders/attribute_set');
		$configAttributeSetArray = explode(",",$builders_attribute_set);
		if (in_array($productAttributeSetId, $configAttributeSetArray))
		{
			$flag = true;
		}
		return $flag;
	}	
	
	
}
