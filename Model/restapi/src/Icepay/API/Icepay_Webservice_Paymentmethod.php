<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

class Icepay_Webservice_Paymentmethod extends Icepay_Webservice_Filtering
{

    protected $_methodData;
    protected $_issuerData;
    protected $_country;

    /**
     * Select the payment method by code
     *
     * @since 2.1.0
     * @access public
     * @param string $name
     * @return \Icepay_Webservice_Paymentmethod
     * @throws Exception
     */
    public function selectPaymentMethodByCode($name)
    {
        if (!isset($this->_paymentMethodsArray)) {
            throw new Exception("No data loaded");
        }
        foreach ($this->_paymentMethodsArray as $paymentMethod) {
            if ($paymentMethod->PaymentMethodCode == strtoupper($name)) {
                $this->_methodData = $paymentMethod;
                break;
            }
        }
        return $this;
    }

    /**
     * Select an issuer by keyword
     *
     * @since 2.1.0
     * @access public
     * @param string $name
     * @return \Icepay_Webservice_Paymentmethod
     * @throws Exception
     */
    public function selectIssuerByKeyword($name)
    {
        if (!isset($this->_paymentMethodsArray)) {
            throw new Exception("No data loaded");
        }
        foreach ($this->_paymentMethodsArray as $paymentMethod) {
            foreach ($paymentMethod->Issuers as $issuer) {
                if ($issuer->IssuerKeyword == strtoupper($name)) {
                    $this->_methodData = $paymentMethod;
                    $this->_issuerData = $issuer;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Selects the country out of the issuer data
     *
     * @since 2.1.0
     * @access Public
     * @param string $country
     * @return \Icepay_Webservice_Paymentmethod
     */
    public function selectCountry($country)
    {
        if (!isset($this->_issuerData)) {
            $this->_country = $this->validateCountry($country);
            return $this;
        }

        if (in_array($country, $this->getCountries())) {
            $this->_country = $this->validateCountry($country);
            return $this;
        };

        if (in_array("00", $this->getCountries())) {
            $this->_country = "00";
        };

        return $this;
    }

    /**
     * Get payment method data
     *
     * @since 2.1.0
     * @access public
     * @return array
     */
    public function getPaymentmethodData()
    {
        return $this->_methodData;
    }

    /**
     * Get issuer data
     *
     * @since 2.1.0
     * @access public
     * @return array
     */
    public function getIssuerData()
    {
        return $this->_issuerData;
    }

    /**
     * Get issuer list
     *
     * @since 2.1.0
     * @access public
     * @return array
     * @throws Exception
     */
    public function getIssuers()
    {
        if (!isset($this->_methodData)) {
            throw new Exception("Paymentmethod must be selected first");
        }
        return $this->_methodData->Issuers;
    }

    /**
     * Get currencies
     *
     * @since 2.1.0
     * @access public
     * @return array
     * @throws Exception
     */
    public function getCurrencies()
    {
        if (!isset($this->_issuerData)) {
            throw new Exception("Issuer must be selected first");
        }
        if (!isset($this->_country)) {
            throw new Exception("Country must be selected first");
        }
        foreach ($this->_issuerData["Countries"] as $country) {
            if ($this->_country == $country["CountryCode"]) {
                return $country["Currencies"];
            }
        }
        return [];
    }

    /**
     * Get countries
     *
     * @since 2.1.0
     * @access public
     * @return array
     * @throws Exception
     */
    public function getCountries()
    {
        if (!isset($this->_issuerData)) {
            throw new Exception("Issuer must be selected first");
        }
        $countries = [];
        foreach ($this->_issuerData["Countries"] as $country) {
            array_push($countries, $country["CountryCode"]);
        }
        return $countries;
    }

    /**
     * Get minimum amount
     *
     * @since 2.1.0
     * @access public
     * @return int
     * @throws Exception
     */
    public function getMinimumAmount()
    {
        if (!isset($this->_issuerData)) {
            throw new Exception("Issuer must be selected first");
        }
        if (!isset($this->_country)) {
            throw new Exception("Country must be selected first");
        }
        foreach ($this->_issuerData["Countries"] as $country) {
            if ($this->_country == $country["CountryCode"]) {
                return intval($country["MinimumAmount"]);
            }
        }
    }

    /**
     * Get maximum amount
     *
     * @since 2.1.0
     * @access public
     * @return int
     * @throws Exception
     */
    public function getMaximumAmount()
    {
        if (!isset($this->_issuerData)) {
            throw new Exception("Issuer must be selected first");
        }
        if (!isset($this->_country)) {
            throw new Exception("Country must be selected first");
        }
        foreach ($this->_issuerData["Countries"] as $country) {
            if ($this->_country == $country["CountryCode"]) {
                return intval($country["MaximumAmount"]);
            }
        }
    }

    /**
     * Validate given country
     *
     * @since 2.1.0
     * @access protected
     * @param string $country
     * @return string
     * @throws Exception
     */
    protected function validateCountry($country)
    {
        if (strlen($country) != 2) {
            throw new Exception("Country must be ISO 3166-1 alpha-2");
        }
        return strtoupper($country);
    }
}
