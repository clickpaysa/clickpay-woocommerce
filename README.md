# Clickpay - WooCommerce

Official WooCommerce plugin for Clickpay

---

## Installation

*Note:* **WooCommerce** must be installed and activated for Clickpay plugin to work.

### Install using FTP method

1. Download the latest release of the plugin
2. Upload the folder `clickpay-woocommerce` to the wordpress installation directory: `wp-content/plugins/`

*Note: Delete any previous Clickpay plugin.*

### Install using WordPress Admin panel

1. Download the latest release of the plugin
2. Go to `"WordPress admin panel" >> Plugins >> Add New`
3. Select `Upload Plugin`
4. Click `Browse` and select the downloaded zip file (`clickpay-woocommerce.zip`)
5. Click `Install Now`
6. If a previous version exists, select `Replace current with uploaded`

---

## Activating the Plugin

1. Go to `"Wordpress admin panel" >> Plugins >> Installed Plugins`
2. Look for `Clickpay - WooCommerce Payment Gateway` and click `Activate`

---

## Configure the Plugin

1. Go to `"WordPress admin panel" >> WooCommerce >> Settings`
2. Select `Payments` tab
3. Select the preferred payment method from the available list of Clickpay payment methods
4. Check the `Enable Payment Gateway`
5. Enter the primary credentials:
   - **Profile ID**: Enter the Profile ID of your Clickpay account
   - **Server Key**: `Merchant’s Dashboard >> Developers >> Key management >> Server Key`
6. Click `Save changes`

## Use Auth - Capture - Void

1. In the configuration page select transaction type: **Auth**.
2. The default order-status for **Auth** Orders is **on-hold** unless you change it from the configuration page.
3. To **Capture** an **Auth** order you need to go to the order edit view >> change the order status to **Completed** then Save, the **Capture** will be done.
4. To **Void** the **Auth** order, you need to go to the order edit view >> change the order status to **Cancelled** then Save, the **Void** will be done.

## Use iFrame

---

1. In the configuration page select Payment form type: **iFrame**.
2. Save the configuration.

## Log Access

### Clickpay custome log

1. Access `debug_clickpay.log` file found at: `/wp-content/debug_clickpay.log`

---

Done
