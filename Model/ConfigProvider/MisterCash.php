<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Model\ConfigProvider;

class MisterCash extends AbstractConfigProvider
{
    /**
     *
     */
    protected $methodCode = \Icepay\IcpCore\Model\PaymentMethod\MisterCash::CODE;

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $quote = $this->checkoutSession->getQuote();
        return $this->method->isAvailable($quote) ? [
            'payment' => [
                'icepay' => [
                    'mistercash' => [
                        'paymentMethodLogoSrc' => $this->getPaymentMethodLogoSrc(),
                        'issuers' => $this->getIssuerList(),
                        'redirectUrl' => $this->getMethodRedirectUrl(),
                        'getPaymentMethodDisplayName' => $this->getPaymentMethodDisplayName()
                    ],
                ],
            ],
        ] : [];
    }
}
