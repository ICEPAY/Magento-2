<?php

namespace Icepay\IcpCore\Controller\Checkout;

use Magento\Checkout\Model\Type\Onepage;

class Payment extends \Icepay\IcpCore\Controller\AbstractCheckout
{

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $checkoutType = 'Icepay\IcpCore\Model\Checkout\Checkout';


        /**
     * Submit the order
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {

            /**
             * @var /Icepay\IcpCore\Model\Result
             */
            $result = $this->_objectManager->create('Icepay\IcpCore\Model\Result');

            $orderID = preg_replace('/[^a-zA-Z0-9_\s]/', '', strip_tags($this->_request->getParam('OrderID')));

            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderID);

            if (!$order->getId()) {
                $this->logger->debug(sprintf('Order %s not found!', $orderID));
                throw new \Exception(__(sprintf('Order %s not found!', $orderID)));
            }

            if (!$result->validate($order->getStore())) {
                throw new \Exception("Result validation failed");
            }

            if ($result->isPaymentSuccessful()) {
                $this->_redirect('checkout/onepage/success');
                return;
            }


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->debug("Payment.php: ".$e->getMessage());
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
            $this->_redirect('/');
        } catch (\Exception $e) {
            $this->logger->debug("Payment.php: ".$e->getMessage());
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t place the order.')
            );
        }

        $this->_redirect('/');
    }


}
