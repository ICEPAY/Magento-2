<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Controller\Adminhtml\Paymentmethod;


class Index extends \Icepay\IcpCore\Controller\Adminhtml\Paymentmethod
{
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }

        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Icepay_IcpCore::paymentmethod');
        $resultPage->getConfig()->getTitle()->prepend(__('Payment Methods'));
        $resultPage->addBreadcrumb(__('ICEPAY'), __('ICEPAY'));
        $resultPage->addBreadcrumb(__('Manage Payment Methods'), __('Manage Payment Methods'));

        return $resultPage;
    }

}