<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Model\ConfigProvider;

class GiroPay extends AbstractConfigProvider
{
    /**
     *
     */
    protected $methodCode = \Icepay\IcpCore\Model\PaymentMethod\GiroPay::CODE;

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $quote = $this->checkoutSession->getQuote();
        return $this->method->isAvailable($quote) ? [
            'payment' => [
                'icepay' => [
                    'giropay' => [
                        'paymentMethodLogoSrc' => $this->getPaymentMethodLogoSrc(),
                        'issuer' => $this->getIssuerList()[0],
                        'redirectUrl' => $this->getMethodRedirectUrl(),
                        'getPaymentMethodDisplayName' => $this->getPaymentMethodDisplayName()
                    ],
                ],
            ],
        ] : [];
    }
}
