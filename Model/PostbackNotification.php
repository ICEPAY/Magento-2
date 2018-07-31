<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Model;

//TODO: replace
require_once(dirname(__FILE__) . '/restapi/src/Icepay/API/Autoloader.php');
use Icepay\IcpCore\Api\PostbackNotificationInterface;
use Magento\Store\Model\ScopeInterface;
use Icepay_StatusCode;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;


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
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Model\Order
     *
     * TODO: \Magento\Sales\Api\Data\OrderInterface $order
     *
     */
    protected $order;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var OrderCommentSender
     */
    protected $orderCommentSender;

    /**
     * @var \Magento\Framework\Webapi\Request $request
     */
    public $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     */
    protected $transactionBuilder;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\OrderRepository  $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria
     * @param OrderSender $orderSender
     * @param OrderCommentSender $orderCommentSender
     * @param \Magento\Framework\Webapi\Request $request
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria,
        OrderSender $orderSender,
        OrderCommentSender $orderCommentSender,
        \Magento\Framework\Webapi\Request $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        LoggerInterface $logger,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
    
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->order = $order;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteria;
        $this->orderSender = $orderSender;
        $this->orderCommentSender = $orderCommentSender;
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->transactionBuilder = $transactionBuilder;

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
                throw new \Magento\Framework\Webapi\Exception(
                    __(sprintf('Order %s not found!', $orderID)),
                    0,
                    \Magento\Framework\Webapi\Exception::HTTP_NOT_FOUND
                );
            };

            if (!$this->validate($this->order->getStore())) {
                $this->logger->debug(sprintf('Postback initialization\validation failed.  %s ', print_r($this->request->getPost(), true)));

                throw new \Magento\Framework\Webapi\Exception(
                    __('Postback initialization\validation failed.'),
                    0,
                    \Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED
                );
            }

            //TODO: refactor to service contracts (repositories)
            $this->order->loadByIncrementId($this->icepayPostback->getOrderID());

            $transactionId = (string)filter_input(INPUT_POST, 'TransactionID');
            $currentIcepayOrderStatus = $this->getIcepayOrderStatus($this->order->getStatus());
            $amount = (string)filter_input(INPUT_POST, 'Amount');

            $this->logger->debug(sprintf('Order ID: %s, Transaction ID: %s, Current Magento Order Status: %s, Current ICEPAY Order Status: %s',
                $this->order->getId(), $transactionId, $this->order->getStatus(), $currentIcepayOrderStatus));

            if (($currentIcepayOrderStatus === "NEW" || $this->icepayPostback->canUpdateStatus($currentIcepayOrderStatus))
                && $this->icepayPostback->getStatus() !== $currentIcepayOrderStatus) {
                switch ($this->icepayPostback->getStatus()) {
                    case Icepay_StatusCode::OPEN:
                        $this->order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                        $this->order->setStatus('icepay_icpcore_open');

                        $this->createTransaction($this->order, $transactionId, $amount, $this->icepayPostback->getTransactionString());

                        $this->order->addStatusHistoryComment(__(
                            'Order status has changed to OPEN.'
                        ))->save();

                        $this->order->save();

                        $this->logger->debug('Order status has changed to OPEN');
                        break;
                    case Icepay_StatusCode::SUCCESS:    
                        $this->order->setState(\Magento\Sales\Model\Order::STATE_NEW);
                        $this->order->setStatus('icepay_icpcore_ok');

                        $this->createTransaction($this->order, $transactionId, $amount, $this->icepayPostback->getTransactionString());

                        $this->order->addStatusHistoryComment(__(
                            'Order has been paid successfully.'
                        ))->save();

                        if ($this->order->getCanSendNewEmailFlag()) {
                            $this->orderSender->send($this->order);

                            $history = $this->order->addStatusHistoryComment(__(
                                'Confirmed the order to the customer via email.'
                            ));
                            $history->setIsCustomerNotified(true);
                            $history->save();

                            $this->logger->debug('Confirmed the order to the customer via email.');
                        }

                        $this->order->save();

                        $this->logger->debug('Order status has changed to OK');
                        break;
                    case Icepay_StatusCode::ERROR:
                        $this->order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                        $this->order->setStatus('icepay_icpcore_error');

                        if ($this->order->canCancel()) {
                            $this->order->cancel();
                            $this->order->setStatus('canceled');
                        }

                        $this->createTransaction($this->order, $transactionId, $amount, $this->icepayPostback->getTransactionString());

                        $this->order->addStatusHistoryComment(__(
                            'Order was cancelled due to a system error.'
                        ))->save();

                        $this->order->save();

                        //TODO:
//                        $this->orderCommentSender->send($order, $notify, $comment);

                        $this->logger->debug('Order status has changed to ERROR');
                        break;
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

    private function createTransaction($order, $transactionId, $paymentAmount, $paymentData)
    {
        try {
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->setTransactionId($transactionId);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS =>  $paymentData]
            );
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
            //$order->getGrandTotal()
                $paymentAmount
            );

            $message = __('Payment amount is %1.', $formatedPrice);
            $trans = $this->transactionBuilder;
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS =>  $paymentData]
                )
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_PAYMENT);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
//            $order->save();

            return  $transaction->save()->getTransactionId();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
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