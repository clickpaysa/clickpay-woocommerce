<?php
/*
Plugin Name: Clickpay - WooCommerce Payment Gateway
Plugin URI:    https://clickpay.com.sa/
Description:   Clickpay is a <strong>3rd party payment gateway</strong>. Ideal payment solutions for your internet business.
Version: 4.21.1
Author: Clickpay
Author URI: support@clickpay.com
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

define('CLICKPAY_VERSION', '1.0.0');
define('CLICKPAY_DIR', plugin_dir_path(__FILE__));
define('CLICKPAY_URL', plugins_url("/", __FILE__));
define('CLICKPAY_IMAGES_URL', plugins_url("images/", __FILE__));

define('CLICKPAY_METHODS', [
  'cchosted' => 'WC_Gateway_Clickpay_CC_Hosted',
  'ccmanaged' => 'WC_Gateway_Clickpay_CC_Managed',
  'ccapplepay'   => 'WC_Gateway_Clickpay_Applepay',
  'cchostedapplepay'   => 'WC_Gateway_Clickpay_HostedApplepay',
]);

require_once CLICKPAY_DIR . 'includes/clickpay_functions.php';

// Plugin activated
register_activation_hook(__FILE__, 'woocommerce_clickpay_activated');

// Load plugin function when woocommerce loaded
add_action('plugins_loaded', 'woocommerce_clickpay_init', 0);


function woocommerce_clickpay_init()
{

  if (!class_exists('WooCommerce') || !class_exists('WC_Payment_Gateway')) {
    add_action('admin_notices', 'woocommerce_clickpay_missing_wc_notice');
    return;
  }

  require_once CLICKPAY_DIR . 'includes/clickpay_payment_methods.php';
  require_once CLICKPAY_DIR . 'includes/clickpay_gateways.php';

  /**
   * Add the Gateway to WooCommerce
   **/
  function woocommerce_add_clickpay_gateway($gateways)
  {
    $clickpay_gateways = array_values(CLICKPAY_METHODS);
    $gateways = array_merge($gateways, $clickpay_gateways);
   
    return $gateways;
  }

  function clickpay_filter_gateways($load_gateways)
  {
    if (is_admin()) return $load_gateways;

    $gateways = [];
    $currency = get_woocommerce_currency();

    foreach ($load_gateways as $gateway) {

      $code = array_search($gateway, CLICKPAY_METHODS);

      if ($code) {
        $allowed = true;      //do any processing to allow / disallow methods
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

  add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'clickpay_add_action_links');


  add_action('woocommerce_blocks_loaded', 'woocommerce_gateway_clickpay_woocommerce_block_support');
  
  
  function woocommerce_gateway_clickpay_woocommerce_block_support()
  {
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
      require_once CLICKPAY_DIR . 'includes/blocks/cchosted-block.php';
	    require_once CLICKPAY_DIR . 'includes/blocks/ccmanaged-block.php';
      require_once CLICKPAY_DIR . 'includes/blocks/ccapplepay-block.php';
      require_once CLICKPAY_DIR . 'includes/blocks/cchostedapplepay-block.php';
	  
      add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
          $payment_method_registry->register(new WC_Gateway_Cchosted_Blocks_Support);
        }
      );
	  
	    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
          $payment_method_registry->register(new WC_Gateway_Ccmanaged_Blocks_Support);
        }
      );

      add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
          $payment_method_registry->register(new WC_Gateway_Ccapplepay_Blocks_Support);
        }
      );

      add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
          $payment_method_registry->register(new WC_Gateway_Cchostedapplepay_Blocks_Support);
        }
      );
    }

  }

}

function woocommerce_clickpay_missing_wc_notice()
{
    echo '<div class="error"><p><strong>Clickpay requires WooCommerce to be installed and active.</strong></p></div>';
}

function woocommerce_clickpay_activated()
{
  ClickpayHelper::log("Clickpay Activated.");
}


