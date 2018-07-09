<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016-2018, ICEPAY B.V. All rights reserved.
 */

namespace Icepay\API;

class Icepay_Webservice_Base extends Icepay_Api_Base
{

    protected $service = 'https://connect.icepay.com/webservice/icepay.svc?wsdl';
    protected $client;

    /**
     * Make connection with the soap client
     *
     * @since 2.1.0
     * @access public
     * @return \Icepay_Webservice_Base
     */
    public function setupClient()
    {
        /* Return if already set */
        if ($this->client) {
            return $this;
        }

        /* Start a new client */
        $this->client = new \Icepay\API\Client();
        $this->client->setApiSecret($this->_secretCode);
        $this->client->setApiKey($this->_merchantID);
        $this->client->setCompletedURL($this->data->ic_urlcompleted);
        $this->client->setErrorURL($this->data->ic_urlerror);

        return $this;
    }

    /**
     * Return current timestamp in gmdate format
     *
     * @since 2.1.0
     * @access protected
     * @return string
     */
    public function getTimeStamp()
    {
        return gmdate("Y-m-d\TH:i:s\Z");
    }

    /**
     * Return IP Address
     *
     * @since 2.1.0
     * @access protected
     * @return string
     */
    protected function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Arrange the object in given order
     *
     * @since 1.0.2
     * @access public
     * @param object $object !required
     * @param array $order !required
     * @return object $obj
     */
    public function arrangeObject($object, $order = [])
    {

        if (!is_object($object)) {
            throw new \Exception("Please provide a valid Object for the arrangeObject method");
        }
        if (!is_array($order) || empty($order)) {
            throw new \Exception("Please provide a valid orderArray for the arrangeObject method");
        }

        $obj = new \stdClass();

        foreach ($order as $key) {
            $obj->$key = $object->$key;
        }
        return $obj;
    }

    /**
     * Inserts properties of sub object into mainobject as property
     *
     * @since version 1.0.2
     * @access public
     * @param object $mainObject !required
     * @param object $subObject !required
     * @param bool $arrange
     * @param array $order !required if $arrange == true
     * @return object $obj
     */
    public function parseForChecksum($mainObject, $subObject, $arrange = false, $order = [])
    {

        if (!is_object($mainObject)) {
            throw new \Exception("Please provide a valid Object");
        }

        $mainObject = $mainObject;

        $i = 1;

        $subObject = $this->forceArray($subObject);

        foreach ($subObject as $sub) {
            // $sub is always an object, just a double-check
            if (is_object($sub)) {
                if ($arrange) {
                    // Arrange object in right order
                    $sub = $this->arrangeObject($sub, $order);
                }

                // Inject each value of subObject into $obj as property for checksum
                foreach ($sub as $value) {
                    $mainObject->$i = $value;
                    $i++;
                }
            }
        }

        return $mainObject;
    }

    /**
     * Generates the checksum
     *
     * @since 2.1.0
     * @access public
     * @param object $obj
     * @param string $secretCode
     * @param bool $isautocheckout
     * @return string
     */
    public function generateChecksum($obj = null, $secretCode = null, $isautocheckout = false)
    {
        $arr = [];
        if ($secretCode) {
            array_push($arr, $secretCode);
        }

        foreach ($obj as $val) {
            $insert = $val;

            if (is_bool($val)) {
                if ($isautocheckout) {
                    $insert = ($val) ? 'True' : 'False';    // autocheckout function computes boolean checksum differently (first character uppercase)
                } else {
                    $insert = ($val) ? 'true' : 'false';
                }
            }

            array_push($arr, $insert);
        }

        return sha1(implode("|", $arr));
    }

    /**
     * Force object into array
     *
     * @since 2.1.0
     * @access protected
     * @param object $obj
     * @return array
     */
    protected function forceArray($obj)
    {
        if (is_array($obj)) {
            return $obj;
        }

        $arr = [];
        array_push($arr, $obj);
        return $arr;
    }
}
