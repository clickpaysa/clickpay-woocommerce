<h1 align="center">ClickPay - Woocommerce</h1>
<p align="center"><i>The Official Woocommerce plugin for ClickPay</i></p>
<div align="center">
   <h2 align="center">Plugin features</h2>
<h4>Direct Apple Pay</h4>
</div>

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

## Configure the Plugin for Direct Apple Pay on your website

1. Navigate to `"Magento admin panel" >> Stores >> Configuration`
2. Open `"Sales >> Payment Methods`
3. Select the Apple Pay method from the available list of ClickPay payment methods
4. Please find the setup section below for the apple pay certificate creation
5. Once certificates created upload the certificates in admin panel
6. Add the Merchnat identifier name

   <img width="809" alt="Screenshot 2024-02-28 at 11 02 44 AM" src="https://github.com/clickpaysa/clickpay-magento2.x/assets/135695828/75893cf4-5159-47c6-a5bf-7b7e3a200c62">

8. Enter the primary credentials:
   - **Profile ID**: Enter the Profile ID of your ClickPay account
   - **Server Key**: `Merchant’s Dashboard >> Developers >> Key management >> Server Key`
   - **Client Key**: `Merchant’s Dashboard >> Developers >> Key management >> Server Key`
9. Click `Save Config`

   
## Use iFrame

---

1. In the configuration page select Payment form type: **iFrame**.
2. Save the configuration.

## Log Access

### Clickpay custome log

1. Access `debug_clickpay.log` file found at: `/wp-content/debug_clickpay.log`

---

Done


