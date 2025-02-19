<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Dummy Payments Blocks integration
 *
 * @since 1.0.3
 */
final class WC_Gateway_Cchosted_Blocks_Support extends AbstractPaymentMethodType
{
	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'cchosted';
	
	
	/**
	 * Initializes the payment method type.
	 */
	public function initialize()
	{
		$this->settings = get_option('woocommerce_cchosted_settings', []);		

	}


	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active()
	{
		return true; 
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles()
	{
		
    wp_register_script(
      'clickpay-hosted-blocks-integration',
      plugin_dir_url(__DIR__) . 'blocks/cchosted_block.js',
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

    return ['clickpay-hosted-blocks-integration'];	
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data()
	{
		$title = "ClickPay Payment Gateway";
		if (isset($this->settings['title'])) {
			$title = $this->settings['title'];
		}
		
		$description = "Pay with ClickPay";
		if (isset($this->settings['description'])) {
			$description = $this->settings['description'];
		}
		return [
			'title' => $title,
			'icon' => plugins_url('images/clickpay.png',__FILE__),
			'description' => $description,
		];
	}
}
