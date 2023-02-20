<?php


namespace Bsitc\Brightpearl\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class Producttocartafter implements ObserverInterface
{
 
    protected $_request;

 
    
    public function __construct(RequestInterface $request, Json $serializer = null)
    {
        $this->_request = $request;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }
 
  
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $reqeust = $observer->getEvent()->getRequest();
        $item = $observer->getQuoteItem();
        $product = $observer->getProduct();
        
        if ($product->getTypeId() == 'bundle' ||  $product->getTypeId() == 'configurable') {
            $this->setBundleItemAttributes($item);
            $this->checkBespokeItem($item);
            return true;
        } else {   
            if ($item->getChildren()) {
                foreach ($item->getChildren() as $value) {
                    $productId = $value->getProductId();
                    $product = $value->getProduct();
                }
            }

            $additionalOptions = [];
            if ($additionalOption = $item->getOptionByCode('additional_options')) {
                $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
            }
            
            /*
				ItemTypeInfo :  1 => Pre Order
				ItemTypeInfo :  2 => Made To Order
				ItemTypeInfo :  3 => Print To Order
				ItemTypeInfo :  4 => Trade Order
				ItemTypeInfo :  5 => Bespok Order
            */
            
            if ($product->getWkPreorder()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $mtoObj = $objectManager->create('Bsitc\Brightpearl\Model\Madetoorder');
                $chkItem  = $mtoObj->getPreOrderMsg($product, 'array');
                if (count($chkItem) > 0) {
                    $pid                 = $chkItem['pre_id'];
                    $is_printtoorder     = $chkItem['is_preorder'];
                    $generalinfo         = $chkItem['generalinfo'];
                    $exp_delivery_date     = $chkItem['exp_delivery_date'];
                    $po_id                 = $chkItem['po_id'];
                    $additionalOptions[] = ['label' => $generalinfo,'value' => 'Available On '.$exp_delivery_date];
                    $item->setItemTypeInfo(1); // 1 => Pre Order
                    $item->setPid($pid);
                    $item->setGeneralinfo($generalinfo);
                    $item->setExpDeliveryDate($exp_delivery_date);
                    $item->setPoId($po_id);
                }
            } elseif ($product->getIsMadetoorder()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $mtoObj = $objectManager->create('Bsitc\Brightpearl\Model\Madetoorder');
                $chkItem  = $mtoObj->getMadeToOrderMsg($product, 'array');
                if (count($chkItem) > 0) {
                    $pid                 = $chkItem['mto_id'];
                    $is_madetoorder     = $chkItem['is_madetoorder'];
                    $generalinfo         = $chkItem['generalinfo'];
                    $lead_time_txt         = $chkItem['mto_lead_time_txt'];
                    $lead_time_days     = $chkItem['mto_lead_time_days'];
                    $additionalOptions[] = ['label' => $generalinfo,'value' => 'Available in '.$lead_time_txt];
                    $item->setItemTypeInfo(2);  // 2 => Made Order
                    $item->setPid($pid);
                    $item->setGeneralinfo($generalinfo);
                    $item->setLeadTimeTxt($lead_time_txt);
                    $item->setLeadTimeDays($lead_time_days);
                }
            } elseif ($product->getIsPrinttoorder()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $mtoObj = $objectManager->create('Bsitc\Brightpearl\Model\Madetoorder');
                $chkItem  = $mtoObj->getPrintToOrderMsg($product, 'array');
                if (count($chkItem) > 0) {
                    $pid                 = $chkItem['pto_id'];
                    $is_printtoorder     = $chkItem['is_printtoorder'];
                    $generalinfo         = $chkItem['generalinfo'];
                    $lead_time_txt         = $chkItem['pto_lead_time_txt'];
                    $lead_time_days     = $chkItem['pto_lead_time_days'];
                    $additionalOptions[] = ['label' => $generalinfo,'value' => 'Available in '.$lead_time_txt];
                    $item->setItemTypeInfo(3);  // 3 => Print Order
                    $item->setPid($pid);
                    $item->setGeneralinfo($generalinfo);
                    $item->setLeadTimeTxt($lead_time_txt);
                    $item->setLeadTimeDays($lead_time_days);
                }
            } else {
				/* ---------- */
			}
            if (count($additionalOptions) > 0) {
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize($additionalOptions)
                ]);
            }
        }
    }
    
    
    
    public function setBundleItemAttributes($items)
    {
        $bundleAdditionalOptions = [];
        if ($additionalOption = $items->getOptionByCode('additional_options')) {
            $bundleAdditionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
        }
        if ($items->getChildren()) {
            foreach ($items->getChildren() as $item) {
                $productId     = $item->getProductId();
                $product     = $item->getProduct();
                $additionalOptions = [];
                if ($additionalOption = $item->getOptionByCode('additional_options')) {
                    $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
                }
                
                if ($product->getWkPreorder()) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $mtoObj = $objectManager->create('Bsitc\Brightpearl\Model\Madetoorder');
                    $chkItem  = $mtoObj->getPreOrderMsg($product, 'array');
                    if (count($chkItem) > 0) {
                        $pid                 = $chkItem['pre_id'];
                        $is_printtoorder     = $chkItem['is_preorder'];
                        $generalinfo         = $chkItem['generalinfo'];
                        $exp_delivery_date     = $chkItem['exp_delivery_date'];
                        $po_id                 = $chkItem['po_id'];
                        $additionalOptions[] = ['label' => $generalinfo,'value' => 'Available On '.$exp_delivery_date];
                        $bundleAdditionalOptions[] = ['label' => $generalinfo,'value' => $product->getName() . ' - Available On '.$exp_delivery_date];
                        $item->setItemTypeInfo(1); // 1 => Pre Order
                        $item->setPid($pid);
                        $item->setGeneralinfo($generalinfo);
                        $item->setExpDeliveryDate($exp_delivery_date);
                        $item->setPoId($po_id);
                    }
                } elseif ($product->getIsMadetoorder()) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $mtoObj = $objectManager->create('Bsitc\Brightpearl\Model\Madetoorder');
                    $chkItem  = $mtoObj->getMadeToOrderMsg($product, 'array');
                    if (count($chkItem) > 0) {
                        $pid                 = $chkItem['mto_id'];
                        $is_madetoorder     = $chkItem['is_madetoorder'];
                        $generalinfo         = $chkItem['generalinfo'];
                        $lead_time_txt         = $chkItem['mto_lead_time_txt'];
                        $lead_time_days     = $chkItem['mto_lead_time_days'];
                        $additionalOptions[] = ['label' => $generalinfo,'value' => 'Available in '.$lead_time_txt];
                        $bundleAdditionalOptions[] = ['label' => $generalinfo,'value' => $product->getName() . ' - Available in '.$lead_time_txt];
                        $item->setItemTypeInfo(2);  // 2 => Made Order
                        $item->setPid($pid);
                        $item->setGeneralinfo($generalinfo);
                        $item->setLeadTimeTxt($lead_time_txt);
                        $item->setLeadTimeDays($lead_time_days);
                    }
                } elseif ($product->getIsPrinttoorder()) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $mtoObj = $objectManager->create('Bsitc\Brightpearl\Model\Madetoorder');
                    $chkItem  = $mtoObj->getPrintToOrderMsg($product, 'array');
                    if (count($chkItem) > 0) {
                        $pid                 = $chkItem['pto_id'];
                        $is_printtoorder     = $chkItem['is_printtoorder'];
                        $generalinfo         = $chkItem['generalinfo'];
                        $lead_time_txt         = $chkItem['pto_lead_time_txt'];
                        $lead_time_days     = $chkItem['pto_lead_time_days'];
                        $additionalOptions[] = ['label' => $generalinfo,'value' => 'Available in '.$lead_time_txt];
                        $bundleAdditionalOptions[] = ['label' => $generalinfo,'value' => $product->getName() . ' - Available in '.$lead_time_txt];
                        $item->setItemTypeInfo(3);  // 3 => Print Order
                        $item->setPid($pid);
                        $item->setGeneralinfo($generalinfo);
                        $item->setLeadTimeTxt($lead_time_txt);
                        $item->setLeadTimeDays($lead_time_days);
                    }
                } else {
					/* ---------- */
				}
                if (count($additionalOptions) > 0) {
                    $item->addOption([
                        'product_id' => $item->getProductId(),
                        'code' => 'additional_options',
                        'value' => $this->serializer->serialize($additionalOptions)
                    ]);
                }
            }
        }
        /* update option value at bundle level */
        if (count($bundleAdditionalOptions) > 0) {
            $items->addOption([
            'product_id' => $items->getProductId(),
            'code' => 'additional_options',
            'value' => $this->serializer->serialize($bundleAdditionalOptions)
            ]);
        }
    }

	public function checkBespokeItem($items)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->create('Bsitc\Brightpearl\Helper\Mtodata');
		$isBuilderProduct = $helper->isBuilderProduct($items->getProduct()->getAttributeSetId());
		if($isBuilderProduct){
			$items->setItemTypeInfo(5);  // 5 => Bespok Order
		}
	}

}
