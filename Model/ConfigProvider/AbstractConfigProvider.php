<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Model\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;

class AbstractConfigProvider implements ConfigProviderInterface
{

    /**
     * @var \Icepay\IcpCore\Model\PaymentMethod\IcepayAbstractMethod
     */
    protected $method;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->assetRepo = $assetRepo;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        //$quote = $this->checkoutSession->getQuote();
        $issuerList = $this->getIssuerList();
        $propertyName = 'issuers';

        $issuers = '';
        if(count($issuerList) == 1 && $issuerList[0]['code'] == "DEFAULT" ){
            $issuers = $issuerList[0];
            $propertyName = 'issuer';
        } else {
            $issuers = $issuerList;
        }

        return /*$this->method->isAvailable($quote) ?*/ [
            'payment' => [
                'icepay' => [
                    strtolower($this->method->getIcepayMethodCode()) => [
                        'paymentMethodLogoSrc' => $this->getPaymentMethodLogoSrc(),
                        $propertyName => $issuers,
                        'redirectUrl' => $this->getMethodRedirectUrl(),
                        'getPaymentMethodDisplayName' => $this->getPaymentMethodDisplayName()
                    ],
                ],
            ],
        ]; // : [];
    }


    protected function getIssuerList()
    {
        $quote = $this->checkoutSession->getQuote();
        return $this->method->getIssuerList($quote);
    }

    /**
     * Return redirect URL for method
     *
     * @return string
     */
    protected function getMethodRedirectUrl()
    {
        return $this->method->getCheckoutRedirectUrl();
    }

    /**
     * Get payment method logo URL
     *
     * @return string
     */
    protected function getPaymentMethodLogoSrc()
    {
        $logoName = strtolower($this->method->getIcepayMethodCode());
        return $this->assetRepo->getUrl('Icepay_IcpCore::images/methods/'.$logoName.'.png');
    }

    protected function getPaymentMethodDisplayName()
    {
        return $this->method->getPaymentMethodDisplayName();
    }
}
