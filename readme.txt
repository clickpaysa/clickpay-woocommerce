=== WooCommerce_Clickpay ===
Contributors: ()
Donate Link:
Tags: payment, gateway, clickpay
Requires at least: 6.7
Tested: 6.7.1
Stable tag: 1.0.0
Requires PHP: 8.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

ClickPay gateway, plugin for WooCommerce Subscription payments.


== Description ==

Caution: Always keep backup of your existing WooCommerce installation including Mysql Database, before installing a new module.

The plugin zip can be easily installed using Wordpress's upload plugin feature.

== Frequently Asked Questions ==

= Does this plugin support checkout block? =

Yes, this plugin doese not supports checkout block.

= Is there any dependency on other specific plugin apart from WooCommerce? =

Yes. This plugin needs WooCommerce, along with WooCommerce Subscription as that is the sole purpose of the plugin to facilitate subscription payments. Apart from the above plugins, it does not depend directly on any other plugin.

= Direct Apple Pay

Direct Apple Pay requires the certificate and key files to be uploaded. If you face any issues in uploading the files, then try the following:
1. Add the statement "define('ALLOW_UNFILTERED_UPLOADS', true);" to wp-config.php
2. Use a 3rd party file upload plugin like "Lord of the Files"

== Screenshots ==

== Changelog ==
= 4.21.1 =
New plugin developed for WooCommerce v9.3.3.
