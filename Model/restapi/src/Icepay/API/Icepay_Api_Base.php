<?php
/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

class Icepay_Api_Base
{

    private $_pinCode;
    protected $_merchantID;
    protected $_secretCode;
    protected $_method = null;
    protected $_issuer = null;
    protected $_country = null;
    protected $_language = null;
    protected $_currency = null;
    protected $_version = "1.0.2";
    protected $_doIPCheck = [];
    protected $_whiteList = [];
    protected $data;
    protected $_logger;

    public function __construct()
    {
        $this->_logger = Icepay_Api_Logger::getInstance();
        $this->data = new stdClass();
    }

    /**
     * Validate data
     * @since version 1.0.0
     * @access public
     * @param string $needle
     * @param array $haystack
     * @return boolean
     */
    public function exists($needle, $haystack = null)
    {
        $result = true;
        if ($haystack && $result && $haystack[0] != "00") {
            $result = in_array($needle, $haystack);
        }
        return $result;
    }

    /**
     * Get the version of the API or the loaded payment method class
     * @since 1.0.0
     * @access public
     * @return string Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the Merchant ID field
     * @since 1.0.0
     * @access public
     * @param (int) $merchantID
     */
    public function setMerchantID($merchantID)
    {
        if (!\Icepay\API\Icepay_Parameter_Validation::merchantID($merchantID)) {
            throw new Exception('MerchantID not valid');
        }

        $this->_merchantID = (int) $merchantID;

        return $this;
    }

    /**
     * Get the Merchant ID field
     * @since 1.0.0
     * @access public
     * @return (int) MerchantID
     */
    public function getMerchantID()
    {
        return $this->_merchantID;
    }

    /**
     * Set the Secret Code field
     * @since 1.0.0
     * @access public
     * @param (string) $secretCode
     */
    public function setSecretCode($secretCode)
    {
        if (!\Icepay\API\Icepay_Parameter_Validation::secretCode($secretCode)) {
            throw new Exception('Secretcode not valid');
        }

        $this->_secretCode = (string) $secretCode;
        return $this;
    }

    /**
     * Get the Secret Code field
     * @since 1.0.0
     * @access protected
     * @return (string) Secret Code
     */
    protected function getSecretCode()
    {
        return $this->_secretCode;
    }

    /**
     * Set the Pin Code field
     * @since 1.0.1
     * @access public
     * @param (int) $pinCode
     */
    public function setPinCode($pinCode)
    {
        if (!\Icepay\API\Icepay_Parameter_Validation::pinCode($pinCode)) {
            throw new Exception('Pincode not valid');
        }

        $this->_pinCode = (string) $pinCode;

        return $this;
    }

    /**
     * Get the Pin Code field
     * @since 1.0.0
     * @access protected
     * @return (int) PinCode
     */
    protected function getPinCode()
    {
        return $this->_pinCode;
    }

    /**
     * Set the success url field (optional)
     * @since version 1.0.1
     * @access public
     * @param string $url
     */
    public function setSuccessURL($url = "")
    {
        if (!isset($this->data)) {
            $this->data = new stdClass();
        }

        $this->data->ic_urlcompleted = $url;

        return $this;
    }

    /**
     * Set the error url field (optional)
     * @since version 1.0.1
     * @access public
     * @param string $url
     */
    public function setErrorURL($url = "")
    {
        if (!isset($this->data)) {
            $this->data = new stdClass();
        }

        $this->data->ic_urlerror = $url;
        return $this;
    }

    /**
     * Get the success URL
     * @since version 2.1.0
     * @access public
     * @return string $url
     */
    public function getSuccessURL()
    {
        return (isset($this->data->ic_urlcompleted)) ? $this->data->ic_urlcompleted : "";
    }

    /**
     * Get the error URL
     * @since version 2.1.0
     * @access public
     * @return string $url
     */
    public function getErrorURL()
    {
        return (isset($this->data->ic_urlerror)) ? $this->data->ic_urlerror : "";
    }

    public function getTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s\Z");
    }
}
