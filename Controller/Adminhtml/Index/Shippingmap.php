<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\Index;

class Shippingmap extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
