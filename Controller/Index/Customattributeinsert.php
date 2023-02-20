<?php
namespace Bsitc\Brightpearl\Controller\Index;

class Customattributeinsert extends \Magento\Framework\App\Action\Action
{
    

    protected $api;
    protected $restapi;
    protected $productapi;
    protected $attributeapi;

    protected $customattribute;

    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bsitc\Brightpearl\Model\Api $api,
        \Bsitc\Brightpearl\Model\RestApi $restapi,
        \Bsitc\Brightpearl\Model\BpproductsFactory $productapi,
        \Bsitc\Brightpearl\Model\AttributeFactory $attributeapi,
        \Bsitc\Brightpearl\Model\CustomattributeFactory $customattribute
    ) {
        $this->api             = $api;
        $this->restapi         = $restapi;
        $this->productapi       = $productapi;
        $this->attributeapi    = $attributeapi;
        $this->customattribute = $customattribute;
        parent::__construct($context);
    }
    
    public function execute()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $datahelper       = $objectManager->create('Bsitc\Brightpearl\Helper\Data');

        $data = $this->restapi->setCollectionOption();

        /*$code = $datahelper->getCustomAttributes();
        $codes = explode(",", $code);
        foreach($codes as $code){
            $data = $this->restapi->setCollectionOption($code);
            //$data = $this->customattribute->syncCustomattributeApi($code);
        }*/


        $code = 'bp_collection';
    }
}
