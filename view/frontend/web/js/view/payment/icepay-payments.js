/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'icepay_icpcore_creditcard',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/creditcard'
            },
            {
               type: 'icepay_icpcore_ideal',
               component: 'Icepay_IcpCore/js/view/payment/method-renderer/ideal'
            },
            {
                type: 'icepay_icpcore_paypal',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/paypal'
            },
            {
                type: 'icepay_icpcore_giropay',
                    component: 'Icepay_IcpCore/js/view/payment/method-renderer/giropay'
            },
            {
                type: 'icepay_icpcore_directdebit',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/directdebit'
            },
            {
                type: 'icepay_icpcore_giftcard',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/giftcard'
            },
            {
                type: 'icepay_icpcore_mistercash',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/mistercash'
            },
            {
                type: 'icepay_icpcore_paysafecard',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/paysafecard'
            },
            {
                type: 'icepay_icpcore_phone',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/phone'
            },
            {
                type: 'icepay_icpcore_sms',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/sms'
            },
            {
                type: 'icepay_icpcore_wiretransfer',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/wiretransfer'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);