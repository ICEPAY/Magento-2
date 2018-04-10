<?php


namespace Icepay\IcpCore\Model;

//TODO: replace
require_once(dirname(__FILE__) . '/restapi/src/Icepay/API/Autoloader.php');
use Icepay\IcpCore\Api\PostbackNotificationInterface;
use Magento\Store\Model\ScopeInterface;
use Icepay_StatusCode;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;


class PostbackNotification implements PostbackNotificationInterface
{


    /**
     * @var Icepay_Postback
     */
    protected $icepayPostback;

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
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Framework\Webapi\Request $request
     */
    public $request;

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
        \Magento\Sales\Model\Order $order,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        \Magento\Framework\Webapi\Request $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        LoggerInterface $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->logger = $logger;

        $this->icepayPostback = $this->objectManager->create('Icepay_Postback');



    }


    public function processGet()
    {
            return "success";
    }

    public function processPostbackNotification()
    {

        try {

            $this->logger->debug("*******[ICEPAY] Postback\Notification*******");
            $this->logger->debug('request => ' . print_r($this->request, true));

            $orderID = preg_replace('/[^a-zA-Z0-9_\s]/', '', strip_tags($this->request->getParam('OrderID')));

            $this->order->loadByIncrementId($orderID);

            if (!$this->order->getId()) {
                $this->logger->debug(sprintf('Order %s not found!', $orderID));

                //throw NoSuchEntityException::singleField('orderID', $orderID);
                throw new \Magento\Framework\Webapi\Exception(
                    __(sprintf('Order %s not found!', $orderID)),
                    0,
                    \Magento\Framework\Webapi\Exception::HTTP_NOT_FOUND
                );
            };

            if (!$this->validate($this->order->getStore())) {
                $this->logger->debug(sprintf('Postback inicialization\validation failed.  %s ', print_r($this->request->getPost(), true)));

                throw new \Magento\Framework\Webapi\Exception(
                    __('Postback inicialization\validation failed.'),
                    0,
                    \Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED
                );
            }

            $this->order->loadByIncrementId($this->icepayPostback->getOrderID());

            $currentIcepayOrderStatus = $this->getIcepayOrderStatus($this->order->getStatus());

            if(($currentIcepayOrderStatus === "NEW" || $this->icepayPostback->canUpdateStatus($currentIcepayOrderStatus)) && $this->icepayPostback->getStatus() !== $currentIcepayOrderStatus) {
                switch ($this->icepayPostback->getStatus()) {
                    case Icepay_StatusCode::OPEN:
                        $this->order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                        $this->order->setStatus('icepay_icpcore_open');
                        $this->orderSender->send($this->order);
                        $this->order->save();

                        $history = $this->order->addStatusHistoryComment(__(
                            'Transaction status has changed to OPEN.'
                        ));
                        $history->save();

                        break;
                    case Icepay_StatusCode::SUCCESS:
                        $this->order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $this->order->setStatus('icepay_icpcore_ok');
                        $this->order->save();

                        $history = $this->order->addStatusHistoryComment(__(
                            'Order has been paid successfully.'
                        ));
                        $history->save();

                        break;
                    case Icepay_StatusCode::ERROR:
                        $this->order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                        $this->order->setStatus('icepay_icpcore_error');

                        if ($this->order->canCancel()) {
                            $this->order->cancel();
                            $this->order->setStatus('canceled');
                        }
                        $this->orderSender->send($this->order);
                        $this->order->save();

                        $history = $this->order->addStatusHistoryComment(__(
                            'Order was cancelled due to a system error.'
                        ));
                        $history->save();

                        break;
                }
            }

            if(!$this->order->getIsNotified())
            {
                $this->orderSender->send($this->order, true);

                $history = $this->order->addStatusHistoryComment(__(
                    'Confirmed the order to the customer via email.'
                ));
                $history->setIsCustomerNotified(true);
                $history->save();
            }

            if ($this->order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING && $this->order->canInvoice() && !$this->order->hasInvoices()) {

                /**
                 * @var \Magento\Sales\Model\Order\Payment $payment
                 */
                $payment = $this->order->getPayment();
                $payment->registerCaptureNotification($this->order->getGrandTotal());
                $payment->save();
                $this->order->save();

                foreach ($this->order->getInvoiceCollection() as $invoice) {
                    $this->invoiceSender->send($invoice, true);
                    $this->order->addStatusHistoryComment(
                        __('Notified customer about invoice #%1.', $invoice->getId())
                    )
                        ->setIsCustomerNotified(true)
                        ->save();
                }
            }


        }
        catch (\Magento\Framework\Webapi\Exception $e)
        {
            $this->logger->error($e->getMessage());
            throw $e;
        }
        catch (\Exception $e) {
            $this->logger->critical($e);

            throw new \Magento\Framework\Webapi\Exception(
                __('Internal Error'),
                0,
                \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
            );
        }
    }


    protected function validate($store)
    {
        $merchantId = $this->scopeConfig->getValue('payment/icepay_settings/merchant_id', ScopeInterface::SCOPE_STORE, $store);
        $secretCode = $this->encryptor->decrypt($this->scopeConfig->getValue('payment/icepay_settings/merchant_secret', ScopeInterface::SCOPE_STORE, $store));

        $this->icepayPostback->setMerchantID($merchantId)->setSecretCode($secretCode);

        return (bool) $this->icepayPostback->validate();
    }


    /**
     * Get ICEPAY order status by Magento order status
     */
    private function getIcepayOrderStatus($magentoOrderStatus)
    {
        switch ($magentoOrderStatus)
        {
            case "icepay_icpcore_new": return "NEW";
            case "icepay_icpcore_open": return Icepay_StatusCode::OPEN;
            case "icepay_icpcore_ok": return Icepay_StatusCode::SUCCESS;
            case "icepay_icpcore_error": return Icepay_StatusCode::ERROR;
            default:
                throw new \Magento\Framework\Webapi\Exception(
                __(sprintf('No mapping found for status: ', $magentoOrderStatus)),
                0,
                \Magento\Framework\Webapi\Exception::HTTP_NOT_FOUND
            );
        }
    }

}