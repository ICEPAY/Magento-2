<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2015-2018, ICEPAY B.V. All rights reserved.
 */

namespace Icepay\API;

require_once("Icepay_StatusCode.php");
require_once("Icepay_Api_Logger.php");
require_once("Icepay_PaymentObject_Interface_Abstract.php");
require_once("Icepay_Basic_Paymentmethod_Interface_Abstract.php");
require_once("Icepay_WebserviceTransaction_Interface_Abstract.php");
require_once("Icepay_Parameter_Validation.php");
require_once("Icepay_Webservice_Filtering.php");
require_once("Icepay_Webservice_Paymentmethod.php");
require_once("Icepay_TransactionObject.php");
require_once("Icepay_Api_Base.php");
require_once("Icepay_Webservice_Base.php");
require_once("Icepay_Webservice_Pay.php");
require_once("Icepay_PaymentObject.php");
require_once("Icepay_Result.php");
require_once("Icepay_Postback.php");
require_once("Client.php");
require_once("Resources/BaseApi.php");
require_once("Resources/Payment.php");
require_once("Resources/Refund.php");
class Autoloader
{
    //TODO
}
