<?php

namespace Bsitc\Brightpearl\Model;

class Inventoryintransit extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */

    protected function _construct()
    {
        $this->_init('Bsitc\Brightpearl\Model\ResourceModel\Inventoryintransit');
           $objectManager            = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_objectManager    =  $objectManager;
    }
    
    public function syncFromApi()
    {

        /*Check if Api Autherize*/
        
            $api = $this->_objectManager->create('Bsitc\Brightpearl\Model\Api');
        if ($api->authorisationToken) {
            $result = $api->GetStockTransfer();

            if (count($result)>0) {
                foreach ($result['response'] as $item) {
                    if (array_key_exists('goodsOutNoteId', $item)) {
                            $goodoutnoteid  = $item['goodsOutNoteId'];
                        if ($goodoutnoteid) {
                                    $response  = $api->getGoodsOutNote($goodoutnoteid);
                                    $responses = $response['response'];
                            foreach ($responses as $response) {
                                if ($response['transfer'] == 1) {
                                                        $data = [];
                                                        $data['source_warehouse_id'] = $response['warehouseId'];
                                                        $data['target_warehouse_id'] = $response['targetWarehouseId'];
                                                        $data['transfer']              = $response['transfer'];
                                                        $data['stock_transfer_id']      = $response['stockTransferId'];
                                                        $data['product_id']          = $response['transferRows']['0']['productId'];
                                                        $data['quantity']              = $response['transferRows']['0']['quantity'];
                                                        $data['status']              = 'pending';
                                                        $model = $this->_objectManager->create('Bsitc\Brightpearl\Model\Inventoryintransit');
                                                        $model->setData($data);
                                                        $model->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
