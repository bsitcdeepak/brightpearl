<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\Index;

class Transit extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
