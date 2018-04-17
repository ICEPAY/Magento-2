/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

/* eslint-disable no-undef */
// jscs:disable jsDoc

require([
    'jquery',
    'tinymce',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    //'loadingPopup',
    'mage/backend/floating-header'
], function (jQuery, tinyMCE, confirm) {
    'use strict';

    function syncPaymentMethods(url)
    {
        confirm({
            content: 'Are you sure you want to retrieve payment method information from ICEPAY payment gateway?',
            actions: {
                confirm: function () {
                    location.href = url;
                }
            }
        });
    }

     window.syncPaymentMethods = syncPaymentMethods;

});
