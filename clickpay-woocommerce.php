<?php

/**
 * @package Clickpay_PayPage
 */

/**
 * Plugin Name:   Clickpay - WooCommerce Payment Gateway
 * Plugin URI:    https://clickpay.com.sa/
 * Description:   Clickpay is a <strong>3rd party payment gateway</strong>. Ideal payment solutions for your internet business.

 * Version:       5.0.0
 * Requires PHP:  7.0
 * Author:        Clickpay
 * Author URI:    support@clickpay.com
 */

if (!function_exists('add_action')) {
  exit;
}


define('CLICKPAY_PAYPAGE_VERSION', '5.0.0');
define('CLICKPAY_PAYPAGE_DIR', plugin_dir_path(__FILE__));
define('CLICKPAY_PAYPAGE_URL', plugins_url("/", __FILE__));
define('CLICKPAY_PAYPAGE_ICONS_URL', plugins_url("icons/", __FILE__));
define('CLICKPAY_PAYPAGE_IMAGES_URL', plugins_url("images/", __FILE__));
define('CLICKPAY_DEBUG_FILE', WP_CONTENT_DIR . "/debug_clickpay.log");
define('CLICKPAY_HTACCESS_FILE', WP_CONTENT_DIR . "/.htaccess");
define('CLICKPAY_DEBUG_FILE_URL', get_bloginfo('url') . "/wp-content/debug_clickpay.log");

define('CLICKPAY_PAYPAGE_METHODS', [
  'mada'       => 'WC_Gateway_Clickpay_Mada',
  'all'        => 'WC_Gateway_Clickpay_All',
  'creditcard' => 'WC_Gateway_Clickpay_Creditcard',
  'applepay'   => 'WC_Gateway_Clickpay_Applepay',
  'amex'       => 'WC_Gateway_Clickpay_Amex',
  'tabby'       => 'WC_Gateway_Clickpay_Tabby',
]);

require_once CLICKPAY_PAYPAGE_DIR . 'includes/clickpay_core.php';
require_once CLICKPAY_PAYPAGE_DIR . 'includes/clickpay_functions.php';

// Plugin activated
register_activation_hook(__FILE__, 'woocommerce_clickpay_activated');

// Load plugin function when woocommerce loaded
add_action('plugins_loaded', 'woocommerce_clickpay_init', 0);

//

function woocommerce_clickpay_init()
{

  if (!class_exists('WooCommerce') || !class_exists('WC_Payment_Gateway')) {
    add_action('admin_notices', 'woocommerce_clickpay_missing_wc_notice');
    return;
  }

  define('WooCommerce2', !woocommerce_clickpay_version_check('3.0'));

  // PT
  require_once CLICKPAY_PAYPAGE_DIR . 'includes/clickpay_payment_methods.php';
  require_once CLICKPAY_PAYPAGE_DIR . 'includes/clickpay_gateways.php';
  require_once CLICKPAY_PAYPAGE_DIR . 'includes/clickpay_payment_token.php';
  require_once CLICKPAY_PAYPAGE_DIR . 'includes/widgets/valu.php';


  /**
   * Add the Gateway to WooCommerce
   **/
  function woocommerce_add_clickpay_gateway($gateways)
  {
    $clickpay_gateways = array_values(CLICKPAY_PAYPAGE_METHODS);
    $gateways = array_merge($gateways, $clickpay_gateways);

    return $gateways;
  }

  function clickpay_filter_gateways($load_gateways)
  {
    if (is_admin()) return $load_gateways;

    $gateways = [];
    $currency = get_woocommerce_currency();

    foreach ($load_gateways as $gateway) {

      $code = array_search($gateway, CLICKPAY_PAYPAGE_METHODS);

      if ($code) {
        $allowed = ClickpayHelper::paymentAllowed($code, $currency);
        if ($allowed) {
          $gateways[] = $gateway;
        }
      } else {
        // Not Clickpay Gateway
        $gateways[] = $gateway;
      }
    }

    return $gateways;
  }


  /**
   * Add URL link to Clickpay plugin name pointing to WooCommerce payment tab
   */
  function clickpay_add_action_links($links)
  {
    $settings_url = admin_url('admin.php?page=wc-settings&tab=checkout');

    $links[] = "<a href='{$settings_url}'>Settings</a>";

    return $links;
  }

  add_filter('woocommerce_payment_gateways', 'woocommerce_add_clickpay_gateway');
  add_filter('woocommerce_payment_gateways', 'clickpay_filter_gateways', 10, 1);
  add_filter('woocommerce_payment_methods_list_item', 'get_account_saved_payment_methods_list_item_clickpay', 10, 2);
  add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'clickpay_add_action_links');

  add_action('woocommerce_single_product_summary', 'valu_widget', 21);

  add_action('woocommerce_blocks_loaded', 'woocommerce_gateway_clickpay_woocommerce_block_support');
  function woocommerce_gateway_clickpay_woocommerce_block_support()
  {
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
      require_once 'includes/blocks/class-wc-clickpay-payments-blocks.php';
      add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
          $payment_method_registry->register(new WC_Gateway_Clickpay_Blocks_Support());
        }
      );
    }
  }

  function valu_widget()
  {
    $enabled_gateways = WC()->payment_gateways->get_available_payment_gateways();

    if (array_key_exists('clickpay_valu', $enabled_gateways)) {
      $valu_payment = $enabled_gateways['clickpay_valu'];

      $valu_widget = new ValuWidget();
      $valu_widget->init($valu_payment);
    }
  }
}


function woocommerce_clickpay_activated()
{
  ClickpayHelper::log("Activate hook.", 1);
  woocommerce_clickpay_check_log_permission();
}
