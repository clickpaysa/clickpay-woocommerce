const cpapplepay_settings = window.wc.wcSettings.getSetting('ccapplepay_data', {});
const cpapplepay_label = window.wp.htmlEntities.decodeEntities(cpapplepay_settings.title) || window.wp.i18n.__('ClickPay for WooCommerce', 'noon');
const cpbtn_apple = cpapplepay_settings.button_apple;
const cpapplepay_apple_pay_enabled = cpapplepay_settings.clickpay_apple_pay_enabled;

const cpapplepay_content = () => {
    return window.wp.htmlEntities.decodeEntities(cpapplepay_settings.description || '');
};

// const CPApplepay_Block_Gateway = {
//     name: 'ccapplepay',
//   	label: cpapplepay_label,
//     content: Object(window.wp.element.createElement)(cpapplepay_content, null ),
//     edit: Object(window.wp.element.createElement)(cpapplepay_content, null ),
//     canMakePayment: () => true,
//     ariaLabel: cpapplepay_label,
// 	  placeOrderButtonLabel: 'Pay Now with ClickPay',
//     supports: {
//         features: cpapplepay_settings.supports,
//     },
// };

const CPApplepay_Express_Block_Gateway = {
  
    name: 'clickpayexpress',    	
    content: '<div id=\"paybutton\""></div>',
	  content: Object(window.wp.element.createElement(() =>
      window.wp.element.createElement(
        "button",
        {
          className:'wc-block-components-button wp-element-button wc-block-components-checkout-place-order-button wc-block-components-checkout-place-order-button--full-width contained',
          type:'button',
          height:'15px',
          width:'20px',
          id:'cpapplepay_apple_pay_button',
          onClick: function() { clickpay_apple_pay(); return false; },
        },
        window.wp.element.createElement("img", {
          src: cpbtn_apple,
          alt: 'Apple Pay',	
		      height: '10px',
        }),
		))),
  	edit: Object(window.wp.element.createElement("button",{id:'cpapplepay_btn'})),
    canMakePayment: () => { return cpapplepay_apple_pay_enabled === 'yes' },
	  paymentMethodId: 'ccapplepay',    
    supports: {
        features: cpapplepay_settings.supports,
    },
};

window.wc.wcBlocksRegistry.registerExpressPaymentMethod( CPApplepay_Express_Block_Gateway );
//window.wc.wcBlocksRegistry.registerPaymentMethod( CPApplepay_Block_Gateway );
