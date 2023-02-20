<?php

namespace Bsitc\Brightpearl\Controller\Adminhtml\bpshippingmap;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bsitc_Brightpearl::bpshippingmap');
        $resultPage->addBreadcrumb(__('Bsitc'), __('Bsitc'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Manage Shipping Method Mapping'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Shipping Method Mapping'));

        return $resultPage;
    }
}
