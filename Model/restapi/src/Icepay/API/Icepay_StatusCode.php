<?php
/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Magento 2
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016-2018, ICEPAY B.V. All rights reserved.
 */

namespace Icepay\API;

class Icepay_StatusCode
{

    const OPEN = "OPEN";
    const AUTHORIZED = "AUTHORIZED";
    const VALIDATE = "VALIDATE";
    const ERROR = "ERR";
    const SUCCESS = "OK";
    const REFUND = "REFUND";
    const CHARGEBACK = "CBACK";
}
