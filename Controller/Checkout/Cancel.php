<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */
 
namespace Icepay\IcpCore\Controller\Checkout;

use Magento\Framework\Controller\ResultFactory;

class Cancel extends \Icepay\IcpCore\Controller\AbstractCheckout
{
    /**
     * Cancel ICEPAY Checkout
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {

            //TODO: check if payment was canceled or failed


            // if there is an order - cancel it
            $orderId = $this->getCheckoutSession()->getLastOrderId();
            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderId ? $this->_orderFactory->create()->load($orderId) : false;

            $message = 'Checkout has been canceled.';
            if ($order && $this->cancelOrder($order, $message)) {
                $message = 'Checkout and Order have been canceled.';
            }

            $this->messageManager->addSuccessMessage(__($message));
            $this->logger->debug(sprintf($message.' Order ID: %s', $orderId ? '' : $orderId));

            $this->getCheckoutSession()->restoreQuote();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            $this->logger->debug("Cancel.php: " . $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to cancel ICEPAY Checkout'));
            $this->logger->debug("Cancel.php: " . $e->getMessage());
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('checkout/cart');
    }


}
