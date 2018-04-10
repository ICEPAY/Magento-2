<?php
namespace Icepay\IcpCore\Model;

//TODO: replace
require_once(dirname(__FILE__).'/restapi/src/Icepay/API/Autoloader.php');

use Icepay_StatusCode;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;


class Result    
{

    /**
     * @var Icepay_Result
     */
    protected $icepayResult;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Sales\Model\Order $order
     * @param OrderSender $orderSender
     * @param \Magento\Framework\Webapi\Request $request
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
//        \Magento\Sales\Model\Order $order,
//        OrderSender $orderSender,
//        InvoiceSender $invoiceSender,
//        \Magento\Framework\Webapi\Request $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        LoggerInterface $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
//        $this->order = $order;
//        $this->orderSender = $orderSender;
//        $this->invoiceSender = $invoiceSender;
//        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->logger = $logger;

        $this->icepayResult = $this->objectManager->create('Icepay_Result');

    }

    public function validate($store)
    {

        $merchantId = $this->scopeConfig->getValue('payment/icepay_settings/merchant_id', ScopeInterface::SCOPE_STORE, $store);
        $secretCode = $this->encryptor->decrypt($this->scopeConfig->getValue('payment/icepay_settings/merchant_secret', ScopeInterface::SCOPE_STORE, $store));

        $this->icepayResult->setMerchantID($merchantId)->setSecretCode($secretCode);

        return (bool) $this->icepayResult->validate();

        //throw new \Exception('Failed to validete ICEPAY result');
    }


    public function isPaymentSuccessful()
    {
        $status = $this->icepayResult->getStatus();
        if ($status === Icepay_StatusCode::SUCCESS || $status === Icepay_StatusCode::OPEN)
            return true;

        return false;

    }

}