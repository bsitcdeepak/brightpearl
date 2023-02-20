<?php
namespace Bsitc\Brightpearl\Controller\Adminhtml\channel;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Synchannelapi extends \Magento\Backend\App\Action
{
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
         $collection = $this->_objectManager->create('Bsitc\Brightpearl\Model\ResourceModel\Channel\Collection');
        $collection->walk('delete');
                 
     
        $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->getChannelName();
            if (count($result)>0) {
                foreach ($result['response'] as $item) {
                    $data = [];

                    $channelTypeId = '';
                    if (array_key_exists('channelTypeId', $item)) {
                        $channelTypeId = $item['channelTypeId'];
                    }
                    
                    $defaultWarehouseId = '';
                    if (array_key_exists('defaultWarehouseId', $item)) {
                        $defaultWarehouseId = json_encode($item['defaultWarehouseId']);
                    }

                    $websiteid = '';
                    if (array_key_exists('warehouseIds', $item)) {
                        $websiteid = $item['warehouseIds'];
                    }
                    
                    $integrationDetail = '';
                    if (array_key_exists('integrationDetail', $item)) {
                        $integrationDetail = json_encode($item['integrationDetail']);
                    }
                    
                    $channelBrandId = '';
                    if (array_key_exists('channelBrandId', $item)) {
                        $channelBrandId = $item['channelBrandId'];
                    }

                    $showInChannelMenu = '';
                    if (array_key_exists('showInChannelMenu', $item)) {
                        $showInChannelMenu = $item['showInChannelMenu'];
                    }

                    $defaultPriceListId = '';
                    if (array_key_exists('defaultPriceListId', $item)) {
                        $defaultPriceListId = $item['defaultPriceListId'];
                    }
                
                    $showInChannelMenu = '';
                    if (array_key_exists('showInChannelMenu', $item)) {
                        $showInChannelMenu = $item['showInChannelMenu'];
                    }
                    
                    $contactGroupId = '';
                    if (array_key_exists('contactGroupId', $item)) {
                        $contactGroupId = $item['contactGroupId'];
                    }
                    
                    
                    $data['channel_id'] = $item['id'];
                    $data['name'] = $item['name'];
                    $data['channel_type_id'] = $channelTypeId;
                    $data['channel_brand_id'] = $channelBrandId;
                    $data['show_inchannel_menu'] = $showInChannelMenu;
                    $data['contact_group_id'] = $contactGroupId;
                    $data['default_price_list_id'] = $defaultPriceListId;
                    $data['show_inchannel_menu'] = $showInChannelMenu;
                    $data['warehouse_ids'] = $defaultWarehouseId;
                    $data['integration_detail'] = $integrationDetail;
                    
                    $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Channel');
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        $this->messageManager->addSuccess(__('Brightpearl Payment Method has been saved.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
