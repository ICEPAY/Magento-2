<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016-2018, ICEPAY B.V. All rights reserved.
 */

namespace Icepay\API;

class Icepay_Webservice_Filtering
{

    protected $_paymentMethodsArray;
    protected $_paymentMethodsArrayFiltered;

    /**
     * From String
     *
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return \Icepay_Webservice_Filtering
     */
    public function importFromString($string)
    {
        $this->_paymentMethodsArray = unserialize(urldecode($string));
        $this->_paymentMethodsArrayFiltered = $this->_paymentMethodsArray;
        return $this;
    }

    /**
     * Export String
     *
     * @since 2.1.0
     * @access public
     * @return string
     */
    public function exportAsString()
    {
        return urlencode(serialize($this->_paymentMethodsArrayFiltered));
    }

    /**
     * From Array
     *
     * @since 2.1.0
     * @access public
     * @param array $array
     * @return \Icepay_Webservice_Filtering
     */
    public function loadFromArray($array)
    {
        $this->_paymentMethodsArray = $array;
        $this->_paymentMethodsArrayFiltered = $this->_paymentMethodsArray;
        return $this;
    }

    /**
     * Read data from stored file
     *
     * @since 2.1.0
     * @access public
     * @param string $fileName
     * @param string $directory
     * @return \Icepay_Webservice_Filtering
     * @throws Exception
     */
    public function loadFromFile($fileName = "wsdata", $directory = "")
    {
        if ($directory == "") {
            $directory = dirname(__FILE__);
        }

        $filename = sprintf("%s/%s.csv", $directory, $fileName);
        try {
            $fp = @fopen($filename, "r");
            $line = @fgets($fp);
            @fclose($fp);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        };

        if (!$line) {
            throw new \Exception("No data stored");
        }

        $arr = explode(",", $line);
        $this->importFromString($arr[1]);

        return $this;
    }

    /**
     * Get payment methods
     *
     * @since 2.1.0
     * @access public
     * @return array
     */
    public function getPaymentmethods()
    {
        return $this->_paymentMethodsArray;
    }

    /**
     * Get filtered payment methods
     *
     * @since 2.1.0
     * @access public
     * @return array
     */
    public function getFilteredPaymentmethods()
    {
        return $this->_paymentMethodsArrayFiltered;
    }

    /**
     * Filter the paymentmethods array by currency
     * @since 2.1.0
     * @access public
     * @param string $currency Language ISO 4217 code
     */
    public function filterByCurrency($currency)
    {
        $filteredArr = [];
       // var_dump($this->_paymentMethodsArrayFiltered);
        foreach ($this->_paymentMethodsArrayFiltered as $paymentMethod) {
            $continue = true;
            foreach ($paymentMethod->Issuers as $issuer) {
                foreach ($issuer->Countries as $country) {
                    if (in_array(strtoupper($currency), explode(", ", $country->Currency))) {
                        array_push($filteredArr, $paymentMethod); //return//return
                        $continue = false;
                    }
                    if (!$continue) {
                        break;
                    }
                }
                if (!$continue) {
                    break;
                }
            }
        }
        $this->_paymentMethodsArrayFiltered = $filteredArr;
        return $this;
    }

    /**
     * Filter the paymentmethods array by country
     * @since 2.1.0
     * @access public
     * @param string $country Country ISO 3166-1-alpha-2 code
     */
    public function filterByCountry($countryCode)
    {
        $filteredArr = [];
        foreach ($this->_paymentMethodsArrayFiltered as $paymentMethod) {
            $continue = true;
            foreach ($paymentMethod->Issuers as $issuer) {
                foreach ($issuer->Countries as $country) {
                    if (strtoupper($country->CountryCode) == strtoupper($countryCode) || $country->CountryCode == "00") {
                        array_push($filteredArr, $paymentMethod);
                        $continue = false;
                    }
                    if (!$continue) {
                        break;
                    }
                }
                if (!$continue) {
                    break;
                }
            }
        }
        $this->_paymentMethodsArrayFiltered = $filteredArr;
        return $this;
    }

    /**
     * Filter the paymentmethods array by amount
     * @since 2.1.0
     * @access public
     * @param int $amount Amount in cents
     */
    public function filterByAmount($amount)
    {
        $amount = intval($amount);
        $filteredArr = [];
        foreach ($this->_paymentMethodsArrayFiltered as $paymentMethod) {
            $continue = true;
            foreach ($paymentMethod->Issuers as $issuer) {
                foreach ($issuer->Countries as $country) {
                    if ($amount >= intval($country->MinimumAmount) &&
                            $amount <= intval($country->MaximumAmount)) {
                        array_push($filteredArr, $paymentMethod);
                        $continue = false;
                    }
                    if (!$continue) {
                        break;
                    }
                }
                if (!$continue) {
                    break;
                }
            }
        }
        $this->_paymentMethodsArrayFiltered = $filteredArr;
        return $this;
    }
}
