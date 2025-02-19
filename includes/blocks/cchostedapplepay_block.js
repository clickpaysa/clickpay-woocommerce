const hosted_ap_settings = window.wc.wcSettings.getSetting('cchostedapplepay_data', {});
const hosted_ap_label = window.wp.htmlEntities.decodeEntities(hosted_ap_settings.title) || window.wp.i18n.__('Hosted Payment', 'clickpay');

const hosted_ap_content = () => {
      return window.wp.htmlEntities.decodeEntities(hosted_ap_settings.description || '');
 };
  

const hosted_ap_icon = hosted_ap_settings.icon;

const CP_Block_Hosted_Apple_Gateway = {
    name: 'cchostedapplepay',
  	label: hosted_ap_label,
    content: Object(window.wp.element.createElement)(hosted_ap_content, null ),
    edit: Object(window.wp.element.createElement)(hosted_ap_content, null ),
    canMakePayment: () => true,
    ariaLabel: hosted_ap_label,
    supports: {
        features: hosted_ap_settings.supports,
    },
};

window.wc.wcBlocksRegistry.registerPaymentMethod( CP_Block_Hosted_Apple_Gateway );

