<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Controller\Adminhtml\Paymentmethod;

use Icepay\IcpCore\Api\Data;
use Icepay\IcpCore\Api;


class Sync extends \Icepay\IcpCore\Controller\Adminhtml\Paymentmethod
{

    /**
     * @var \Icepay\IcpCore\Api\PaymentmethodRepositoryInterface
     */
    protected $paymentmethodRepository;

    /**
     * @var \Icepay\IcpCore\Api\Data\PaymentmethodInterfaceFactory
     */
    protected $paymentmethodDataFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    protected $issuerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Icepay\IcpCore\Api\PaymentmethodRepositoryInterface $paymentmethodRepository
     * @param \Icepay\IcpCore\Api\Data\PaymentmethodInterfaceFactory $paymentmethodDataFactory
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Icepay\IcpCore\Model\PaymentmethodFactory $paymentmethodFactory
     * @param \Icepay\IcpCore\Model\IssuerFactory $issuerFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Icepay\IcpCore\Api\PaymentmethodRepositoryInterface $paymentmethodRepository,
        \Icepay\IcpCore\Api\Data\PaymentmethodInterfaceFactory $paymentmethodDataFactory,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Icepay\IcpCore\Model\PaymentmethodFactory $paymentmethodFactory,
        \Icepay\IcpCore\Model\IssuerFactory $issuerFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    )
    {
        parent::__construct($context, $resultPageFactory, $resultForwardFactory);
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->paymentmethodFactory = $paymentmethodFactory;
        $this->paymentmethodRepository = $paymentmethodRepository;
        $this->paymentmethodDataFactory = $paymentmethodDataFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->issuerFactory = $issuerFactory;
        $this->_encryptor = $encryptor;
    }


    public function execute()
    {

        $storeIds = array();
        $redirectParams = [];

        if ($this->getRequest()->getParam('store')) {
            $storeIds[] = (int)$this->getRequest()->getParam('store');
            $redirectParams = ['store' => (int)$this->getRequest()->getParam('store')];
        } else if ($this->getRequest()->getParam('website')) {
            $website = $this->getStoreManager()->getWebsite((int)$this->getRequest()->getParam('website'));
            $storeIds = array_merge($storeIds, $website->GetStoreIds());
            $redirectParams = ['website' => (int)$this->getRequest()->getParam('website')];
        } else $storeIds[] = 0;

        foreach ($storeIds as $storeId) {

            $store = $this->getStoreManager()->getStore($storeId);

            $merchantId = $this->_scopeConfig->getValue('payment/icepay_settings/merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $secretCode = $this->_encryptor->decrypt($this->_scopeConfig->getValue('payment/icepay_settings/merchant_secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId));

            $paymentMethods = null;
            if (empty($merchantId) || empty($secretCode)) {
                $this->messageManager->addErrorMessage(__('Merchant ID and Secret code settings are missing for %1.', $store->getName()));
                continue;
            }

            $paymentMethod = $this->_objectManager->create('Icepay\IcpCore\Model\Paymentmethod');
            $paymentMethods = $paymentMethod->getIcepayPaymentMethods($merchantId, $secretCode);

            if (!isset($paymentMethods->PaymentMethods) || !is_array($paymentMethods->PaymentMethods)) {
                $this->messageManager->addErrorMessage(__('Could not retrieve payment method configuration for %1. Please check if Merchant ID and Secret Code settings are valid.', $store->getName()));
                continue;
            }

            $filter = $this->filterBuilder->setField('store_id')->setValue($storeId)->setConditionType('eq')->create();
            $collection = (array)($this->paymentmethodRepository->getList(
                $this->searchCriteriaBuilder->addFilters([$filter])->create()
            )->getItems());

            $paymentmethodsDeleted = 0;
            foreach ($collection as $pmethod) {
                if ($this->paymentmethodRepository->deleteById($pmethod['paymentmethod_id']))
                    $paymentmethodsDeleted++;

            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted from %2.', $paymentmethodsDeleted, $store->getName()));

            $paymentmethodsAdded = 0;
            foreach ($paymentMethods->PaymentMethods as $paymentMethodDescription) {

                $paymentmethod = $this->paymentmethodDataFactory->create();
                $paymentmethod->setCode($paymentMethodDescription->PaymentMethodCode);
                $paymentmethod->setName($paymentMethodDescription->Description);
                $paymentmethod->setDisplayName($paymentMethodDescription->Description);
                $paymentmethod->setDisplayPosition($paymentmethodsAdded++);
                $paymentmethod->setStoreId($storeId);
                $paymentmethod->setWebsiteId($store->getWebsiteId());
                $paymentmethod->setRawPmData(serialize($paymentMethods->PaymentMethods));
                $this->paymentmethodRepository->save($paymentmethod);


                $arrCountry = array();
                $arrCurrency = array();
                $arrMinimum = array();
                $arrMaximum = array();

                //TODO: repository, refactoring
                foreach ($paymentMethodDescription->Issuers as $issuerDescription) {
                    foreach ($issuerDescription->Countries as $country) {
                        array_push($arrCountry, trim($country->CountryCode));
                        array_push($arrMinimum, $country->MinimumAmount);
                        array_push($arrMaximum, $country->MaximumAmount);
                        $arrCurrency = $this->addCurrencies($arrCurrency, explode(',', $country->Currency));
                    }

                    $issuer = $this->issuerFactory->create();
                    $issuer->setPaymentmethodId($paymentmethod->getId());
                    $issuer->setCode($issuerDescription->IssuerKeyword);
                    $issuer->setName($issuerDescription->Description);
                    $issuer->setCurrencyList($this->arrEncode($arrCurrency));
                    $issuer->setCountryList($this->arrEncode($arrCountry));
//                $issuer->setLanguageList($this->arrEncode());
                    $issuer->setMinimumAmountList($this->arrEncode($arrMinimum));
                    $issuer->setMaximumAmountList($this->arrEncode($arrMaximum));
                    $issuer->save();
                }
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were added to %2.', $paymentmethodsAdded, $store->getName()));

        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index/', $redirectParams);

    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Icepay_IcpCore::paymentmethod_save');
    }


    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
        }
        return $this->storeManager;
    }

    private function arrEncode($arr)
    {
        return serialize($arr);
    }

    private function addCurrencies($arr, $currencyArr)
    {
        foreach ($currencyArr as $currency) {
            array_push($arr, trim($currency));
        }
        return $arr;
    }
}