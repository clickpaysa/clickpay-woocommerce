<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Dummy Payments Blocks integration
 *
 * @since 1.0.3
 */
final class WC_Gateway_Ccapplepay_Blocks_Support extends AbstractPaymentMethodType
{
  /**
   * Payment method name/id/slug.
   *
   * @var string
   */
  protected $name = 'ccapplepay';

  /**
   * Initializes the payment method type.
   */
  public function initialize()
  {
    $this->settings = get_option('woocommerce_ccapplepay_settings', []);

  }


  /**
   * Returns if this payment method should be active. If false, the scripts will not be enqueued.
   *
   * @return boolean
   */
  public function is_active()
  {
    return true;
    //return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
  }

  /**
   * Returns an array of scripts/handles to be registered for this payment method.
   *
   * @return array
   */
  public function get_payment_method_script_handles()
  {

    wp_register_script(
      'clickpay-apple-blocks-integration',
      plugin_dir_url(__DIR__) . 'blocks/ccapplepay_block.js',
      [
        'wc-blocks-registry',
        'wc-settings',
        'wp-element',
        'wp-html-entities',
        'wp-i18n',
      ],
      null,
      true
    );

    return ['clickpay-apple-blocks-integration'];
  }

  /**
   * Returns an array of key=>value pairs of data made available to the payment methods script.
   *
   * @return array
   */
  public function get_payment_method_data()
  {
    $title = "ClickPay - Applepay";
    if (isset($this->settings['title'])) {
      $title = $this->settings['title'];
    }
    $description = "Pay with Apple Pay";
    if (isset($this->settings['description'])) {
      $description = $this->settings['description'];
    }
    $clickpay_apple_pay_enabled = false;
    if (isset($this->settings['ap_enabled'])) {
      $clickpay_apple_pay_enabled = $this->settings['ap_enabled'];
    }

    return [
      'title' => $title,
      'button_apple' => CLICKPAY_IMAGES_URL . '/applepay.png',
      'description' => $description,
      'clickpay_apple_pay_enabled' => $clickpay_apple_pay_enabled,
    ];
  }
}