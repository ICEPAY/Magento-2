<?php namespace Icepay\API\Resources;

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

class Payment extends BaseApi
{
    /**
     * @param $data
     * @return mixed
     */
    public function checkOut($data)
    {
        /**
         * Information for starting a payment
         */
        $information = [
            'Timestamp' => $this->getTimeStamp(),
            'Amount' => $data['Amount'],
            'Country' => $data['Country'],
            'Currency' => $data['Currency'],
            'Description' => $data['Description'],
            'Issuer' => $data['Issuer'],
            'Language' => $data['Language'],
            'OrderID' => $data['OrderID'],
            'PaymentMethod' => $data['PaymentMethod'],
            'Reference' => $data['Reference'],
            'URLCompleted' => $this->client->api_completed_url,
            'URLError' => $this->client->api_error_url,
            'EndUserIP' => $this->getClientIp()
        ];

        /**
         * Generate the checksum for the request
         */
        $checksum = $this->client->generateChecksum(
            $this->client->api_endpoint .
            'payment/checkout' .
            $this->client->api_post .
            $this->client->api_key .
            $this->client->api_secret .
            json_encode($information)
        );

        /**
         * Make the call.
         */
        return $this->client->request($this->client->api_post, 'payment/checkout', $information, $checksum);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function vaultCheckout($data)
    {
        /**
         * Information for starting a recurring payment
         */
        $information = [
            'Timestamp' => $this->getTimeStamp(),
            'Amount' => $data['Amount'],
            'Country' => $data['Country'],
            'Currency' => $data['Currency'],
            'Description' => $data['Description'],
            'Issuer' => $data['Issuer'],
            'CustomerID' => $data['CustomerID'],
            'Language' => $data['Language'],
            'OrderID' => $data['OrderID'],
            'PaymentMethod' => $data['Paymentmethod'],
            'Reference' => $data['Reference'],
            'URLCompleted' => $this->client->api_completed_url,
            'URLError' => $this->client->api_error_url,
            'EndUserIP' => $this->getClientIp()
        ];

        /**
         * Generate the checksum for the request
         */
        $checksum = $this->client->generateChecksum(
            $this->client->api_endpoint .
            'payment/vaultcheckout' .
            $this->client->api_post .
            $this->client->api_key .
            $this->client->api_secret .
            json_encode($information)
        );

        /**
         * Make the call.
         */
        return $this->client->request($this->client->api_post, 'payment/vaultcheckout', $information, $checksum);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function autoCheckout($data)
    {
        /**
         * Information for continuing a recurring payment
         */
        $information = [
            'Timestamp' => $this->getTimeStamp(),
            'Amount' => $data['Amount'],
            'Country' => $data['Country'],
            'Currency' => $data['Currency'],
            'Description' => $data['Description'],
            'Issuer' => $data['Issuer'],
            'CustomerID' => $data['CustomerID'],
            'Language' => $data['Language'],
            'OrderID' => $data['OrderID'],
            'PaymentMethod' => $data['Paymentmethod'],
            'Reference' => $data['Reference'],
            'URLCompleted' => $this->client->api_completed_url,
            'URLError' => $this->client->api_error_url,
            'EndUserIP' => $this->getClientIp()
        ];

        /**
         * Generate the checksum for the request
         */
        $checksum = $this->client->generateChecksum(
            $this->client->api_endpoint .
            'payment/automaticcheckout' .
            $this->client->api_post .
            $this->client->api_key .
            $this->client->api_secret .
            json_encode($information)
        );

        /**
         * Make the call.
         */
        return $this->client->request($this->client->api_post, 'payment/automaticcheckout', $information, $checksum);
    }

    /**
     * @return mixed
     */
    public function getMyPaymentMethods()
    {
        /**
         * Information for getting active payment methods
         */
        $information = [
            'Timestamp' => $this->getTimeStamp()
        ];

        /**
         * Generate the checksum for the request
         */
        $checksum = $this->client->generateChecksum(
            $this->client->api_endpoint .
            'payment/getmypaymentmethods' .
            $this->client->api_post .
            $this->client->api_key .
            $this->client->api_secret .
            json_encode($information)
        );

        /**
         * Make the call.
         */
        return $this->client->request($this->client->api_post, 'payment/getmypaymentmethods', $information, $checksum);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getPayment($data)
    {
        /**
         * Information for getting active payment methods
         */
        $information = [
            'Timestamp' => $this->getTimeStamp(),
            'PaymentID' => $data['PaymentID']
        ];

        /**
         * Generate the checksum for the request
         */
        $checksum = $this->client->generateChecksum(
            $this->client->api_endpoint .
            'payment/getpayment' .
            $this->client->api_post .
            $this->client->api_key .
            $this->client->api_secret .
            json_encode($information)
        );

        /**
         * Make the call.
         */
        return $this->client->request($this->client->api_post, 'payment/getpayment', $information, $checksum);
    }
}
