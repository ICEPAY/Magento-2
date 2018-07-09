<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */
 
namespace Icepay\IcpCore\Model;

use Icepay\IcpCore\Api\Data\PaymentmethodInterface;

require_once(dirname(__FILE__).'/restapi/src/Icepay/API/Autoloader.php');

class Paymentmethod extends \Magento\Framework\Model\AbstractModel implements PaymentmethodInterface
{
    const ENTITY = 'icepay_icpcore_paymentmethod';


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource =
        null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection =
        null,
        array $data = []
    ) {
    
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        $this->_init('Icepay\IcpCore\Model\ResourceModel\Paymentmethod');
    }



    public function getIcepayPaymentMethods($merchantId, $secretCode)
    {

        try {
            $icepay = new \Icepay\API\Client();
            $icepay->setApiSecret($secretCode);
            $icepay->setApiKey($merchantId);
            $icepay->setCompletedURL('...');
            $icepay->setErrorURL('...');
            return $icepay->payment->getMyPaymentMethods();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Retrieve payment method id
     *
     * @return int
     */
    public function getPaymentmethodId()
    {
        return $this->getData(self::PAYMENTMETHOD_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return PaymentmethodInterface
     */
    public function setPaymentmethodId($id)
    {
        return $this->setData(self::PAYMENTMETHOD_ID, $id);
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * Set payment method code
     *
     * @param string $code
     * @return PaymentmethodInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function getIsActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set is active
     *
     * @param bool|int $isActive
     * @return PaymentmethodInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    public function getDisplayName()
    {
        return $this->getData(self::DISPLAY_NAME);
    }

    public function setDisplayName($displayName)
    {
        return $this->setData(self::DISPLAY_NAME, $displayName);
    }

    public function getDisplayPosition()
    {
        return $this->getData(self::DISPLAY_POSITION);
    }

    public function setDisplayPosition($displayPosition)
    {
        return $this->setData(self::DISPLAY_POSITION, $displayPosition);
    }

    public function getRawPmData()
    {
        return $this->getData(self::RAW_PM_DATA);
    }

    public function setRawPmData($rawPmData)
    {
        return $this->setData(self::RAW_PM_DATA, $rawPmData);
    }
}
