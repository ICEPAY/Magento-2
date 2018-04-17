<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

class Icepay_Postback extends Icepay_Api_Base
{

    public function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
    }

    /**
     * Return minimized transactional data
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getTransactionString()
    {
        return sprintf(
            "Paymentmethod: %s \n| OrderID: %s \n| Status: %s \n| StatusCode: %s \n| PaymentID: %s \n| TransactionID: %s \n| Amount: %s",
            isset($this->data->paymentMethod) ? $this->data->paymentMethod : "",
            isset($this->data->orderID) ? $this->data->orderID : "",
            isset($this->data->status) ? $this->data->status : "",
            isset($this->data->statusCode) ? $this->data->statusCode : "",
            isset($this->data->paymentID) ? $this->data->paymentID : "",
            isset($this->data->transactionID) ? $this->data->transactionID : "",
            isset($this->data->amount) ? $this->data->amount : ""
        );
    }

    /**
     * Return the statuscode field
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getStatus()
    {
        return (isset($this->data->status)) ? $this->data->status : null;
    }

    /**
     * Return the orderID field
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getOrderID()
    {
        return (isset($this->data->orderID)) ? $this->data->orderID : null;
    }

    /**
     * Return the postback checksum
     * @since version 1.0.0
     * @access protected
     * @return string SHA1 encoded
     */
    protected function generateChecksumForPostback()
    {
        return sha1(
            sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s", $this->_secretCode, $this->_merchantID, $this->data->status, $this->data->statusCode, $this->data->orderID, $this->data->paymentID, $this->data->reference, $this->data->transactionID, $this->data->amount, $this->data->currency, $this->data->duration, $this->data->consumerIPAddress)
        );
    }

    /**
     * Return the version checksum
     * @since version 1.0.2
     * @access protected
     * @return string SHA1 encoded
     */
    protected function generateChecksumForVersion()
    {
        return sha1(
            sprintf("%s|%s|%s|%s", $this->_secretCode, $this->_merchantID, $this->data->status, substr(strval(time()), 0, 8))
        );
    }

    /**
     * Returns the postback response parameter names, useful for a database install script
     * @since version 1.0.1
     * @access public
     * @return array
     */
    public function getPostbackResponseFields()
    {
        return [
            //object reference name => post param name
            "status" => "Status",
            "statusCode" => "StatusCode",
            "merchant" => "Merchant",
            "orderID" => "OrderID",
            "paymentID" => "PaymentID",
            "reference" => "Reference",
            "transactionID" => "TransactionID",
            "consumerName" => "ConsumerName",
            "consumerAccountNumber" => "ConsumerAccountNumber",
            "consumerBIC" => "ConsumerBIC",
            "consumerAddress" => "ConsumerAddress",
            "consumerHouseNumber" => "ConsumerHouseNumber",
            "consumerCity" => "ConsumerCity",
            "consumerCountry" => "ConsumerCountry",
            "consumerEmail" => "ConsumerEmail",
            "consumerPhoneNumber" => "ConsumerPhoneNumber",
            "consumerIPAddress" => "ConsumerIPAddress",
            "amount" => "Amount",
            "currency" => "Currency",
            "duration" => "Duration",
            "paymentMethod" => "PaymentMethod",
            "checksum" => "Checksum"];
    }

    /**
     * Validate for version check
     * @since version 1.0.2
     * @access public
     * @return boolean
     */
    public function validateVersion()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->_logger->log('Invalid request method', Icepay_Api_Logger::ERROR);
            return false;
        }

        if ($this->generateChecksumForVersion() != $this->data->checksum) {
            $this->_logger->log('Checksum does not match', Icepay_Api_Logger::ERROR);
            return false;
        }

        return true;
    }

    /**
     * Has Version Check status
     * @since version 1.0.2
     * @access public
     * @return boolean
     */
    public function isVersionCheck()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->_logger->log('Invalid request method', Icepay_Api_Logger::ERROR);
            return false;
        }

        if ($this->data->status != "VCHECK") {
            return false;
        }

        return true;
    }

    /**
     * Validate the postback data
     * @since version 1.0.0
     * @access public
     * @return boolean
     */
    public function validate()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->_logger->log("Invalid request method", Icepay_Api_Logger::ERROR);
            return false;
        };
        
        $this->_logger->log(sprintf("Postback: %s", serialize($_POST)), Icepay_Api_Logger::TRANSACTION);

        /* @since version 1.0.2 */
        foreach ($this->getPostbackResponseFields() as $obj => $param) {
            $this->data->$obj = (isset($_POST[$param])) ? $_POST[$param] : "";
        }

        if ($this->isVersionCheck()) {
            return false;
        }

        if (!\Icepay\API\Icepay_Parameter_Validation::merchantID($this->data->merchant)) {
            $this->_logger->log("Merchant ID is not numeric: {$this->data->merchant}", Icepay_Api_Logger::ERROR);
            return false;
        }

        if (!\Icepay\API\Icepay_Parameter_Validation::amount($this->data->amount)) {
            $this->_logger->log("Amount is not numeric: {$this->data->amount}", Icepay_Api_Logger::ERROR);
            return false;
        }

        if ($this->_merchantID != $this->data->merchant) {
            $this->_logger->log("Invalid Merchant ID: {$this->data->merchant}", Icepay_Api_Logger::ERROR);
            return false;
        }

        if (!in_array(strtoupper($this->data->status), [
                    Icepay_StatusCode::OPEN,
                    Icepay_StatusCode::AUTHORIZED,
                    Icepay_StatusCode::SUCCESS,
                    Icepay_StatusCode::ERROR,
                    Icepay_StatusCode::REFUND,
                    Icepay_StatusCode::CHARGEBACK
                ])) {
            $this->_logger->log("Unknown status: {$this->data->status}", Icepay_Api_Logger::ERROR);
            return false;
        }

        if ($this->generateChecksumForPostback() != $this->data->checksum) {
            $this->_logger->log("Checksum does not match", Icepay_Api_Logger::ERROR);
            return false;
        }
        return true;
    }

    /**
     * Return the postback data
     * @since version 1.0.0
     * @access public
     * @return object
     */
    public function getPostback()
    {
        return $this->data;
    }

    /**
     * Check between ICEPAY statuscodes whether the status can be updated.
     * @since version 1.0.0
     * @access public
     * @param string $currentStatus The ICEPAY statuscode of the order before a statuschange
     * @return boolean
     */
    public function canUpdateStatus($currentStatus)
    {
        if (!isset($this->data->status)) {
            $this->_logger->log("Status not set", Icepay_Api_Logger::ERROR);
            return false;
        }

        switch ($this->data->status) {
            case Icepay_StatusCode::SUCCESS:
                return ($currentStatus == Icepay_StatusCode::OPEN || $currentStatus == Icepay_StatusCode::AUTHORIZED || $currentStatus == Icepay_StatusCode::VALIDATE);
            case Icepay_StatusCode::OPEN:
                return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::AUTHORIZED:
                return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::VALIDATE:
                return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::ERROR:
                return ($currentStatus == Icepay_StatusCode::OPEN || $currentStatus == Icepay_StatusCode::AUTHORIZED || $currentStatus == Icepay_StatusCode::VALIDATE);
            case Icepay_StatusCode::CHARGEBACK:
                return ($currentStatus == Icepay_StatusCode::SUCCESS);
            case Icepay_StatusCode::REFUND:
                return ($currentStatus == Icepay_StatusCode::SUCCESS);
            default:
                return false;
        };
    }
}
