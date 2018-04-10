<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Icepay\IcpCore\Model\PaymentMethod;

require_once(dirname(__FILE__).'/../restapi/src/Icepay/API/Autoloader.php');

use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;


class IcepayAbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Icepay\IcpCore\Api\PaymentmethodRepositoryInterface
     */
    protected $paymentmethodRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    protected $icepayMethodCode;

    protected $paymentMethodInformation;

    protected $paymentMethod;
    /**
     * @var Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var ManagerInterface
     */
    protected $transactionManager;

    /**
     * @var CountryProvider
     */
    protected $countryProvider;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\Method\Logger $logger,
        \Icepay\IcpCore\Api\PaymentmethodRepositoryInterface $paymentmethodRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider $countryProvider,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface $transactionManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_storeManager = $storeManager;
        $this->paymentmethodRepository = $paymentmethodRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionManager = $transactionManager;
        $this->countryProvider = $countryProvider;

        $this->_moduleList = $moduleList;
        $this->_localeDate = $localeDate;

    }


    private function initPaymentMethodInformation()
    {
        $store = $this->getStoreManager()->getStore();

        $pmethodFilter = $this->filterBuilder->setField('code')->setValue($this->icepayMethodCode)->setConditionType('eq')->create();

        $storeFilters = array(
            $this->filterBuilder->setField('store_id')->setValue((int)$store->getId())->setConditionType('eq')->create(),
            $this->filterBuilder->setField('store_id')->setValue(0)->setConditionType('eq')->create()
        );

        $this->searchCriteriaBuilder->addFilters([$pmethodFilter]);
        $this->searchCriteriaBuilder->addFilters($storeFilters);

        $collection = (array)($this->paymentmethodRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems());

        if(1 == count($collection))
        {
            $this->paymentMethod = reset($collection);
        }
        else {
            foreach ($collection as $pmethod) {
                if (0 != $pmethod->getStoreId()) {
                    $this->paymentMethod = $pmethod;
                    break;
                }
            }
        }

        if($this->paymentMethod)
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $mt = $objectManager->create('Icepay_Webservice_Paymentmethod');
            $pmData = $this->paymentMethod->getRawPmData();
            $method = $mt->loadFromArray(unserialize($pmData));
            $this->paymentMethodInformation = $method;
        }
    }


    /**
     * Determine method availability based on quote amount, country and currency
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        if ($quote)
        {
//            if ($this->paymentMethodInformation == null) {
//                $this->initPaymentMethodInformation();
//            }
            $countryCode = $this->countryProvider->getCountry($quote);

            $pMethod = $this->paymentMethodInformation
                ->filterByCurrency($quote->getBaseCurrencyCode())
                ->filterByCountry($countryCode)
                ->filterByAmount($quote->getBaseGrandTotal() * 100);

            $available = false;
            foreach ($pMethod->getFilteredPaymentmethods() as $value)
            {
                if ($value->PaymentMethodCode === $this->icepayMethodCode)
                {
                    $available = true;
                    break;
                }
            }
            if(!$available) return false;
        }

        return parent::isAvailable($quote);
    }


    protected function getIssuerList($paymentMethodCode)
    {
        if($this->paymentMethodInformation == null)
        {
            $this->initPaymentMethodInformation();
        }

        $pMethod = $this->paymentMethodInformation->selectPaymentMethodByCode($paymentMethodCode);
        return $pMethod->getIssuers();
    }


    public function getIcepayMethodCode()
    {
        return $this->icepayMethodCode;
    }


    public function assignData(\Magento\Framework\DataObject $data)
    {
        $additionalData = $data->getAdditionalData();

        if (!is_array($data->getAdditionalData())) {
            return $this;
        }
        $additionalData = new DataObject($additionalData);

        $infoInstance = $this->getInfoInstance();
        $infoInstance->setAdditionalInformation(
            'issuer',
            $additionalData->getData('issuer')
        );
        return $this;


    }

    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        throw new \Magento\Framework\Validator\Exception(__('Payment Capture is not supported in this version'));
    }

    /**
     * Authorize payment
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        throw new \Magento\Framework\Validator\Exception(__('Payment Authorize is not supported in this version'));
    }

    /**
     * Order payment
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
//        $icepayTransactionData = $this->_checkoutSession->getIcepayTransactionData();
//        if (!isset($icepayTransactionData)) {
//            throw new LocalizedException(__('ICEPAY result is not set. Order is canceled or already created.'));
//        } else {
//            $this->_importToPayment($icepayTransactionData, $payment);
//        }
        
        $order = $payment->getOrder();
        //$orderTransactionId = $payment->getTransactionId().'-order';

        $formattedPrice = $order->getBaseCurrency()->formatTxt($amount);

        
            $message = __('Ordered amount of %1', $formattedPrice);
            $state = \Magento\Sales\Model\Order::STATE_NEW;
            $status = 'icepay_icpcore_new';


//        $transactionId = $this->transactionManager->generateTransactionId($payment, Transaction::TYPE_ORDER);


        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
//            ->setTransactionId($transactionId)
            ->build(Transaction::TYPE_ORDER);
        $payment->addTransactionCommentsToOrder($transaction, $message);

        $order->setState($state)
            ->setStatus($status);

        $payment->setSkipOrderProcessing(true);

        return $this;
    }


    /**
     * Import payment info to payment
     *
     * @param Icepay_Result $icepayResult
     * @param Payment $payment
     * @return void
     */
    protected function _importToPayment($icepayResult, $payment)
    {
        
        $payment->setTransactionId(
            $icepayResult->transactionID
        )->setIsTransactionClosed(
            0
        );

        //TODO: refactor
        if($icepayResult->status === "OPEN") {
            $payment->setIsTransactionPending(true);
        }
        else if($icepayResult->status === "OK") {
            $payment->setIsTransactionApproved(true);
        }
        else
        {
            $payment->setIsTransactionApproved(false);
            $payment->setIsTransactionPending(false); //TODO: Check if redundant
        }

    }


    public function getPaymentMethodDisplayName()
    {
        if($this->paymentMethodInformation == null)
        {
            $this->initPaymentMethodInformation();
        }
        return $this->paymentMethod->getDisplayName();
    }
    
    
    /**
     * is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {

        if ($this->paymentMethod == null) {
            $this->initPaymentMethodInformation();
        }

        if ($this->paymentMethod != null) {
            return $this->paymentMethod->getIsActive();
        }
    }


    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    private function getStoreManager()
    {
        if (null === $this->_storeManager) {
            $this->_storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
        }
        return $this->_storeManager;
    }


}