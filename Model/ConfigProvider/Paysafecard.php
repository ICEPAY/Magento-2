<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Model\ConfigProvider;

class Paysafecard extends AbstractConfigProvider
{
    /**
     *
     */
    protected $methodCode = \Icepay\IcpCore\Model\PaymentMethod\Paysafecard::CODE;

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'icepay' => [
                    'paysafecard' => [
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
