<?php

namespace Icepay\IcpCore\Api;

/**
 * @api
 */
interface PostbackNotificationInterface
{
    /**
     * @return string
     *
     * @api
     */
    public function processGet();

    /**
     * @return string
     *
     * @api
     */
    public function processPostbackNotification();
}
