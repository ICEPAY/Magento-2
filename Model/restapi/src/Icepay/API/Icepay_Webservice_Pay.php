<?php
/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

class Icepay_Webservice_Pay extends Icepay_Webservice_Base
{

    /* @var $client \Icepay\API\Client */
    public $client;
    private $extendedCheckoutList = ['AFTERPAY'];
    private $autoCheckoutList = [
        'CREDITCARD' => ['CCAUTOCHECKOUT'],
        'DDEBIT' => ['IDEALINCASSO']];

    public function __construct()
    {
    }

    public function addToExtendedCheckoutList($paymentMethods)
    {
        $this->extendedCheckoutList = array_merge($this->extendedCheckoutList, $paymentMethods);

        return $this;
    }

    public function isExtendedCheckoutRequiredByPaymentMethod($paymentMethod)
    {
        if (in_array($paymentMethod, $this->extendedCheckoutList)) {
            return true;
        }

        return false;
    }

    public function extendedCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj, $getUrlOnly = false)
    {
        $obj = new stdClass();

        Icepay_Order::getInstance()->validateOrder($paymentObj);

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();
        $obj->XML = Icepay_Order::getInstance()->createXML();

        // ------------------------------------------------
        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->payment->CheckoutExtended((Array)$obj);

        /* store the checksum momentarily */
        $checksum = $result->CheckoutExtendedResult->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->CheckoutExtendedResult->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result->CheckoutExtendedResult)) {
            throw new Exception("Data could not be verified");
        }

        /* Return mister checksum */
        $result->CheckoutExtendedResult->Checksum = $checksum;

        /* Return just the payment URL if required */
        if ($getUrlOnly) {
            return $result->CheckoutExtendedResult->PaymentScreenURL;
        }

        $transactionObj = new Icepay_TransactionObject();
        $transactionObj->setData($result->CheckoutExtendedResult);


        /* Default return all data */
        return $transactionObj;
    }

    private function validateAutoCheckout($paymentObj)
    {
        // Check if PaymentMethod is allowed
        if (!array_key_exists($paymentObj->PaymentMethod, $this->autoCheckoutList)) {
            throw new Exception("Error: Paymentmethod {$paymentObj->PaymentMethod} is not allowed to use autoCheckout");
        }

        // Check if Issuer is allowed
        if (!in_array($paymentObj->Issuer, $this->autoCheckoutList[$paymentObj->PaymentMethod])) {
            throw new Exception("Error: Issuer {$paymentObj->Issuer} is not allowed to use autoCheckout");
        }

        return true;
    }

    public function autoCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj, $consumerID)
    {
        $obj = new StdClass();

        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();

        $this->validateAutoCheckout($obj);

        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode(), true);

        // Checksum is generated without the consumer ID
        $obj->ConsumerID = $consumerID;

        // Call the webservice
        $result = $this->client->payment->automaticCheckout((Array)$obj);

        /* store the checksum momentarily */
        $checksum = $result->AutomaticCheckoutResult->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->AutomaticCheckoutResult->Checksum = $this->getSecretCode();

        $checksumObject = $this->arrangeObject($result->AutomaticCheckoutResult, [
            'Checksum', 'MerchantID', 'Timestamp', 'PaymentID', 'Success', 'ErrorDescription'
        ]);

        /* Verify response data */
        if ($checksum != $this->generateChecksum($checksumObject, null, true)) {
            throw new Exception("Data could not be verified");
        }

        // Return checksum
        $result->AutomaticCheckoutResult->Checksum = $checksum;

        return $result->AutomaticCheckoutResult;
    }

    public function vaultCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj, $consumerID, $getUrlOnly = false)
    {
        $obj = new StdClass();

        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();

        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        // Checksum is generated without the consumer ID
        $obj->ConsumerID = $consumerID;

        // Call the webservice
        $result = $this->client->payment->VaultCheckout((Array)$obj);

        /* store the checksum momentarily */
        $checksum = $result->VaultCheckoutResult->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->VaultCheckoutResult->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result->VaultCheckoutResult)) {
            throw new Exception("Data could not be verified");
        }

        /* Return mister checksum */
        $result->VaultCheckoutResult->Checksum = $checksum;

        /* Return just the payment URL if required */
        if ($getUrlOnly) {
            return $result->VaultCheckoutResult->PaymentScreenURL;
        }

        $transactionObj = new Icepay_TransactionObject();
        $transactionObj->setData($result->VaultCheckoutResult);

        /* Default return all data */
        return $transactionObj;
    }

    /**
     * The Checkout web method allows you to  initialize a new payment in the ICEPAY system for  ALL the
     * payment methods that you have access to
     *
     * @since version 2.1.0
     * @access public
     * @param Icepay_PaymentObject_Interface_Abstract $paymentObj
     * @param bool $geturlOnly
     * @return array result
     */
    public function checkOut(Icepay_PaymentObject_Interface_Abstract $paymentObj, $getUrlOnly = false)
    {
        $obj = new stdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();

        // ------------------------------------------------

        $result = $this->client->payment->Checkout((Array)$obj);

        if (!isset($result->Checksum)) {
            $message = "Error creating the order";
            if (0 === strpos($result->Message, 'ERR_') || 0 === strpos($result->Message, 'IC_ERR')) {
                $message .= ": " . preg_replace('/[^a-zA-Z0-9_\s]/', '', strip_tags($result->Message));
            }
            throw new Exception($message);
        }

        /* Return just the payment URL if required */
        if ($getUrlOnly) {
            return $result->PaymentScreenURL;
        }

        $transactionObj = new Icepay_TransactionObject();
        $transactionObj->setData($result);


        /* Default return all data */
        return $transactionObj;
    }

    /**
     * The PhoneCheckout web method  allows you to create a phone payment in the ICEPAY system. The
     * main difference with the  Checkout web method is the response. The response  is  a
     * PhoneCheckoutResponse object, which contains extra members such as the phone number etc., making
     * seamless integration possible.
     *
     * @since 2.1.0
     * @access public
     * @param array $data
     * @param bool $geturlOnly
     * @return array result
     */
    public function phoneCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj, $getUrlOnly = false)
    {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->payment->PhoneCheckout(['request' => $obj]);

        /* store the checksum momentarily */
        $checksum = $result->PhoneCheckoutResult->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->PhoneCheckoutResult->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result->PhoneCheckoutResult)) {
            throw new Exception("Data could not be verified");
        }

        /* Return mister checksum */
        $result->PhoneCheckoutResult->Checksum = $checksum;

        /* Return only the payment URL if required */
        if ($getUrlOnly) {
            return $result->PhoneCheckoutResult->PaymentScreenURL;
        }

        /* Default return all data */
        return (array) $result->PhoneCheckoutResult;
    }

    /**
     * The SmsCheckout web method allows you to create an SMS payment in the ICEPAY system. The main
     * difference with the Checkout web method is the response. The response will contain extra members such
     * as the premium-rate number, making seamless integration possible.
     *
     * @since 2.1.0
     * @access public
     * @param array $data
     * @param bool $geturlOnly
     * @return array
     */
    public function smsCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj, $getUrlOnly = false)
    {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->payment->SmsCheckout(['request' => $obj]);
        $result = $result->SMSCheckoutResult;

        /* store the checksum momentarily */
        $checksum = $result->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->Checksum = $this->getSecretCode();

        // Order object in correct order for Checksum
        $checksumObject = $this->arrangeObject($result, [
            'Checksum', 'MerchantID', 'Timestamp', 'Amount', 'Country',
            'Currency', 'Description', 'EndUserIP', 'Issuer', 'Language',
            'OrderID', 'PaymentID', 'PaymentMethod', 'PaymentScreenURL',
            'ProviderTransactionID', 'Reference', 'TestMode', 'URLCompleted',
            'URLError', 'ActivationCode', 'Keyword', 'PremiumNumber', 'Disclaimer'
        ]);

        /* Verify response data */
        if ($checksum != $this->generateChecksum($checksumObject)) {
            throw new Exception("Data could not be verified");
        }

        /* Return mister checksum */
        $result->Checksum = $checksum;

        /* Return only the payment URL if required */
        if ($getUrlOnly) {
            return $result->PaymentScreenURL;
        }

        /* Default return all data */
        return (array) $result;
    }

    /**
     * The ValidatePhoneCode web method verifies the code that the end-user must provide in
     * order to start a phone payment.
     *
     * @since 2.1.0
     * @access public
     * @param int $paymentID
     * @param int $phoneCode
     * @return bool success
     */
    public function validatePhoneCode($paymentID, $phoneCode)
    {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->PaymentID = $paymentID;
        $obj->PhoneCode = $phoneCode;

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->payment->ValidatePhoneCode(['request' => $obj]);
        $result = $result->ValidatePhoneCodeResult;

        /* store the checksum momentarily */
        $checksum = $result->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result)) {
            throw new Exception("Data could not be verified");
        }

        return $result->Success;
    }

    /**
     * The ValidateSmsCode web method validates the code that the end-user must provide.
     *
     * @since 2.1.0
     * @access public
     * @param int $paymentID
     * @param int $smsCode
     * @return bool success
     */
    public function validateSmsCode($paymentID, $smsCode)
    {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->PaymentID = $paymentID;
        $obj->SmsCode = $smsCode;

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->payment->ValidateSmsCode(['request' => $obj]);
        $result = $result->ValidateSmsCodeResult;

        /* store the checksum momentarily */
        $checksum = $result->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result)) {
            throw new Exception("Data could not be verified");
        }

        return $result->Success;
    }

    /**
     * The phoneDirectCheckout web method allows you to initialize a new payment in the ICEPAY system
     * with paymentmethod Phone with Pincode
     *
     * @since version 2.1.0
     * @access public
     * @param object $data
     * @return array result
     */
    public function phoneDirectCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj)
    {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();
        $obj->PINCode = $this->getPinCode();

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->payment->phoneDirectCheckout(['request' => $obj]);
        $result = $result->PhoneDirectCheckoutResult;

        /* store the checksum momentarily */
        $checksum = $result->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->Checksum = $this->getSecretCode();

        // Reverse Success and Error Description, since order must be specific for Checksum
        $success = $result->Success;
        $errorDescription = $result->ErrorDescription;

        unset($result->Success, $result->ErrorDescription);

        $result->Success = $success;
        $result->ErrorDescription = $errorDescription;

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result)) {
            throw new Exception("Data could not be verified");
        }

        /* Return mister checksum */
        $result->Checksum = $checksum;

        /* Default return all data */
        return (array) $result;
    }

    /**
     * The GetPremiumRateNumbers web method is supplementary to the PhoneDirectCheckout web method.
     * The idea is that you query the latest premium-rate number information (such as rate per minute, etc.)
     * and cache it on your own system so that you can display the
     * premium-rate number information to the enduser without having to start a new transaction.
     *
     * @since version 2.1.0
     * @access public
     * @return array result
     */
    public function getPremiumRateNumbers()
    {
        $obj = new StdClass();

        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();

        $obj->Checksum = $this->generateChecksum($obj);

        $result = $this->client->payment->GetPremiumRateNumbers(['request' => $obj]);
        $result = $result->GetPremiumRateNumbersResult;
        $premiumRateNumbers = isset($result->PremiumRateNumbers->PremiumRateNumberInformation) ? $result->PremiumRateNumbers->PremiumRateNumberInformation : null;

        // Checksum
        $obj = new StdClass();

        $obj->SecretCode = $this->getSecretCode();
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $result->Timestamp;

        if (!is_null($premiumRateNumbers)) {
            $obj = $this->parseForChecksum($obj, $premiumRateNumbers, true, ["PhoneNumber", "RatePerCall", "RatePerMinute"]);
        }

        $Checksum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $Checksum) {
            throw new Exception('Data could not be verified');
        }

        return (array) $result;
    }

    /**
     * The GetPremiumRateNumbers web method is supplementary to the PhoneDirectCheckout web method.
     * The idea is that you query the latest premium-rate number information (such as rate per minute, etc.)
     * and cache it on your own system so that you can display the
     * premium-rate number information to the enduser without having to start a new transaction.
     *
     * @since version 2.1.0
     * @access public
     * @param int $paymentID
     * @return array result
     */
    public function getPayment($paymentID)
    {
        $obj = new StdClass();

        $obj->SecretCode = $this->getSecretCode();
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimestamp();
        $obj->PaymentID = $paymentID;

        $obj->Checksum = $this->generateChecksum($obj);

        $result = $this->client->payment->getPayment((Array)$obj);
        $result = $result->GetPaymentResult;

        $checksum = $result->Checksum;

        $result->Checksum = $this->getSecretCode();

        // Order object in correct order for Checksum
        $result = $this->arrangeObject($result, [
            "Checksum", "MerchantID", "Timestamp", "PaymentID",
            "Amount", "ConsumerAccountNumber", "ConsumerBIC", "ConsumerAddress",
            "ConsumerCity", "ConsumerCountry", "ConsumerEmail",
            "ConsumerHouseNumber", "ConsumerIPAddress", "ConsumerName",
            "ConsumerPhoneNumber", "Currency", "Description", "Duration",
            "Issuer", "OrderID", "OrderTime", "PaymentMethod", "PaymentTime",
            "Reference", "Status", "StatusCode", "TestMode"
        ]);

        /* Custom checksum for extra fields */
        $signstring = $this->arrangeObject($result, [
            "Checksum", "MerchantID", "Timestamp", "PaymentID",
            "Amount", "ConsumerAccountNumber", "ConsumerAddress",
            "ConsumerCity", "ConsumerCountry", "ConsumerEmail",
            "ConsumerHouseNumber", "ConsumerIPAddress", "ConsumerName",
            "ConsumerPhoneNumber", "Currency", "Description", "Duration",
            "Issuer", "OrderID", "OrderTime", "PaymentMethod", "PaymentTime",
            "Reference", "Status", "StatusCode", "TestMode"
        ]);

        if ($checksum != $this->generateChecksum($signstring)) {
            throw new Exception("Data could not be verified");
        }

        /* Return mister checksum */
        $result->Checksum = $checksum;

        /* Default return all data */
        return (array) $result;
    }
}
