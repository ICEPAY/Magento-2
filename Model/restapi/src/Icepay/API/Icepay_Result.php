<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

class Icepay_Result extends Icepay_Api_Base
{

    public function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
    }

    /**
     * Validate the ICEPAY GET data
     * @since version 1.0.0
     * @access public
     * @return boolean
     */
    public function validate()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->_logger->log("Invalid request method", Icepay_Api_Logger::ERROR);
            return false;
        }

        $this->_logger->log(sprintf("Page data: %s", serialize($_GET)), Icepay_Api_Logger::NOTICE);

        $this->data->status = (isset($_GET['Status'])) ? $_GET['Status'] : "";
        $this->data->statusCode = (isset($_GET['StatusCode'])) ? $_GET['StatusCode'] : "";
        $this->data->merchant = (isset($_GET['Merchant'])) ? $_GET['Merchant'] : "";
        $this->data->orderID = (isset($_GET['OrderID'])) ? $_GET['OrderID'] : "";
        $this->data->paymentID = (isset($_GET['PaymentID'])) ? $_GET['PaymentID'] : "";
        $this->data->reference = (isset($_GET['Reference'])) ? $_GET['Reference'] : "";
        $this->data->transactionID = (isset($_GET['TransactionID'])) ? $_GET['TransactionID'] : "";
        $this->data->checksum = (isset($_GET['Checksum'])) ? $_GET['Checksum'] : "";

        if ($this->generateChecksumForPage() != $this->data->checksum) {
            $this->_logger->log("Checksum does not match", Icepay_Api_Logger::ERROR);
            return false;
        }

        return true;
    }

    /**
     * Get the ICEPAY status
     * @since version 1.0.0
     * @access public
     * @param boolean $includeStatusCode Add the statuscode message to the returned string for display purposes
     * @return string ICEPAY statuscode (and statuscode message)
     */
    public function getStatus($includeStatusCode = false)
    {
        if (!isset($this->data->status)) {
            return null;
        }
        return ($includeStatusCode) ? sprintf("%s: %s", $this->data->status, $this->data->statusCode) : $this->data->status;
    }

    /**
     * Return the orderID field
     * @since version 1.0.2
     * @access public
     * @return string
     */
    public function getOrderID()
    {
        return (isset($this->data->orderID)) ? $this->data->orderID : null;
    }

    public function setOrderID($id)
    {
        $this->data->orderID = $id;
    }

    /**
     * Return the result page checksum
     * @since version 1.0.0
     * @access protected
     * @return string SHA1 hash
     */
    protected function generateChecksumForPage()
    {
        return sha1(
            sprintf("%s|%s|%s|%s|%s|%s|%s|%s", $this->_secretCode, $this->data->merchant, $this->data->status, $this->data->statusCode, $this->data->orderID, $this->data->paymentID, $this->data->reference, $this->data->transactionID)
        );
    }

    /**
     * Return the get data
     * @since version 1.0.1
     * @access public
     * @return object
     */
    public function getResultData()
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
