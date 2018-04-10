<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Controller\Adminhtml\Paymentmethod;

class Save extends \Icepay\IcpCore\Controller\Adminhtml\Paymentmethod
{
    protected $paymentmethodFactory;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $storeManager;
    protected $formKeyValidator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Icepay\IcpCore\Model\PaymentmethodFactory $paymentmethodFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->formKeyValidator = $context->getFormKeyValidator();
        $this->paymentmethodFactory = $paymentmethodFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context, $resultPageFactory, $resultForwardFactory);
    }



    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setRefererUrl();
        }

        $pmId = $this->getRequest()->getParam('id');
        $pmEnabled = ($this->getRequest()->getParam('is_active') == 1);
        $pmDisplayName = $this->getRequest()->getParam('display_name');

        $paymentmethod = $this->paymentmethodFactory->create()->load($pmId);

        if ($paymentmethod && $paymentmethod->getId()) {
            try {

                $paymentmethod->setIsActive($pmEnabled);
                $paymentmethod->setDisplayName($pmDisplayName);

                $paymentmethod->save();
                $this->messageManager->addSuccess(__('Payment method successfully saved.'));

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $paymentmethod->getId()]);
                }

            } catch (Exception $e) {
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
