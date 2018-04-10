<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Icepay\IcpCore\Controller;

use Magento\Checkout\Controller\Express\RedirectLoginInterface;
use Magento\Framework\App\Action\Action as AppAction;
use Magento\Sales\Model\Order;

/**
 * Abstract Checkout Controller
 *
 */
abstract class AbstractCheckout extends AppAction implements RedirectLoginInterface
{

    /**
     * @var \Icepay\IcpCore\Model\Checkout\Checkout
     */
    protected $checkout;

    /**
     * Internal cache of checkout models
     *
     * @var array
     */
    protected $checkoutTypes = [];

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $checkoutType;

    /**
     * @var \Magento\Paypal\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = false;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Icepay\IcpCore\Model\Checkout\Factory
     */
    protected $_checkoutFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_icepaySession;

    /**
     * @var \Magento\Framework\Url\Helper
     */
    protected $_urlHelper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    protected $_cart;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onepage;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Session\Generic $paypalSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Checkout\Model\Type\Onepage $onepage
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Icepay\IcpCore\Model\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Session\Generic $icepaySession,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Type\Onepage $onepage,
        \Psr\Log\LoggerInterface $logger


    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_checkoutFactory = $checkoutFactory;
        $this->_icepaySession = $icepaySession;
        $this->_urlHelper = $urlHelper;
        $this->_customerUrl = $customerUrl;
        $this->_cart = $cart;
        $this->onepage = $onepage;
        $this->logger = $logger;
        parent::__construct($context);
    }



    /**
     * Instantiate quote and checkout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initCheckout()
    {
        $quote = $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__('Quote is empty or has errors')); //TODO:
        }
        //TODO: _config
        if (!isset($this->checkoutTypes[$this->checkoutType])) {
            $parameters = [
                'params' => [
                    'quote' => $quote,
                ],
            ];
            $this->checkoutTypes[$this->checkoutType] = $this->_checkoutFactory->create($this->checkoutType, $parameters);
        }
        $this->checkout = $this->checkoutTypes[$this->checkoutType];
    }


    /**
     * ICEPAY session instance getter
     *
     * @return \Magento\Framework\Session\Generic
     */
    protected function _getSession()
    {
        return $this->_icepaySession;
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->getCheckoutSession()->getQuote();
        }
        return $this->quote;
    }


    /**
     * Returns before_auth_url redirect parameter for customer session
     * @return null
     */
    public function getCustomerBeforeAuthUrl()
    {
        return;
    }

    /**
     * Returns a list of action flags [flag_key] => boolean
     * @return array
     */
    public function getActionFlagList()
    {
        return [];
    }

    /**
     * Returns login url parameter for redirect
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * Returns action name which requires redirect
     * @return string
     */
    public function getRedirectActionName()
    {
        return 'placeorder';
    }


    //TODO: think twice if this function should be here
    protected function cancelOrder($order, $message)
    {
        if ($order && $order->getId() && $order->getQuoteId() == $this->getCheckoutSession()->getQuoteId()) {
            if ($order->getState() != Order::STATE_CANCELED) {
                $order->registerCancellation($message)->save();
            }
            return true;
        }
        return false;
    }



}