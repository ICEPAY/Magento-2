<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */
 
namespace Icepay\IcpCore\Controller\Adminhtml\Paymentmethod;

use Magento\Backend\App\Action;

class Edit extends \Icepay\IcpCore\Controller\Adminhtml\Paymentmethod
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Icepay\IcpCore\Model\PaymentmethodFactroy
     */
    protected $_paymentmethodFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Icepay\IcpCore\Model\PaymentmethodFactory $paymentmethodFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Registry $registry,
        \Icepay\IcpCore\Model\PaymentmethodFactory $paymentmethodFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->_paymentmethodFactory = $paymentmethodFactory;

        parent::__construct($context, $resultPageFactory, $resultForwardFactory);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Icepay_IcpCore::paymentmethod_save');
    }

    
    /**
     * Edit ICEPAY Payment Method
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {

        $pmId = $this->getRequest()->getParam('id');

        $paymentmethod = $this->_paymentmethodFactory->create()->load($pmId);

        if ($paymentmethod && $paymentmethod->getId()) {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $paymentmethod->setData($data);
            }

            $this->_coreRegistry->register('paymentmethod', $paymentmethod);

//            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Icepay_IcpCore::paymentmethod')
                ->addBreadcrumb(__('Payment Methods'), __('Payment Methods'))
                ->addBreadcrumb(__('Manage Payment Methods'), __('Manage Payment Methods'));
            $resultPage->getConfig()->getTitle()->prepend(__('Payment Methods'));

            return $resultPage;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }
}
