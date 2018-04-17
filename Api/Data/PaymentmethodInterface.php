<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Api\Data;

/**
 * Payment Method interface.
 * @api
 */
interface PaymentmethodInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const PAYMENTMETHOD_ID = 'paymentmethod_id';
    const CODE = 'code';
    const IS_ACTIVE = 'is_active';
    const STORE_ID = 'store_id';
    const WEBSITE_ID = 'website_id';
    const NAME = 'name';
    const DISPLAY_NAME = 'display_name';
    const DISPLAY_POSITION = 'display_position';
    const RAW_PM_DATA = 'raw_pm_data';
    /**#@-*/

    /**
     * Get payment method id
     *
     * @return int|null
     */
    public function getPaymentmethodId();

    /**
     * Set payment method id
     *
     * @param int $paymentmathodId
     * @return $this
     */
    public function setPaymentmethodId($paymentmathodId);

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set payment method code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Check whether payment method is active
     *
     * @return bool|null
     */
    public function getIsActive();

    /**
     * Set whether payment method is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get payment method name
     *
     * @return string
     */
    public function getName();

    /**
     * Set payment method name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get payment method display name
     *
     * @return string
     */
    public function getDisplayName();

    /**
     * Set payment method display name
     *
     * @param string $name
     * @return $this
     */
    public function setDisplayName($displayName);

    /**
     * Get payment method display position
     *
     * @return int
     */
    public function getDisplayPosition();

    /**
     * Set payment method display position
     *
     * @param int $displayPosition
     * @return $this
     */
    public function setDisplayPosition($displayPosition);

    /**
     * Get raw payment method data
     *
     * @return string
     */
    public function getRawPmData();

    /**
     * Set raw payment method data
     *
     * @param string $rawPmData
     * @return $this
     */
    public function setRawPmData($rawPmData);
}
