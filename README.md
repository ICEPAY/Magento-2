![]( https://icepay.com/app/themes/icepay/dist/images/logos/logo_icepay.svg)

Magento 2 Payment Module
============================

ICEPAY Magento 2 Payment Module is a more robust and reliable and, at the same time, more powerful, flexible and secure version of our time-tested extension that enables the use of ICEPAY payment methods in your Magento2 webshop. Integrating ICEPAY with Magento 2 is simple and seamless for customers, and fast and easy for installation and maintenance at the back end.

Make payments in your Magento 2 webshop possible. Download the special Magento 2 webshop module [here](https://github.com/ICEPAY/Magento-2/releases) and you will be able to offer a vast variety of most frequently used national and international online payment methods and solutions for worldwide internet commerce.

License
-------

Our module is available under the BSD-2-Clause. See the [LICENSE](https://github.com/ICEPAY/Magento-2/blob/master/LICENSE.md) file for more information.

Installation
------------

### Install by Composer:

1. Go to the Magento 2 root folder

2. Add ICEPAY Payment Module repository to composer:

    ```bash
    composer config repositories.icepay git https://github.com/ICEPAY/Magento-2.git
    ```

3. Install the module:

    ```bash
    composer require icepay/icepay-magento2-module:dev-master
    ```
4. Enable the module:

    ```bash
    php bin/magento setup:upgrade
    php bin/magento setup:static-content:deploy
    ```
    
### Install by uploading files:

#### Download the module as "zip" archive

1. Download the latest release

2. Extract the archive to app/code/Icepay/IcpCore

3. Enable the module:

    ```bash
    php bin/magento setup:upgrade
    php bin/magento setup:static-content:deploy
    ```

#### Clone this repository

1. Go to app/code folder

2. Clone this repository

  * Use HTTPS:
    ```bash
    git clone https://github.com/ICEPAY/Magento-2.git Icepay/IcpCore
    ```
  * Alternatively, use SSH: 
    ```bash
    git clone git@github.com:ICEPAY/Magento-2.git Icepay/IcpCore
    ```
4. Enable the module:

    ```bash
    cd ../../
    php bin/magento setup:upgrade
    php bin/magento setup:static-content:deploy
    ```
   
User documentation
------------------

### Configuring the module
To configure the module, log in to your administrator backend.
1. Go to **Stores** -> **Configuration** -> **Sales** -> **Payment methods** and find and click on **ICEPAY** Settings under **OTHER PAYMENT METHODS**

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%201.png)

### Merchant Settings Section
To configure your **ICEPAY Module Configuration**, you must have a valid and approved ICEPAY merchant account.
1. Copy-paste the callback URL in **Step 1** from the URL for Success/Error/Postback field in the **ICEPAY** Module Configuration section to the corresponding fields of the Configure URL section of your website in the **ICEPAY** client [Portal](https://portal.icepay.com).

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%202.png)

By default, the callback handler is located on the following URL:

       http://www.yourwebshophostname.com/icepay/checkout/placeorder 
       http://www.yourwebshophostname.com/icepay/checkout/cancel 
       http://www.yourwebshophostname.com/icepay/postback/notification 


![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%203.png)

2. Login to the **ICEPAY** client [Portal](https://portal.icepay.com) and open the **Edit website** section of the website you wish to configure. If you cannot access the **ICEPAY** client [Portal](https://portal.icepay.com) or do not have a corresponding website available in the list of websites, please contact **ICEPAY** Customer Service on info@icepay.com.  
3. In the **Edit website** section you will find a **Merchant ID** and **Secret Code** values.

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%204.png)

4. Copy-paste the **Merchant ID** and **Secret Code** values into the corresponding input fields of the ICEPAY Module Configuration section in the **ICEPAY** **Magento 2** Online Payment Module configuration section **Step2**.

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%205.png)

Click on the **Save Config** button.

### Payment methods section
1. Go to **Sales** -> **ICEPAY** -> **Payment Methods**
Press **Sync** button to get payment methods available for your account and confirm it by clicking **OK** in the dialog box

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%206.png)

2. You will get a list of Payment methods available for your website

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%207.png)

By clicking the **Edit** -> **Select**, you can edit the **Display Name** of each available payment method, as well as **Enable/Disable** them. 

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%208.png)

To Select all the available payment methods, you can click on Bulk actions button and choose **Select all** option.

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%209.png)

Afterwards click on **Actions** -> **Enable** or **Disable** selection

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%2010.png)

> NB. You can retreive **ICEPAY** payment methods in a Multistore mode for different stores using a **Store view** switch.

![alt tag](https://github.com/ICEPAY/Magento-2/wiki/images/IMG%2011.png)

Click on **SYNC** button to retreive **ICEPAY** payment methods for an individual store.

At the moment, our module does not support multishipping.
    
Contributing
------------

* Fork it
* Create your feature branch (`git checkout -b my-new-feature`)
* Commit your changes (`git commit -am 'Add some feature'`)
* Push to the branch (`git push origin my-new-feature`)
* Create new Pull Request

Bug report
----------

If you found a repeatable bug, and troubleshooting tips didn't help, then be sure to [search existing issues](https://github.com/ICEPAY/Magento-2/issues) first. Include steps to consistently reproduce the problem, actual vs. expected results, screenshots, and your PrestaShop version and Payment module version number. Disable all other third party extensions to verify the issue is a bug in the Payment module.
