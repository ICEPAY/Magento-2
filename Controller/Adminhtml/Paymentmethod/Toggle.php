<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Controller\Adminhtml\Paymentmethod;

class Toggle extends \Icepay\IcpCore\Controller\Adminhtml\Paymentmethod
{
    /**
     * @var \Icepay\IcpCore\Model\PaymentmethodFactory
     */
    protected $paymentmethodFactory;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Icepay\IcpCore\Model\PaymentmethodFactory $paymentmethodFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Icepay\IcpCore\Model\PaymentmethodFactory $paymentmethodFactory
    ) {
    
        $this->paymentmethodFactory = $paymentmethodFactory;
        parent::__construct($context, $resultPageFactory, $resultForwardFactory);
    }



    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();


        $pmId = $this->getRequest()->getParam('id');

        $paymentmethod = $this->paymentmethodFactory->create()->load($pmId);

        if ($paymentmethod && $paymentmethod->getId()) {
            try {
                $paymentmethod->setIsActive(!$paymentmethod->getIsActive());
                $paymentmethod->save();
                $this->messageManager->addSuccess(__('Payment method successfully saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Error with editing payment method action.'));
            }
        }

        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Icepay_IcpCore::paymentmethod_save');
    }
}
