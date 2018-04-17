<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */
 
namespace Icepay\IcpCore\Model\ResourceModel\Paymentmethod;

/**
 * Subscription Collection
 */
class Collection extends
 \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'Icepay\IcpCore\Model\Paymentmethod',
            'Icepay\IcpCore\Model\ResourceModel\Paymentmethod'
        );
    }

    protected $_idFieldName = 'paymentmethod_id';


    /**
     * Filter collection by specified store ids
     *
     * @param int[]|int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId = null)
    {
        $this->addFieldToFilter('main_table.store_id', ['eq' => $storeId]);
        return $this;
    }


//    /**
//     * Add store availability filter. Include availability product
//     * for store website
//     *
//     * @param null|string|bool|int|Store $store
//     * @return $this
//     */
//    public function addStoreFilter($store = null)
//    {
//        if ($store === null) {
//            $store = $this->getStoreId();
//        }
//        $store = $this->_storeManager->getStore($store);
//
//        if ($store->getId() != Store::DEFAULT_STORE_ID) {
//            $this->setStoreId($store);
//            $this->_productLimitationFilters['store_id'] = $store->getId();
//            $this->_applyProductLimitations();
//        }
//
//        return $this;
//    }

//    /**
//     * Add website filter to collection
//     *
//     * @param null|bool|int|string|array $websites
//     * @return $this
//     */
//    public function addWebsiteFilter($websites = null)
//    {
//        if (!is_array($websites)) {
//            $websites = [$this->_storeManager->getWebsite($websites)->getId()];
//        }
//
//        $this->_productLimitationFilters['website_ids'] = $websites;
//        $this->_applyProductLimitations();
//
//        return $this;
//    }
}
