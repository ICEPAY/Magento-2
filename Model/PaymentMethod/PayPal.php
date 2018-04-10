<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icepay\IcpCore\Model\PaymentMethod;


use Icepay\IcpCore\Model\Icepay;
use Icepay\IcpCore\Model\Issuer;
use Icepay\IcpCore\Model\Paymentmethod;

class PayPal extends IcepayAbstractMethod
{
    const CODE = 'icepay_icpcore_paypal';
    const PMCODE = 'PAYPAL';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var string
     */
    protected $icepayMethodCode = self::PMCODE;

    /**
     * @var string8
     */
    protected $_formBlockType = 'Magento\Payment\Block\Form\Cc';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Cc';


    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;


    protected $_countryFactory;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Exception\LocalizedExceptionFactory
     */
    protected $_exception;


    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

//    /**
//     * @var Transaction\BuilderInterface
//     */
//    protected $transactionBuilder;

    protected $issuerFactory;


    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Icepay\IcpCore\Model\IssuerFactory $issuerFactory,
        \Icepay\IcpCore\Api\PaymentmethodRepositoryInterface $paymentmethodRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider $countryProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface $transactionManager,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $storeManager,
            $logger,
            $paymentmethodRepository,
            $filterBuilder,
            $searchCriteriaBuilder,
            $countryProvider,
            $moduleList,
            $localeDate,
            $resource,
            $resourceCollection,
            $transactionBuilder,
            $transactionManager,
            $data
        );
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_checkoutSession = $checkoutSession;
        $this->_exception = $exception;
        $this->transactionRepository = $transactionRepository;
        $this->issuerFactory = $issuerFactory;
//        $this->transactionBuilder = $transactionBuilder;

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
        //throw new \Magento\Framework\Validator\Exception(__('Inside Stripe, throwing donuts :]'));
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        /** @var \Magento\Sales\Model\Order\Address $billing */
        $billing = $order->getBillingAddress();
        try {
//            $requestData = [
//                'amount'        => $amount * 100,
//                'currency'      => strtolower($order->getBaseCurrencyCode()),
//                'description'   => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
//                'card'          => [
//                    'number'            => $payment->getCcNumber(),
//                    'exp_month'         => sprintf('%02d',$payment->getCcExpMonth()),
//                    'exp_year'          => $payment->getCcExpYear(),
//                    'cvc'               => $payment->getCcCid(),
//                    'name'              => $billing->getName(),
//                    'address_line1'     => $billing->getStreetLine(1),
//                    'address_line2'     => $billing->getStreetLine(2),
//                    'address_city'      => $billing->getCity(),
//                    'address_zip'       => $billing->getPostcode(),
//                    'address_state'     => $billing->getRegion(),
//                    'address_country'   => $billing->getCountryId(),
//                    // To get full localized country name, use this instead:
//                    // 'address_country'   => $this->_countryFactory->create()->loadByCode($billing->getCountryId())->getName(),
//                ]
//            ];
//            $charge = \Stripe\Charge::create($requestData);
//            $payment
//                ->setTransactionId($charge->id)
//                ->setIsTransactionClosed(0);
        } catch (\Exception $e) {
//            $this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
//            $this->_logger->error(__('Payment capturing error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
        }
        return $this;
    }

    public function getIssuerList($paymentMethodCode = null)
    {
        $list = parent::getIssuerList(PayPal::PMCODE);

        $arr = array();
        foreach($list as $issuer)
        {
            array_push($arr,[
                'name' => $issuer->Description,
                'code' => $issuer->IssuerKeyword,
            ]);
        }

        return $arr;

    }
    
    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see Quote\Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('icepay/checkout/placeorder');
    }
    
    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
//        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
//            return false;
//        }
        return true;
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
        return $this->_placeOrder($payment, $amount);
    }

}