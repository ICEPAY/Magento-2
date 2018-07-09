<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016-2018, ICEPAY B.V. All rights reserved.
 */

namespace Icepay\API;

class Icepay_PaymentObject implements Icepay_PaymentObject_Interface_Abstract
{

    protected $data;
    protected $api_type = "webservice";
    protected $pm_class;
    private static $instance;

    /**
     * Construct of Icepay_PaymentObject
     * @since version 2.1.1
     * @access public
     */
    public function __construct()
    {
        // Instantiate $this->data explicitely for PHP Strict error reporting
        $this->data = new \stdClass();
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set all fields in one method
     * @since version 2.1.0
     * @access public
     * @param object $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get all data as an object
     * @since version 2.1.0
     * @access public
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Load a paymentmethod class for Basic
     * @since version 2.1.0
     * @access protected
     */
    protected function loadBasicPaymentMethodClass()
    {

        if (!class_exists("Icepay_Api_Basic")) {
            return $this;
        }

        $this->pm_class = Icepay_Api_Basic::getInstance()
                ->readFolder()
                ->getClassByPaymentMethodCode($this->data->ic_paymentmethod);

        if (count($this->pm_class->getSupportedIssuers()) == 1) {
            $this->setIssuer(current($this->pm_class->getSupportedIssuers()));
        }
        return $this;
    }

    /**
     * Get all data as an object
     * @since version 2.1.0
     * @access public
     * @return Icepay_Basic_Paymentmethod_Interface_Abstract
     */
    public function getBasicPaymentmethodClass()
    {
        return $this->pm_class;
    }

    /**
     * Set the country field
     * @since version 1.0.0
     * @access public
     * @param string $currency Country ISO 3166-1-alpha-2 code !Required
     * @example setCountry("NL") // Netherlands
     */
    public function setCountry($country)
    {
        $country = strtoupper($country);
        if (!\Icepay\API\Icepay_Parameter_Validation::country($country)) {
            throw new \Exception('Country not valid');
        }
        $this->data->ic_country = $country;
        return $this;
    }

    /**
     * Set the currency field
     * @since version 1.0.0
     * @access public
     * @param string $currency Language ISO 4217 code !Required
     * @example setCurrency("EUR") // Euro
     */
    public function setCurrency($currency)
    {
        $this->data->ic_currency = $currency;
        return $this;
    }

    /**
     * Set the language field
     * @since version 1.0.0
     * @access public
     * @param string $lang Language ISO 639-1 code !Required
     * @example setLanguage("EN") // English
     */
    public function setLanguage($lang)
    {
        if (!\Icepay\API\Icepay_Parameter_Validation::language($lang)) {
            throw new \Exception('Language not valid');
        }
        $this->data->ic_language = $lang;
        return $this;
    }

    /**
     * Set the amount field
     * @since version 1.0.0
     * @access public
     * @param int $amount !Required
     */
    public function setAmount($amount)
    {
        $amount = (int) (string) $amount;

        if (!\Icepay\API\Icepay_Parameter_Validation::amount($amount)) {
            throw new \Exception('Amount not valid');
        }
        $this->data->ic_amount = $amount;
        return $this;
    }

    /**
     * Set the order ID field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $id
     */
    public function setOrderID($id = "")
    {
        $this->data->ic_orderid = $id;
        return $this;
    }

    /**
     * Set the reference field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $reference
     */
    public function setReference($reference = "")
    {
        $this->data->ic_reference = $reference;
        return $this;
    }

    /**
     * Set the description field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $info
     */
    public function setDescription($info = "")
    {
        $this->data->ic_description = $info;
        return $this;
    }

    /**
     * Sets the issuer and checks if the issuer exists within the paymentmethod
     * @since version 1.0.0
     * @access public
     * @param string $issuer ICEPAY Issuer code
     */
    public function setIssuer($issuer)
    {
        $this->data->ic_issuer = $issuer;
        return $this;
    }

    public function setXML($xml)
    {
        $this->data->ic_xml = $xml;
        return $this;
    }

    /**
     * Sets the payment method and checks if the method exists within the class
     * @since version 1.0.0
     * @access public
     * @param string $paymentMethod ICEPAY Payment method code
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->data->ic_paymentmethod = $paymentMethod;
        $this->loadBasicPaymentMethodClass();
        return $this;
    }

    public function getCountry()
    {
        return $this->data->ic_country;
    }

    public function getCurrency()
    {
        return $this->data->ic_currency;
    }

    public function getLanguage()
    {
        return $this->data->ic_language;
    }

    public function getAmount()
    {
        return $this->data->ic_amount;
    }

    public function getOrderID()
    {
        return $this->data->ic_orderid;
    }

    public function getReference()
    {
        return (isset($this->data->ic_reference) ? $this->data->ic_reference : null);
    }

    public function getDescription()
    {
        return (isset($this->data->ic_description) ? $this->data->ic_description : null);
    }

    public function getIssuer()
    {
        return (isset($this->data->ic_issuer) ? $this->data->ic_issuer : null);
    }

    public function getPaymentMethod()
    {
        return (isset($this->data->ic_paymentmethod) ? $this->data->ic_paymentmethod : null);
    }

    public function getXML()
    {
        return $this->data->ic_xml;
    }
}
