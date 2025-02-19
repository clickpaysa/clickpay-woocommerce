<?php

use Automattic\WooCommerce\Utilities\OrderUtil;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

include_once CLICKPAY_DIR . 'includes/clickpay_functions.php';

if (!function_exists('wp_handle_upload')) {
  require_once(ABSPATH . 'wp-admin/includes/file.php');
}

/**
 * Gateway class
 */
class WC_Gateway_Clickpay extends WC_Payment_Gateway
{
  protected $msg = array();

  protected $_code = '';
  protected $_title = '';
  protected $_description = '';
  protected $_icon = null;

  protected $gateway_mode;
  protected $gateway_redirect;
  protected $redirect_page_id;
  protected $gateway_url;
  protected $profile_id;
  protected $server_key;
  protected $client_key;
  protected $category;
  protected $language;
  protected $payment_action;
  protected $allow_save_cards;
  protected $ap_enabled;
  protected $ap_merchant_id;

  protected $logger;

  public $supports = array(
    'products',
    'refunds'
  );

  private $payment_hosted = 'cchosted';
  private $payment_managed = 'ccmanaged';
  private $payment_applepay = 'ccapplepay';
  private $payment_hosted_applepay = 'cchostedapplepay';

  public function __construct()
  {
    global $wpdb;

    $this->id = "{$this->_code}";
    $this->method_title = $this->_title;
    $this->method_description = $this->_description;
    $this->icon = $this->getIcon();

    if ($this->id == $this->payment_managed)
      $this->has_fields = true;
    else
      $this->has_fields = false;

    $this->init_form_fields();

    $this->init_settings();

    $this->title = $this->settings['title'];
    $this->description = $this->settings['description'];
    $this->gateway_mode = $this->settings['gateway_mode'];
    $this->gateway_url = $this->settings['gateway_url'];
    $this->gateway_redirect = isset($this->settings['gateway_redirect']) ? $this->settings['gateway_redirect'] : 'noredirect';
    $this->redirect_page_id = $this->settings['redirect_page_id'];
    $this->profile_id = isset($this->settings['profile_id']) ? $this->settings['profile_id'] : '';
    $this->server_key = $this->settings['server_key'];
    $this->client_key = $this->settings['client_key'];
    $this->payment_action = isset($this->settings['payment_action']) ? $this->settings['payment_action'] : '';
    $this->allow_save_cards = isset($this->settings['allow_save_cards']) ? $this->settings['allow_save_cards'] : false;
    $this->ap_enabled = isset($this->settings['ap_enabled']) ? $this->settings['ap_enabled'] : false;
    $this->ap_merchant_id = isset($this->settings['ap_merchant_id']) ? $this->settings['ap_merchant_id'] : '';

    if ($this->id != $this->payment_hosted)
      $this->allow_save_cards = false;

    $this->msg['message'] = "";
    $this->msg['class'] = "";

    //handle the response returned from the payment gateway
    add_action('woocommerce_api_cpresponse' . $this->id, array($this, 'check_clickpay_response'));

    add_action('valid-clickpay-request-' . $this->id, array(&$this, 'SUCCESS'));

    //this action hook saves the settings
    if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
      add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
    } else {
      add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
    }

    add_action('woocommerce_receipt_' . $this->_code, array(&$this, 'receipt_page'));

    add_action('woocommerce_order_actions', array($this, 'add_capture_button_to_order_actions'));
    add_action('woocommerce_order_action_custom_capture', array($this, 'process_capture_payment'));

    // Show custom fields on the checkout page
    //add_action( 'woocommerce_after_order_notes', array( $this, 'display_custom_fields' ) );

    //used for apple pay processing
    if ($this->_code == $this->payment_applepay) {
      add_action('woocommerce_api_clickpayapplevalidate', array($this, 'clickpay_apple_pay_process_validate'));
      add_action('woocommerce_api_clickpayappleprocess', array($this, 'clickpay_apple_pay_process_payment'));

      add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    add_action('wp_enqueue_scripts', array($this, 'clickpay_register_scripts'));  //apple pay



    if ($this->settings['enabled'] == 'yes') //Update session cookies
    {
      $this->manage_session();
    }

    $this->logger = wc_get_logger();
  }



  function init_form_fields()
  {

    $this->form_fields = array(
      'enabled' => array(
        'title' => __('Enable/Disable', 'clickpay'),
        'type' => 'checkbox',
        'label' => __('Enable ClickPay', 'clickpay'),
        'default' => 'no'
      ),
      'title' => array(
        'title' => 'Title',
        'type' => 'text',
        'description' => 'Title to appear at checkout',
        'default' => 'Pay by cards or Apple Pay.',
      ),
      'description' => array(
        'title' => __('Description:', 'clickpay'),
        'type' => 'textarea',
        'description' => __('This controls the description which the user sees during checkout.', 'clickpay'),
        'default' => __('Pay securely through ClickPay.', 'clickpay')
      ),
      'gateway_mode' => array(
        'title' => __('Gateway Mode', 'clickpay'),
        'type' => 'select',
        'options' => array("0" => "Select", "test" => "Test", "live" => "Live"),
        'description' => __('Mode of gateway subscription.', 'clickpay')
      ),
      'gateway_url' => array(
        'title' => __('Gateway Url', 'clickpay'),
        'type' => 'text',
        'description' => __('Gateway Url to connect to (with ending slash)', 'clickpay')
      ),
      'profile_id' => array(
        'title' => __('Profile Id', 'clickpay'),
        'type' => 'text',
        'description' => __('Profile Id', 'clickpay')
      ),
      'server_key' => array(
        'title' => __('Sever Key', 'clickpay'),
        'type' => 'text',
        'description' => __('Server Key (case sensitive)', 'clickpay')
      ),
      'client_key' => array(
        'title' => __('Client Key', 'clickpay'),
        'type' => 'text',
        'description' => __('Client Key (case sensitive)', 'clickpay')
      ),
      'payment_action' => array(
        'title' => __('Payment Action', 'clickpay'),
        'type' => 'select',
        'options' => array("sale" => "Sale", "auth" => "Authorize"),
        'description' => __('Payment action - request Authorize or Sale ', 'clickpay')
      ),
      'redirect_page_id' => array(
        'title' => __('Return Page'),
        'type' => 'select',
        'options' => $this->get_pages('Select Page'),
        'description' => "Page to redirect to after processing payment."
      ),
      'language' => array(
        'title' => __('Language', 'clickpay'),
        'type' => 'select',
        'options' => array("ar" => "Arabic", "en" => "English"),
        'description' => __('Language', 'clickpay')
      ),
    );

    if ($this->id == $this->payment_hosted) {

      $this->form_fields['gateway_redirect'] = array(
        'title' => __('Redirect / iFrame', 'clickpay'),
        'type' => 'select',
        'options' => array("redirect" => "Redirect", "iframe" => "iFrame"),
        'description' => __('Redirect the customer or use an iframe.', 'clickpay')
      );

      $this->form_fields['allow_save_cards'] = array(
        'title' => __('Save Cards', 'clickpay'),
        'type' => 'checkbox',
        'label' => __('Save Cards', 'clickpay'),
        'default' => 'no'
      );
    }

    if ($this->id == $this->payment_hosted_applepay) {
      $this->form_fields['gateway_redirect'] = array(
        'title' => __('Redirect / iFrame', 'clickpay'),
        'type' => 'select',
        'options' => array("redirect" => "Redirect", "iframe" => "iFrame"),
        'description' => __('Redirect the customer or use an iframe.', 'clickpay')
      );
    }

    if ($this->id == $this->payment_applepay) {
      $this->form_fields['ap_enabled'] = array(
        'title' => __('Apple Pay Enabled', 'clickpay'),
        'type' => 'checkbox',
        'label' => __('Apple Pay Enabled', 'clickpay'),
        'default' => 'no'
      );

      $this->form_fields['ap_merchant_id'] = array(
        'title' => __('Apple Pay Merchant ID', 'clickpay'),
        'type' => 'text',
        'description' => __('Apple Pay Merchant ID (case sensitive)', 'clickpay')
      );

      $ap_cert_filename_only = "";
      $ap_key_filename_only = "";

      $cert_file = get_option('clickpay_ap_cert_file', '');
      $key_file = get_option('clickpay_ap_key_file', '');

      if ($cert_file != null && $cert_file != '')
        $ap_cert_filename_only = basename($cert_file);
      if ($key_file != null && $key_file != '')
        $ap_key_filename_only = basename($key_file);

      $filehelp = 'If file is not uploaded, then try the following: Add the statement "define(\'ALLOW_UNFILTERED_UPLOADS\', true);" to wp-config.php or use a 3rd party file upload plugin like "Lord of the Files"';

      $this->form_fields['ap_cert_file'] = array(
        'title' => __('Apple Certificate file', 'clickpay'),
        'type' => 'file',
        'id' => 'ap_cert_file_upload',
        'description' => 'File Name: ' . $ap_cert_filename_only . '<br/>' . $filehelp,
        'default' => '',
      );

      $this->form_fields['ap_key_file'] = array(
        'title' => __('Apple Key file', 'clickpay'),
        'type' => 'file',
        'id' => 'ap_key_file_upload',
        'description' => 'File Name: ' . $ap_key_filename_only . '<br/>' . $filehelp,
        'default' => '',
      );

    }

  }

  public function process_admin_options()
  {

    // Handle file upload and save the file URL
    if (isset($_FILES['woocommerce_ccapplepay_ap_cert_file']) && !empty($_FILES['woocommerce_ccapplepay_ap_cert_file']['name'])) {

      $uploaded_file = $_FILES['woocommerce_ccapplepay_ap_cert_file'];

      // Handle the file upload
      $upload = wp_handle_upload($uploaded_file, array('test_form' => false));

      if (isset($upload['file'])) {
        // Save the file URL to the settings
        update_option('clickpay_ap_cert_url', $upload['url']);
        update_option('clickpay_ap_cert_file', $upload['file']);
      }
    }

    if (isset($_FILES['woocommerce_ccapplepay_ap_key_file']) && !empty($_FILES['woocommerce_ccapplepay_ap_key_file']['name'])) {
      $uploaded_file = $_FILES['woocommerce_ccapplepay_ap_key_file'];

      // Handle the file upload
      $upload = wp_handle_upload($uploaded_file, array('test_form' => false));

      if (isset($upload['file'])) {
        // Save the file URL to the settings
        update_option('clickpay_ap_key_url', $upload['url']);
        update_option('clickpay_ap_key_file', $upload['file']);
      }

    }

    // Process other settings
    parent::process_admin_options();
  }

  /**
   * Admin Panel Options
   * - Options for bits like 'title' and availability on a country-by-country basis
   **/
  public function admin_options()
  {
    echo '<h3>' . __('ClickPay', 'clickpay') . '</h3>';
    echo '<p>' . __('A popular gateways for online shopping.') . '</p>';
    if (PHP_VERSION_ID < 70300) {
      echo "<h1 style=\"color:red;\">**Notice: ClickPay payments plugin requires PHP v7.3 or higher.<br />
	  		 		Plugin will not work properly below PHP v7.3 due to SameSite cookie restriction.</h1>";
    }
    echo '<table class="form-table">';
    $this->generate_settings_html();
    echo '</table>';
  }


  /**
   *  There maybe payment fields
   **/
  function payment_fields()
  {
    if ($this->description)
      echo wpautop(wptexturize($this->description));

    if ($this->id == $this->payment_managed) {
      echo '
        <form action="" id="clickpay_managedform" name="clickpay_managedform" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset id="wc-' . esc_attr($this->id) . '-clickpay-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
        ';

      // Add this action hook (optional)
      do_action('woocommerce_credit_card_form_start', $this->id);

      // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
      $html = '        
        <div class="form-row form-row-wide"><label>Card Number <span class="required">*</span></label>
        <input data-paylib="number" id="clickpay_ccnumber" type="text" autocomplete="off" maxlength="20">
        </div>
        <div class="form-row form-row-first">
          <label>Expiry Date <span class="required">*</span></label>
            <input data-paylib="expmonth" id="clickpay_expmonth" type="text" autocomplete="off" placeholder="MM" maxlength="2">
            <input data-paylib="expyear" id="clickpay_expyear" type="text" autocomplete="off" placeholder="YY" maxlength="4">
        </div>
        <div class="form-row form-row-last">
          <label>Card Code (CVC) <span class="required">*</span></label>
          <input data-paylib="cvv" id="clickpay_cvv" type="password" autocomplete="off" placeholder="CVC" maxlength="4">
        </div>
        <div id="status" style="margin: 20px;">
        <div class="clear"></div>'
      ;

      echo $html;

      do_action('woocommerce_credit_card_form_end', $this->id);

      echo '<div class="clear"></div></fieldset></form>';

    }
  }

  /**
   * Receipt Page
   **/
  function receipt_page($order)
  {
    echo '<p>' . __('Thank you for your order, please wait as you will be automatically redirected to ClickPay payments.', 'clickpay') . '</p>';
    echo $this->generate_clickpay_form($order);
  }

  /**
   * Process the payment and return the result
   **/
  function process_payment($order_id)
  {

    $order = new WC_Order($order_id);

    if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
      return array(
        'result' => 'success',
        'redirect' => add_query_arg(
          'order',
          $order->id,
          add_query_arg('key', $order->get_order_key(), $order->get_checkout_payment_url(true))
        )
      );
    } else {
      return array(
        'result' => 'success',
        'redirect' => add_query_arg(
          'order',
          $order->id,
          add_query_arg('key', $order->get_order_key(), get_permalink(get_option('woocommerce_pay_page_id')))
        )
      );
    }
  }

  /**
   * Check for valid clickpay pay response from the hosted payment page
   **/
  function check_clickpay_response()
  {
    global $woocommerce;

    $redirect_url = '';
    $is_valid = false;
    $response_data = $_POST;

    $trans_ref = filter_input(INPUT_POST, 'tranRef');

    if ($trans_ref && $trans_ref != null && $trans_ref != "") {
      $is_valid = ClickpayHelper::is_valid_redirect($response_data, $this->server_key);
    }

    //the orderId in the response is the clickpay payments order reference number
    if ($is_valid && isset($response_data['cartId']) && !empty($response_data['cartId'])) {

      $txnid = $response_data['cartId'];
      $order_id = explode('_', $txnid);
      $order_id = (int) $order_id[0];    //get rid of time part

      $order = new WC_Order($order_id);
      $action = $this->payment_action;

      $this->msg['class'] = 'error';
      $this->msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";

      //call inquiry and confirm transaction is successful
      $is_success = false;
      $response_inquiry = $this->payment_inquiry($trans_ref);

      if (isset($response_inquiry['status']) && $response_inquiry['status'] == 'success') {
        if (isset($response_inquiry['data']['payment_result']['response_status']))
          $is_success = $response_inquiry['data']['payment_result']['response_status'] === 'A';
      }

      if ($is_success) {

        $this->msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful for Order Id: $order_id <br/>
						We will be shipping your order to you soon.<br/><br/>";
        if ($this->payment_action == 'auth')
          $this->msg['message'] = "Thank you for shopping with us. Your payment has been authorized for Order Id: $order_id <br/>
						  We will be shipping your order to you soon.<br/><br/>";

        $this->msg['class'] = 'success';
        if ($order->get_status() == 'processing' || $order->get_status() == 'completed') {
          //do nothing
          $redirect_url = $order->get_checkout_order_received_url();
        } else {

          if ($this->payment_action == 'auth') {
            //mark the order on hold
            $order->update_status("on-hold");
            $order->add_order_note('ClickPay payments has authorized the payment. Ref Number: ' . $trans_ref);
            $order->add_order_note($this->msg['message']);
            $order->save();
            $woocommerce->cart->empty_cart();

            //save transactionid
            $order->update_meta_data('clickpay_transaction_ref', $trans_ref);
            $order->update_meta_data('clickpay_cart_id', $txnid);
            $order->save();

          } else {
            //complete the order
            $order->payment_complete($trans_ref);
            $order->add_order_note('ClickPay has processed the payment - ' . $action . ' Ref Number: ' . $trans_ref);
            $order->add_order_note($this->msg['message']);
            $order->add_order_note("Paid using ClickPay");
            $order->save();
            $woocommerce->cart->empty_cart();

            //save transactionid
            $order->update_meta_data('clickpay_transaction_ref', $trans_ref);
            $order->update_meta_data('clickpay_cart_id', $txnid);
            $order->save();
          }
          $redirect_url = $order->get_checkout_order_received_url();
        }

        try {
          //check if we have to save card details
          if ($this->allow_save_cards) {
            $cardDetails = isset($response_data["token"]) ? $response_data["token"] : null;
            if ($cardDetails != null && $cardDetails != "") {
              $this->save_card_details($response_data);
            }
          }
        } catch (Exception $e) {
          //error_log($e->getMessage());
        }

      } else {
        //failed
        $this->msg['class'] = 'error';
        $this->msg['message'] = "Thank you for shopping with us. However, the payment failed<br/><br/>";
        $order->update_status('failed');
        $order->add_order_note('Failed');
        $order->add_order_note($this->msg['message']);
      }
    }

    //manage msessages
    if (function_exists('wc_add_notice')) {
      wc_clear_notices();
      if ($this->msg['class'] != 'success') {
        wc_add_notice($this->msg['message'], $this->msg['class']);
      }
    } else {
      if ($this->msg['class'] != 'success') {
        $woocommerce->add_error($this->msg['message']);
      }
      $woocommerce->set_messages();
    }


    if ($redirect_url == '') {
      $redirect_url = ($this->redirect_page_id == "" || $this->redirect_page_id == 0) ? get_site_url() . "/" : get_permalink($this->redirect_page_id);
    }

    if ($this->gateway_redirect == 'iframe') {
      $html = '<script type="text/javascript">window.parent.location.href = "' . $redirect_url . '";</script>';
      echo $html;
    } else {
      wp_redirect($redirect_url);
    }
    exit;

  }

  // Save card details
  private function save_card_details($data)
  {
    //check if user is logged in      
    if (is_user_logged_in()) {
      $user_id = get_current_user_id();

      if ($user_id != null && is_numeric($user_id) && $user_id > 0) {
        $token = (isset($data['token'])) ? $data['token'] : '';
        $cardNumber = (isset($data['payment_info']['payment_description'])) ? $data['payment_info']['payment_description'] : '';
        $cardType = (isset($data['payment_info']['card_type'])) ? $data['payment_info']['card_type'] : '';
        $cardBrand = (isset($data['payment_info']['card_scheme'])) ? $data['payment_info']['card_scheme'] : '';
        $cdata = ["token" => $token, "cardNumber" => $cardNumber, "cardType" => $cardType, "cardBrand" => $cardBrand];
        $card = ["card" => $cdata];
        $json = json_encode($card);
        update_user_meta($user_id, 'user_clickpay_tokens', $json);
      }
    }
  }

  private function get_card_details()
  {
    //add the tokenized cards
    $token = "";

    if ($this->allow_save_cards && is_user_logged_in()) {
      $user_id = get_current_user_id();
      if ($user_id != null && is_numeric($user_id) && $user_id > 0) {
        $data = get_user_meta($user_id, "user_clickpay_tokens", true);

        if ($data != null) {
          $card = json_decode($data, true);
          if (isset($card['card']['token'])) {
            $token = $card['card']['token'];
          }
        }
      }
    }

    return $token;
  }

  //Payment Inquiry
  private function payment_inquiry($trans_ref)
  {
    $request_url = $this->gateway_url . 'payment/query';
    $data = [
      "tran_ref" => $trans_ref
    ];
    $response = ClickpayHelper::send_api_request($request_url, $data, $this->profile_id, $this->server_key);

    return $response;
  }

  /**
   * Generate payment button link
   **/
  public function generate_clickpay_form($order_id)
  {
    $html = "";

    if ($this->_code == $this->payment_hosted || $this->_code == $this->payment_hosted_applepay) {
      if ($this->gateway_redirect == 'redirect' || $this->gateway_redirect == 'iframe')
        $html = $this->generate_hosted_form($order_id);
    }

    return $html;
  }

  private function generate_hosted_form($order_id)
  {
    global $woocommerce;

    $order = new WC_Order($order_id);

    $order_currency = $order->get_currency();

    $txnid = $order_id . '_' . date("ymd") . ':' . rand(1, 100);

    $productInfo = "";
    $order_items = $order->get_items();
    foreach ($order_items as $item_id => $item_data) {
      $product = wc_get_product($item_data['product_id']);
      if ($product->get_sku() != "")
        $productInfo .= $product->get_sku() . " ";
    }
    if ($productInfo != "") {
      $productInfo = trim($productInfo);
      if (strlen($productInfo) > 50)
        $productInfo = substr($productInfo, 0, 50);
    } else
      $productInfo = "Product Info";

    $return_url = get_site_url() . "/";

    if ($this->id == $this->payment_hosted_applepay) {
      $return_url = get_site_url() . '/wc-api/cpresponsecchostedapplepay';
    } else {
      $return_url = get_site_url() . '/wc-api/cpresponsecchosted';
    }
    //$callback_url = get_site_url() . '/wc-api/clickpaycallback';

    $tran_class = "ecom";    //hard coded value

    $data = [
      "tran_type" => $this->payment_action,
      "tran_class" => $tran_class,
      "cart_id" => $txnid,
      "cart_currency" => $order->get_currency(),
      "cart_amount" => $order->get_total(),
      "cart_description" => $productInfo,
      "paypage_lang" => $this->language,
      "show_save_card" => ($this->allow_save_cards == 'yes') ? true : false,
      "callback" => null,  //webhook called by the server
      "return" => $return_url,      //url called after user completes the form
    ];

    if ($this->id == $this->payment_hosted_applepay) {
      $data["payment_methods"] = array("applepay");
    }

    if ($this->allow_save_cards == 'yes') {
      $data['tokenise'] = 2;
      $token = $this->get_card_details();
      if ($token != null && $token != "") {
        $data['token'] = $token;
      }
    }

    $customer_details = [
      "name" => $order->get_billing_first_name(),
      "email" => $order->get_billing_email(),
      "phone" => $order->get_billing_phone(),
      "street1" => $order->get_billing_address_1(),
      "city" => $order->get_billing_city(),
      "state" => $order->get_billing_state(),
      "country" => $order->get_billing_country(),
      "zip" => $order->get_billing_postcode()
    ];

    $shipping_details = [
      "name" => $order->get_shipping_first_name(),
      "email" => '',
      "phone" => '',
      "street1" => $order->get_shipping_address_1(),
      "city" => $order->get_shipping_city(),
      "state" => $order->get_shipping_state(),
      "country" => $order->get_shipping_country(),
      "zip" => $order->get_shipping_postcode()
    ];

    $plugin_info = [
      "cart_name" => "Woocommerce",
      "cart_version" => $woocommerce->version,
      "plugin_version" => "4.21.1"
    ];


    $data['customer_details'] = $customer_details;
    $data['shipping_details'] = $shipping_details;
    $data['plugin_info'] = $plugin_info;

    if ($this->gateway_redirect == "iframe") {
      $data["framed"] = true;
      $data["hide_shipping"] = true;
    }

    $request_url = $this->gateway_url . 'payment/request';
    $response = ClickpayHelper::send_api_request($request_url, $data, $this->profile_id, $this->server_key);

    $action = "";
    if ($response['status'] == 'success') {
      $rdata = $response['data'];
      if (isset($rdata['redirect_url']) && !empty($rdata['redirect_url'])) {
        $action = $rdata['redirect_url'];
      }
    }

    $html = "";
    $homeurl = get_home_url();

    if ($action != "") {
      if ($this->gateway_redirect == 'redirect') {
        $html = "
          <form action=\"" . $action . "\" method=\"post\" id=\"clickpayhosted_form\" name=\"clickpayhosted_form\">
            <button style='display:none' id='submit_clickpay_hosted_form' name='submit_clickpay_hosted_form'>Pay Now</button>
          </form>
          <script type=\"text/javascript\">document.getElementById(\"clickpayhosted_form\").submit();</script>
          ";
      } else {
        $html = '
          <script type="text/javascript">console.log("started");</script>
          <div id="clickpayif" style="display:none; cursor: default"> 
              <iframe id="framed" name="framed" width="800" height="600" src="' . $action . '"></iframe>
              <div style="clear:both"></div>
              <button id="cpifclose" name="cpifclose" onclick="closecpiframe()">Close</button>              
          </div>          
          <script type="text/javascript">
              console.log("Reached here");
              setTimeout(
                () => {                
                 console.log("Timer called");
                 jQuery.blockUI({ message: jQuery("#clickpayif"), css: { width: "800px", height: "550px", top: "10%", left: "20%" }, centerX: true, centerY: true });
                },
                5000
              )
              function closecpiframe()
              {
                console.log("close");
                jQuery.unblockUI(); 
                window.location.href = "' . $homeurl . '";
                return false;
              }
          </script>
        ';
      }
    } else {
      $html = "<h3>Error processing payment. Please try again</h3>";
    }

    return $html;
  }

  public function process_refund($order_id, $amount = null, $reason = '')
  {
    $order = wc_get_order($order_id);

    if (!$order) {
      return new WP_Error('error', __('Refund failed', 'woocommerce'));
    }

    $cart_id = $order->get_meta('clickpay_cart_id', true);
    $transaction_ref = $order->get_meta('clickpay_transaction_ref', true);

    if ($transaction_ref == null && $transaction_ref == "") {
      return new WP_Error('error', __('Refund not available', 'woocommerce'));
    } else if (!$order->get_transaction_id())   //transaction_id the one that was sent with payment request
    {
      return new WP_Error('error', __('Refund failed', 'woocommerce'));
    }

    try {
      $tran_class = "ecom";    //hard coded value

      $data = [
        "tran_type" => 'refund',
        "tran_class" => $tran_class,
        "cart_id" => $cart_id,
        "cart_currency" => $order->get_currency(),
        "cart_amount" => $amount,
        "cart_description" => "Shopping Cart",
        "tran_ref" => $transaction_ref,
      ];

      $request_url = $this->gateway_url . 'payment/request';
      $response = ClickpayHelper::send_api_request($request_url, $data, $this->profile_id, $this->server_key);

      $is_success = false;
      if (isset($response['status']) && $response['status'] == 'success') {
        if (isset($response['data']['payment_result']['response_status']))
          $is_success = $response['data']['payment_result']['response_status'] === 'A';
      }

      if ($is_success) {
        return true;
      } else
        return false;

    } catch (Exception $e) {
      return new WP_Error('error', __($e->getMessage(), 'woocommerce'));
    }

    return false;
  }

  function get_pages($title = false, $indent = true)
  {
    $wp_pages = get_pages('sort_column=menu_order');
    $page_list = array();
    if ($title)
      $page_list[] = $title;
    foreach ($wp_pages as $page) {
      $prefix = '';
      // show indented child pages?
      if ($indent) {
        $has_parent = $page->post_parent;
        while ($has_parent) {
          $prefix .= ' - ';
          $next_page = get_page($has_parent);
          $has_parent = $next_page->post_parent;
        }
      }
      // add to page list array array
      $page_list[$page->ID] = $prefix . $page->post_title;
    }
    return $page_list;
  }

  /**
   * Session patch CSRF Samesite=None; Secure
   **/
  function manage_session()
  {
    $context = array('source' => $this->id);
    try {
      if (PHP_VERSION_ID >= 70300) {
        $options = session_get_cookie_params();
        $options['samesite'] = 'None';
        $options['secure'] = true;
        unset($options['lifetime']);
        $cookies = $_COOKIE;
        foreach ($cookies as $key => $value) {
          if (!preg_match('/cart/', $key)) {
            setcookie($key, $value, $options);
          }
        }
      } else {
        $this->logger->error("clickpay payment plugin does not support this PHP version for cookie management.
												Required PHP v7.3 or higher.", $context);
      }
    } catch (Exception $e) {
      $this->logger->error($e->getMessage(), $context);
    }
  }

  public function clickpay_register_scripts()
  {
    if ((is_checkout() || is_cart()) && $this->ap_enabled === 'yes') {

      wp_enqueue_script(
        'apple-pay-sdk',
        'https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js',
        array('jquery'), // Dependencies (if any)
        null,
        true
      );

      $apple_script = CLICKPAY_URL . '/assets/js/clickpay-apple-pay.js';

      wp_enqueue_script(
        'woocommerce_clickpay_applepay',
        $apple_script,
        array('jquery'),
        null,
        true
      );

      $is_user_logged_in = is_user_logged_in();
      $cart_total = WC()->cart->get_total('raw');
      $currency = get_woocommerce_currency();
      $url = get_site_url() . '/wc-api/';
      $customer_login = get_permalink(wc_get_page_id('myaccount'));


      $clickpay_ap_vars = array(
        'country' => "SA",
        "total" => $cart_total,
        "currency" => $currency,
        "is_user_logged_in" => $is_user_logged_in,
        "api_url" => $url,
        "customer_login" => $customer_login,
      );

      wp_localize_script('woocommerce_clickpay_applepay', 'clickpay_ap_vars', $clickpay_ap_vars);
    }
  }

  function clickpay_apple_pay_process_validate()
  {
    $postdata = file_get_contents('php://input');
    $data = json_decode($postdata, true);
    $validation_url = $data['validationurl'];

    $cert_file = get_option('clickpay_ap_cert_file', '');
    $key_file = get_option('clickpay_ap_key_file', '');

    $domain = $_SERVER['SERVER_NAME'];

    $applepay_url = $validation_url;
    $applepay_data = [
      'merchantIdentifier' => $this->ap_merchant_id,
      'displayName' => "ApplePay Payment",
      'initiative' => "web",
      'initiativeContext' => $domain,
    ];

    error_log("Apple Pay Validation URL " . $applepay_url);
    error_log("Apple Pay Validation Request " . print_r($applepay_data, true));

    $response = ClickpayHelper::applepay_api_request($applepay_url, $applepay_data, $cert_file, $key_file);

    error_log("Apple Pay Validation Response " . print_r($response, true));

    if ($response['status'] != 'success') {
      wp_send_json(array("error" => "Error processing payment"));
    } else {
      $res = $response['data'];
      wp_send_json($res);
    }
  }

  function clickpay_apple_pay_process_payment()
  {

    global $woocommerce;

    $return_response = ["status" => "failure", "redirect_url" => "", "message" => "Error processing order"];

    $postdata = file_get_contents('php://input');
    $data = json_decode($postdata, true);
    $apple_token = isset($data['token']) ? $data['token'] : '';
    $orderid = isset($data['orderid']) ? $data['orderid'] : '';

    error_log("Process apple pay token " . print_r($data, true));

    $cart_total = WC()->cart->get_total('raw');
    $currency = get_woocommerce_currency();

    $txnid = $orderid . '_' . date("ymd") . ':' . rand(1, 100);

    $productInfo = "";
    $cart = WC()->cart->get_cart();
    foreach ($cart as $cart_item_key => $cart_item) {
      $product_id = $cart_item['product_id'];
      $product = wc_get_product($product_id);
      if ($product->get_sku() != "")
        $productInfo .= $product->get_sku() . " ";
    }
    if ($productInfo != "") {
      $productInfo = trim($productInfo);
      if (strlen($productInfo) > 50)
        $productInfo = substr($productInfo, 0, 50);
    } else
      $productInfo = "Product Info";

    $tran_class = "ecom";    //hard coded value

    $data = [
      "tran_type" => $this->payment_action,
      "tran_class" => $tran_class,
      "cart_id" => $txnid,
      "cart_currency" => $currency,
      "cart_amount" => $cart_total,
      "cart_description" => $productInfo,
      "return" => 'none',
    ];

    $customer_details = [];

    // Get current user information
    $current_user = wp_get_current_user();
    $user_name = $current_user->display_name;
    $user_email = $current_user->user_email;

    $customerBilling = WC()->customer->get_billing();

    if (
      isset($customerBilling) && $customerBilling != null &&
      isset($customerBilling['first_name']) && $customerBilling['first_name'] != null && $customerBilling['first_name'] != ""
    ) {
      $customer_details = [
        "name" => $customerBilling['first_name'] . ' ' . $customerBilling['last_name'],
        "email" => $customerBilling['email'],
        "phone" => $customerBilling['phone'],
        "street1" => $customerBilling['address_1'],
        "city" => $customerBilling['city'],
        "state" => $customerBilling['state'],
        "country" => $customerBilling['country'],
        "zip" => $customerBilling['postcode']
      ];
    } else {
      $customerShipping = WC()->customer->get_shipping();
      if (
        isset($customerShipping) && $customerShipping != null &&
        isset($customerShipping['first_name']) && $customerShipping['first_name'] != null && $customerShipping['first_name'] != ""
      ) {
        $customer_details = [
          "name" => $customerShipping['first_name'] . ' ' . $customerShipping['last_name'],
          "email" => $user_email,
          "phone" => '',
          "street1" => $customerShipping['address_1'],
          "city" => $customerShipping['city'],
          "state" => $customerShipping['state'],
          "country" => $customerShipping['country'],
          "zip" => $customerShipping['postcode']
        ];
      } else {
        $customer_details = [
          "name" => $user_name,
          "email" => $user_email,
          "phone" => '',
          "street1" => '',
          "city" => '',
          "state" => '',
          "country" => '',
          "zip" => ''
        ];
      }

    }

    $user_defined = [
      "udf1" => "Applepay",
    ];

    $plugin_info = [
      "cart_name" => "Woocommerce",
      "cart_version" => $woocommerce->version,
      "plugin_version" => "4.21.1"
    ];

    $data['customer_details'] = $customer_details;
    $data['apple_pay_token'] = $apple_token;
    $data['user_defined'] = $user_defined;
    $data['plugin_info'] = $plugin_info;

    error_log("Apple Pay Posted Data with Token " . print_r($data, true));

    $request_url = $this->gateway_url . 'payment/request';
    $response = ClickpayHelper::send_api_request($request_url, $data, $this->profile_id, $this->server_key);

    error_log("Apple pay response " . print_r($response, true));

    if ($response['status'] == 'success') {
      $is_success = false;
      if (isset($response['data']['payment_result']['response_status']))
        $is_success = $response['data']['payment_result']['response_status'] === 'A';

      //if success - create order from cart      
      if ($is_success) {
        $trxn_ref = $response['data']['tran_ref'];
        $redirect_url = $this->create_order($trxn_ref, $txnid);
        $return_response["status"] = "success";
        $return_response["message"] = "Order processed successfully";
        $return_response["redirect_url"] = $redirect_url;
      } else {
        $return_response["status"] = "error";
        $errmsg = "Error processing payment";
		if (isset($response['data']['payment_result']['response_message']))
        	$errmsg = $response['data']['payment_result']['response_message'];
        $return_response["message"] = $errmsg;
      }
      wp_send_json($return_response);

    } else {
      wp_send_json($return_response);
    }

  }

  function create_order($trxn_ref, $txnid)
  {
    if (is_user_logged_in()) {

      $user = wp_get_current_user();
      $order = wc_create_order();

      $cart = WC()->cart->get_cart();

      foreach ($cart as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];
        $order->add_product(wc_get_product($product_id), $quantity);
      }

      $order->set_address(WC()->customer->get_billing(), 'billing');
      $order->set_address(WC()->customer->get_shipping(), 'shipping');

      $order->set_status('pending');

      $order->calculate_totals();

      // Save the order
      $order->save();

      $order->payment_complete($trxn_ref);
      $order->add_order_note('ClickPay has processed the payment via Apple Pay. Ref Number: ' . $trxn_ref);
      $order->add_order_note("Paid using ClickPay - Apple Pay");
      $order->update_meta_data('clickpay_transaction_ref', $trxn_ref);
      $order->update_meta_data('clickpay_cart_id', $txnid);
      $order->save();

      // Optionally, you can empty the cart after creating the order
      WC()->cart->empty_cart();

      // Redirect to the order confirmation page
      return $order->get_checkout_order_received_url();
    }
    return "";
  }


  // Add Capture button to order actions
  public function add_capture_button_to_order_actions($actions)
  {

    global $theorder;

    if ($theorder->get_status() == 'on-hold') {
      $actions['custom_capture'] = __('ClickPay Capture Payment', 'woocommerce');
    }

    return $actions;
  }

  // Handle capture action
  public function process_capture_payment($order)
  {

    if ($order->get_status() != 'on-hold') {
      return false;
    }

    try {

      $cart_id = $order->get_meta('clickpay_cart_id', true);
      $transaction_ref = $order->get_meta('clickpay_transaction_ref', true);

      $tran_class = "ecom";    //hard coded value

      $data = [
        "tran_type" => 'capture',
        "tran_class" => $tran_class,
        "cart_id" => $cart_id,
        "cart_currency" => $order->get_currency(),
        "cart_amount" => $order->get_total(),
        "cart_description" => "Payment captured",
        "tran_ref" => $transaction_ref,
      ];

      $request_url = $this->gateway_url . 'payment/request';
      $response = ClickpayHelper::send_api_request($request_url, $data, $this->profile_id, $this->server_key);

      $is_success = false;
      if (isset($response['status']) && $response['status'] == 'success') {
        if (isset($response['data']['payment_result']['response_status']))
          $is_success = $response['data']['payment_result']['response_status'] === 'A';
      }

      if ($is_success) {
        $order->payment_complete($transaction_ref);
        $order->add_order_note('Payment captured successfully.');
        $order->update_status('processing');
        return true;
      } else {
        $order->add_order_note('Capture failed.');
        return false;

      }
    } catch (Exception $e) {
      return false;
    }

    return false;

  }

  public function getIcon()
  {
    $icon_name = $this->_icon ?? "{$this->_code}.png";

    $iconPath = CLICKPAY_DIR . "images/{$icon_name}";
    $icon = '';
    if (file_exists($iconPath)) {
      $icon = CLICKPAY_IMAGES_URL . "{$icon_name}";
    }

    return $icon;
  }

}

