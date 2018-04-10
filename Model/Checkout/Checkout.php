<?php
namespace Icepay\IcpCore\Model\Checkout;

//TODO: replace
require_once(dirname(__FILE__).'/../restapi/src/Icepay/API/Autoloader.php');

use Magento\Customer\Api\Data\CustomerInterface as CustomerDataObject;
use Magento\Customer\Model\AccountManagement;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Quote\Model\Quote\Address;
use Magento\Framework\DataObject;
use Magento\Store\Model\Information as StoreInformation;
use Magento\Store\Model\ScopeInterface;

/**
 * Wrapper that performs ICEPAY Checkout communication
 * Use current ICEPAY payment method instance
 */
class Checkout
{

    /**
     * Gateway actions locked state key
     */
    const ICEPAY_ISSUER_KEY = 'issuer';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Config instance
     *
     * @var PaypalConfig
     */
    protected $_config;
    
    /**
     * ICEPAY Payment Object instance
     *
     * @var \
     */
    protected $paymentObject;

    /**
     * ICEPAY Webservice Object instance
     *
     * @var \
     */
    protected $webserviceObject;

    /**
     * State helper variable
     *
     * @var string
     */
    protected $_redirectUrl = '';

    /**
     * State helper variable
     *
     * @var string
     */
    protected $_checkoutRedirectUrl = '';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Customer ID
     *
     * @var int
     */
    protected $_customerId;


    /**
     * Order
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

//    /**
//     * @var \Magento\Framework\App\Cache\Type\Config
//     */
//    protected $_configCacheType;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutData;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    /**
     * @var \Magento\Paypal\Model\CartFactory
     */
    protected $_cart;

    /**
     * @var \Magento\Checkout\Model\Type\OnepageFactory
     */
    protected $_checkoutOnepageFactory;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $_objectCopyService;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Customer\Model\AccountManagement
     */
    protected $_accountManagement;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

//    /**
//     * @var \Magento\Quote\Api\CartManagementInterface
//     */
//    protected $quoteManagement;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var CountryProvider
     */
    protected $countryProvider;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Paypal\Model\Info $paypalInfo
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param \Magento\Checkout\Model\Type\OnepageFactory $onepageFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param DataObject\Copy $objectCopyService
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param AccountManagement $accountManagement
     * @param OrderSender $orderSender
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param array $params
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Type\OnepageFactory $onepageFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider $countryProvider,
        AccountManagement $accountManagement,
        OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        $params = []
    ) {
        $this->cartManagement = $cartManagement;
        $this->_customerUrl = $customerUrl;
        $this->_taxData = $taxData;
        $this->_checkoutData = $checkoutData;
//        $this->_configCacheType = $configCacheType;
        $this->_logger = $logger;
        $this->_localeResolver = $localeResolver;
        $this->_storeManager = $storeManager;
        $this->_cart = $cart;
        $this->_checkoutOnepageFactory = $onepageFactory;
        $this->_objectCopyService = $objectCopyService;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerRepository = $customerRepository;
        $this->countryProvider = $countryProvider;
        $this->_encryptor = $encryptor;
        $this->_messageManager = $messageManager;
        $this->orderSender = $orderSender;
        $this->_accountManagement = $accountManagement;
        $this->quoteRepository = $quoteRepository;
        $this->totalsCollector = $totalsCollector;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = isset($params['session'])
            && $params['session'] instanceof \Magento\Customer\Model\Session ? $params['session'] : $customerSession;

        if (isset($params['quote']) && $params['quote'] instanceof \Magento\Quote\Model\Quote) {
            $this->quote = $params['quote'];
        } else {
            throw new \Exception('Quote instance is required.');
        }
    }


    /**
     * Setter for customer
     *
     * @param CustomerDataObject $customerData
     * @return $this
     */
    public function setCustomerData(CustomerDataObject $customerData)
    {
        $this->quote->assignCustomer($customerData);
        $this->_customerId = $customerData->getId();
        return $this;
    }



    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }


    /**
     * Start checkout on ICEPAY
     *
     * @param string $returnUrl
     * @param string $cancelUrl
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startPayment($returnUrl, $cancelUrl)
    {
//        $this->quote->collectTotals();
//
//        if (!$this->quote->getGrandTotal()) {
//            throw new \Magento\Framework\Exception\LocalizedException(
//                __(
//                    'ICEPAY can\'t process orders with a zero balance due. '
//                    . 'To finish your purchase, please go through the standard checkout process.'
//                )
//            );
//        }
//
//        $this->quote->reserveOrderId();
//        $this->quoteRepository->save($this->quote);


        // check if there is an order
        $orderId = $this->_getCheckoutSession()->getLastOrderId();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $orderId ? $this->_orderFactory->create()->load($orderId) : false;
        if ($order && $order->getId() && $order->getQuoteId() == $this->_getCheckoutSession()->getQuoteId()) {

            $this->quote = $this->quoteRepository->get($order->getQuoteId()); //TODO


                /**
                 * @var \Magento\Quote\Model\Quote\Payment
                 */
            $payment = $this->quote->getPayment();

            //get ICEPAY payment method code
            $icepayCode = $payment->getMethodInstance()->getIcepayMethodCode();
            //get issuer for ICEPAY payment method
            $issuer = addslashes(htmlspecialchars($this->quote->getPayment()->getAdditionalInformation(self::ICEPAY_ISSUER_KEY)));

            $checkoutLanguage = $this->getCheckoutLanguage();

            $countryCode = $this->countryProvider->getCountry($this->quote);

            //Get module configuration settings
            $merchantId = $this->_scopeConfig->getValue('payment/icepay_settings/merchant_id', ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
            $secretCode = $this->_scopeConfig->getValue('payment/icepay_settings/merchant_secret', ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
            $secretCode = $this->_encryptor->decrypt($secretCode);

            // prepare ICEPAY Payment Object
            $this->getIcepayApiPaymentObject();

            $this->paymentObject->setAmount($this->quote->getBaseGrandTotal() * 100)
                ->setCountry($countryCode)
                ->setLanguage($checkoutLanguage)
                ->setIssuer($issuer)
                ->setPaymentMethod($icepayCode)
                ->setDescription('Merchant ' . $merchantId . ' OrderID ' . $order->getIncrementId())
                ->setCurrency($this->quote->getBaseCurrencyCode())
                ->setOrderID($order->getIncrementId())
                ->setReference('Order: ' . $order->getIncrementId(). ', Customer: ' . $this->quote->getCustomerEmail());

            // prepare ICEPAY Webservice Object
            $this->getIcepayApiWebserviceObject();

            $this->webserviceObject
                ->setMerchantID($merchantId)
                ->setSecretCode($secretCode)
                ->setSuccessURL($returnUrl)
                ->setErrorURL($cancelUrl)
                ->setupClient();

//        try
//        {
            $transactionObj = $this->webserviceObject->checkOut($this->paymentObject);
            $this->_setRedirectUrl($transactionObj->getPaymentScreenURL());
//        } catch (\Exception $e) {
//            //TODO: error message, localized exception
//            return false;
//        }

            $payment = $this->quote->getPayment();
            $payment->save();

            return true;
        }
    }


    /**
     * Create payment redirect url
     * @return void
     */
    protected function _setRedirectUrl($redirectUrl)
    {
        $this->_redirectUrl = $redirectUrl;
    }

    /**
     * Determine whether redirect somewhere specifically is required
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }


    /**
     * @return \Icepay_PaymentObject
     */
    protected function getIcepayApiPaymentObject()
    {
        if (null === $this->paymentObject)
        {
            $this->paymentObject = $this->_objectManager->get('Icepay_PaymentObject');
        }
        return $this->paymentObject;
    }

    /**
     * @return \Icepay_Webservice_Pay
     */
    protected function getIcepayApiWebserviceObject()
    {
        if (null === $this->webserviceObject) {
            $this->webserviceObject = $this->_objectManager->get('Icepay_Webservice_Pay');
        }
        return $this->webserviceObject;
    }



    public function getCheckoutLanguage()
    {
        return substr($this->_localeResolver->getLocale(), 0, 2);
    }

    /**
     * Place the order
     *
     * @throws \Exception
     */
    public function place()
    {

        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }

        $this->quote->collectTotals();
        $orderId = $this->cartManagement->placeOrder($this->quote->getId());
//        $order = $this->cartManagement->submit($this->quote);

        if (!$orderId) {
            return;
        }

//        $this->_order = $order;
        $this->_order = $this->_orderFactory->create()->load($orderId);
    }



    /**
     * Return order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get checkout method
     *
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$this->quote->getCheckoutMethod()) {
            if ($this->_checkoutData->isAllowedGuestCheckout($this->quote)) {
                $this->quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $this->quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $this->quote->getCheckoutMethod();
    }

    /**
     * Get customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }


    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    protected function prepareGuestQuote()
    {
        $quote = $this->quote;
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $this;
    }


}
