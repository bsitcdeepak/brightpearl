<?php

namespace Bsitc\Brightpearl\Controller\Preorder;

use Magento\Framework\App\Action\Context;

class Check extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\Preorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    
    
    public $_mto;
    public $_po;
    

    /**
     * @param Context $context
     * @param \Bsitc\Brightpearl\Helper\Mtodata $preorderHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Bsitc\Brightpearl\Helper\Mtodata $preorderHelper,
        \Bsitc\Brightpearl\Model\Bppurchaseorders $po,
        \Bsitc\Brightpearl\Model\Madetoorder $mto,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_preorderHelper = $preorderHelper;
        $this->_po = $po;
        $this->_mto = $mto;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $info = [];
         
        $helper = $this->_preorderHelper;
        $type = $this->getRequest()->getParam('type');
        $productId = $this->getRequest()->getParam('product_id');
        $product = $this->_preorderHelper->getProduct($productId);
        
        if ($type == 1) {
            $attributesInfo = $this->getRequest()->getParam('info');
            $productId = $helper->getAssociatedId($attributesInfo, $product);
        }
        
        $info['preorder'] = 0;
		$info['pType'] = '';

        if ($helper->isPreorder($productId)) {
            $preorderQty =  $this->_po->getTotalRemainderQtyInPo($product->getSku());
            $msg = "<div class='wk-msg-box wk-info'>".$this->_mto->getPreOrderMsg($product).'</div>';
            $payHtml = $helper->getPayPreOrderHtml();
            $info['preorder'] = 1;
            $info['msg'] = $msg;
            $info['payHtml'] = $payHtml;
            $info['preorderQty'] = $preorderQty;
            $info['preOrderLabel'] = __("Pre Order");
            $info['outOfStockLabel'] = __("Out of stock");
            $info['pType'] = '';
             /*
            $payHtml = $helper->getPayPreOrderHtml();
            $msg = $helper->getPreOrderInfoBlock($productId);
            $info['preorder'] = 1;
            $info['msg'] = $msg;
            $info['payHtml'] = $payHtml;
            */
        }
        
        if ($product->getIsMadetoorder()) {
            $msg = $this->_mto->getMadeToOrderMsg($product);
            $payHtml = $helper->getPayPreOrderHtml();
            $mptoHtml = "<div class='wk-msg-box wk-info'>".$msg."</div>";
            $info['preorder'] = 0;
            $info['msg'] = $msg;
            $info['payHtml'] = $payHtml;
            $info['mptoHtml'] = $mptoHtml;
            $info['preorderQty'] = 0;
            $info['preOrderLabel'] = __("Made To Order");
            $info['outOfStockLabel'] = __("Out of stock");
            $info['pType'] = 'mto';
        }
        
        if ($product->getIsPrinttoorder()) {
            $msg = $this->_mto->getPrintToOrderMsg($product);
            $payHtml = $helper->getPayPreOrderHtml();
            $mptoHtml = "<div class='wk-msg-box wk-info'>".$msg."</div>";
            $info['preorder'] = 0;
            $info['msg'] = $msg;
            $info['payHtml'] = $payHtml;
            $info['mptoHtml'] = $mptoHtml;
            $info['preorderQty'] = 0;
            $info['preOrderLabel'] = __("Print To Order");
            $info['outOfStockLabel'] = __("Out of stock");
            $info['pType'] = 'pto';
        }
         
        $info['stock'] = $helper->getStockDetails($productId);
        $result = $this->_resultJsonFactory->create();
        $result->setData($info);
        return $result;
    }
}
