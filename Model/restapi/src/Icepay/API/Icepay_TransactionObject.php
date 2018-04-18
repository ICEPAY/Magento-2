<?php
/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016-2018, ICEPAY B.V. All rights reserved.
 */

namespace Icepay\API;

class Icepay_TransactionObject implements Icepay_WebserviceTransaction_Interface_Abstract
{

    protected $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getPaymentScreenURL()
    {
        return $this->data->PaymentScreenURL;
    }

    public function getPaymentID()
    {
        return $this->data->PaymentID;
    }

    public function getProviderTransactionID()
    {
        return $this->data->ProviderTransactionID;
    }

    public function getTestMode()
    {
        return $this->data->TestMode;
    }

    public function getTimestamp()
    {
        return $this->data->Timestamp;
    }

    public function getEndUserIP()
    {
        return $this->data->EndUserIP;
    }

    public function getOrderID()
    {
        return $this->data->OrderID;
    }
}
