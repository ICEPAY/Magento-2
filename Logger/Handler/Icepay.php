<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Icepay extends Base
{
    protected $fileName = '/var/log/icepay/icepay.log';
    protected $loggerType = Logger::DEBUG;
}
