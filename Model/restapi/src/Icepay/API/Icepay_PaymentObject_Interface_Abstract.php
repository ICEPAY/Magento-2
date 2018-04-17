<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

interface Icepay_PaymentObject_Interface_Abstract
{

    public function setData($data);

    public function getData();

    public function setIssuer($issuer);

    public function getIssuer();

    public function setPaymentMethod($paymentmethod);

    public function getPaymentMethod();

    public function setCountry($country);

    public function getCountry();

    public function setCurrency($currency);

    public function getCurrency();

    public function setLanguage($lang);

    public function getLanguage();

    public function setAmount($amount);

    public function getAmount();

    public function setOrderID($id = "");

    public function getOrderID();

    public function setReference($reference = "");

    public function getReference();

    public function setDescription($info = "");

    public function getDescription();
}

interface Icepay_Basic_Paymentmethod_Interface_Abstract
{

    public function getCode();

    public function getReadableName();

    public function getSupportedIssuers();

    public function getSupportedCountries();

    public function getSupportedCurrency();

    public function getSupportedLanguages();

    public function getSupportedAmountRange();
}

interface Icepay_WebserviceTransaction_Interface_Abstract
{

    public function setData($data);

    public function getPaymentScreenURL();

    public function getPaymentID();

    public function getProviderTransactionID();

    public function getTestMode();

    public function getTimestamp();

    public function getEndUserIP();
}
