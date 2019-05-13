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


        for(var paymentmethod in window.checkoutConfig.payment.icepay) {

            rendererList.push(
                {
                    type: 'icepay_icpcore_' + paymentmethod,
                    component: 'Icepay_IcpCore/js/view/payment/method-renderer/' + paymentmethod
                }
            );
        }

        /** Add view logic here if needed */
        return Component.extend({});
    }
);