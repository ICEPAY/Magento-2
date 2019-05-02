<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Block\Adminhtml\Paymentmethod\Sync;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SyncButton implements ButtonProviderInterface
{

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    ) {
        $this->authorization = $context->getAuthorization();
        $this->storeManager = $context->getStoreManager();
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->authorization->isAllowed('Icepay_IcpCore::paymentmethod_save')) {
            $data = [
                'label' => __('Sync'),
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to retrieve payment method information from ICEPAY payment gateway?'
                    ) . '\', \'' . $this->getSyncUrl() . '\', {data: {form_key: window.FORM_KEY}})',
                'class' => 'primary',
                'sort_order' => 10,
            ];
        }
        return $data;
    }

    /**
     * Get URL for sync button
     *
     * @return string
     */
    public function getSyncUrl()
    {
        $params = ['_current' => true, '_query' => ['isAjax' => null]];

        if ($this->context->getRequest()->getParam('store')) {
            $params['store'] = (int)$this->context->getRequest()->getParam('store');
        } elseif ($this->context->getRequest()->getParam('website')) {
            $params['website'] = (int)$this->context->getRequest()->getParam('website');
        }

        return $this->getUrl('*/*/sync', $params);
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
