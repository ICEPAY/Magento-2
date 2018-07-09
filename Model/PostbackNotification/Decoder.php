<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Model\PostbackNotification;

use Magento\Framework;

/**
 */
class Decoder implements DecoderInterface
{
    /**s
     * Decodes the given $data string which is encoded in the x-www-form-urlencoded format.
     *
     * @param string $data
     * @return mixed
     */
    public function decode($data)
    {
        parse_str($data, $result);

        return $result;
    }
}
