<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Controller\Checkout;

use Magento\TestFramework\Inspection\Exception;
use Magento\Checkout\Model\Type\Onepage;

/**
 * Class PlaceOrder
 */
class PlaceOrder extends \Icepay\IcpCore\Controller\AbstractCheckout
{

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $checkoutType = 'Icepay\IcpCore\Model\Checkout\Checkout';


    /**
     * Start ICEPAY Checkout
     *
     * @return void
     */
    public function execute()
    {
        $errorMessage = 'unknown error';

        try {
            $this->initCheckout();

            $customerData = $this->_customerSession->getCustomerDataObject();
            $quoteCheckoutMethod = $this->onepage->getCheckoutMethod();


            if (!$customerData->getId() && ((!$quoteCheckoutMethod || $quoteCheckoutMethod != Onepage::METHOD_REGISTER)
                    && !$this->_objectManager->get('Magento\Checkout\Helper\Data')->isAllowedGuestCheckout(
                        $this->getQuote(),
                        $this->getQuote()->getStoreId()
                    ))
            ) {
                $this->messageManager->addNoticeMessage(
                    __('To check out, please sign in with your email address.')
                );

                $this->_objectManager->get('Magento\Checkout\Helper\ExpressRedirect')->redirectLogin($this);
                $this->_customerSession->setBeforeAuthUrl($this->_url->getUrl('*/*/*', ['_current' => true]));

                return;
            }

            //place order
            $this->checkout->place();

            //Start payment
            $success = $this->checkout->startPayment(
                $this->_url->getUrl('*/*/payment'),
                $this->_url->getUrl('*/*/cancel')
            );
            $url = $this->checkout->getRedirectUrl();
            if ($success && $url) {
                $this->getResponse()->setRedirect($url);
                return;
            }
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            $errorMessage = $e->getMessage();
            $this->messageManager->addExceptionMessage($e, $errorMessage);
        }
        catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->messageManager->addExceptionMessage(
                $e,
                __('Can\'t start Icepay Checkout. ' . $errorMessage)
            );
        }

        if (isset($this->checkout)) {

            // if there is an order - cancel it
            $orderId = $this->getCheckoutSession()->getLastOrderId();
            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderId ? $this->_orderFactory->create()->load($orderId) : false;
            
            $this->cancelOrder($this->checkout->getOrder(), 'Order was cancelled due to a system error: ' . $errorMessage);
            $this->messageManager->addErrorMessage(
                __('Order was cancelled due to a system error.')
            );
            $this->getCheckoutSession()->restoreQuote();
        }

        $this->_redirect('checkout/cart');
    }


}
