<?php

namespace Icepay\IcpCore\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class PostbackUrlField extends \Magento\Config\Block\System\Config\Form\Field
{

    protected function _getElementHtml(AbstractElement $element)
    {
//        $store = $this->_storeManager->getStore();
//        if($store)
//        {
//
//            //$element->setValue($store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).'icepay/postback/notification');
//        }

        $element->setValue($this->_urlBuilder->getDirectUrl('rest/V1/icepay/postback'));

        $element->setReadonly('readonly');

        return $element->getElementHtml();

    }
}