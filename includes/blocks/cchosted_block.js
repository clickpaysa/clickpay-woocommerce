const hosted_settings = window.wc.wcSettings.getSetting('cchosted_data', {});
const hosted_label = window.wp.htmlEntities.decodeEntities(hosted_settings.title) || window.wp.i18n.__('Hosted Payment', 'clickpay');

// const hosted_content = () => {
//     return window.wp.htmlEntities.decodeEntities(hosted_settings.description || '');
// };

const hosted_content = () => {
      return window.wp.htmlEntities.decodeEntities(hosted_settings.description || '');
 };
  

const hosted_icon = hosted_settings.icon;

const CP_Block_Hosted_Gateway = {
    name: 'cchosted',
  	label: hosted_label,
    content: Object(window.wp.element.createElement)(hosted_content, null ),
    edit: Object(window.wp.element.createElement)(hosted_content, null ),
    canMakePayment: () => true,
    ariaLabel: hosted_label,
    supports: {
        features: hosted_settings.supports,
    },
};

window.wc.wcBlocksRegistry.registerPaymentMethod( CP_Block_Hosted_Gateway );

