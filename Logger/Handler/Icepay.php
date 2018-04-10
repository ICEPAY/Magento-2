<?php

namespace Icepay\IcpCore\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Icepay extends Base
{
    protected $fileName = '/var/log/icepay/icepay.log';
    protected $loggerType = Logger::DEBUG;
}