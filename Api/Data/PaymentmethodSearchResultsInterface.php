<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Api\Data;

/**
 * Interface for payment method search results.
 * @api
 */
interface PaymentmethodSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get payment method list.
     *
     * @return \Icepay\IcpCore\Api\Data\PaymentmethodInterface[]
     */
    public function getItems();

    /**
     * Set payment method list.
     *
     * @param \Icepay\IcpCore\Api\Data\PaymentmethodInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
