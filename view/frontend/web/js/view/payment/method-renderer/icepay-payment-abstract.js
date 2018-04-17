/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */
 
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Icepay_IcpCore/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, Component, setPaymentMethodAction, additionalValidators) {
    
        'use strict';

        return Component.extend({

            /** Redirect to icepay */
            continueToIcepay: function () {
                if (additionalValidators.validate()) {
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer);
                    return false;
                }
            }

        });
    }
);

