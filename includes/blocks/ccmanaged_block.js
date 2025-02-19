const managed_settings = window.wc.wcSettings.getSetting('ccmanaged_data', {});
const managed_label = window.wp.htmlEntities.decodeEntities(managed_settings.title) || window.wp.i18n.__('Managed Payment', 'clickpay');
const managed_clientkey = managed_settings.clientkey
const managed_cc_path = managed_settings.cciconpath
const managed_cc_blank = managed_settings.cciconblank

console.log(managed_cc_blank, managed_cc_path)

const managed_content = () => {
    return window.wp.htmlEntities.decodeEntities(managed_settings.description || '');
};
const managed_icon = managed_settings.icon;

// =========================================================================

const CustomInputField = ({ id, label,value,onChange }) => {
  return React.createElement('div', {style: {marginBottom: "8px"}},
  
    React.createElement('label', {style:{minWidth: "200px", display: "inline-block" }, htmlFor: id}, 
      label,React.createElement('span', {style: {color: "red", margingLeft: "10px", marginRight: "15px"} }, '*')),
  
    React.createElement('input', {type: 'text', id: id, name: id, required: true, 'data-paylib': 'number',
              placeholder: 'Card Number',value: value, onChange: onChange, maxLength: 23,
              style: {padding: "6px", fontSize: "0.9em", marginRight: "15px"}}),

    React.createElement('img', {id: "cc-icon", name: "cc-icon", alt: 'CC', src: managed_cc_blank,
      style: {padding: "2px", width: "50px", height: "22px"}}),

  );
};

const currentDate = new Date();
const currentMonth = currentDate.getMonth() + 1; 

const options = [
  { value: '01', text: '01' },
  { value: '02', text: '02' },
  { value: '03', text: '03' },
  { value: '04', text: '04' },
  { value: '05', text: '05' },
  { value: '06', text: '06' },
  { value: '07', text: '07' },
  { value: '08', text: '08' },
  { value: '09', text: '09' },
  { value: '10', text: '10' },
  { value: '11', text: '11' },
  { value: '12', text: '12' },
];

const currentYear = new Date().getFullYear();
const options_year = [];

for (let i = 0; i < 10; i++) {
  const year = currentYear + i;
  options_year.push({ value: String(year), text: String(year) });
}

const CustomInputField2 = ({ id,id2,label,value,onChange,value2,onChange2 }) => {
  return React.createElement('div', {style: {marginBottom: "8px"}},
  
    React.createElement('label', {style:{minWidth: "200px", display: "inline-block" }, htmlFor: id}, 
      label,React.createElement('span', { style: {color: "red", margingLeft: "10px", marginRight: "15px"} }, '*')),
  
      React.createElement('div', {style:{display: "inline-block" }},
  React.createElement('select',{style: {padding: "6px", fontSize: "0.9em", marginRight: "10px"}, 
      id: id, name: id, required: true,value: value, 'data-paylib': 'expmonth', onChange: onChange}, 
    options.map(option => React.createElement('option', { value: option.value, key: option.value }, option.text))),
  
  React.createElement('select', {style: {padding: "6px", fontSize: "0.9em"}, 
    id: id2, name: id2, required: true,value: value2, 'data-paylib': 'expyear', onChange: onChange2}, 
    options_year.map(option => React.createElement('option', { value: option.value,  key: option.value }, option.text))),
  )
  );
};

const CustomInputField3 = ({ id, label,value,onChange }) => {
  return React.createElement('div', {className: 'custom-input-field'},
  
  React.createElement('label', {style:{minWidth: "200px", display: "inline-block" }, htmlFor: id}, 
    label,React.createElement('span', { style: {color: "red", margingLeft: "10px", marginRight: "15px"} }, '*')),
  
  React.createElement('input', {type: 'text', id: id, name: id, required: true, 'data-paylib': 'cvv',
            placeholder: 'CVV',value: value, onChange: onChange, maxLength: 4,
            style: {padding: "6px", fontSize: "0.9em"}}),
  );
};

const clickpay_card_content = (props) => {

  const [cardNumber, setCardNumber] = React.useState('');

  const handleCardNumberChange = (event) => {
    const value = event.target.value;
    const formattedValue = value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
    
    const testvalue = value.replace(/ /g, "")
    var re = {
      electron: /^(4026|417500|4405|4508|4844|4913|4917)\d+$/,
      maestro: /^(5018|5020|5038|5612|5893|6304|6759|6761|6762|6763|0604|6390)\d+$/,
      visa: /^4[0-9]\d+$/,
      mastercard: /^5[1-5][0-9]\d+$/,
      amex: /^3[47][0-9]\d+$/,
      diners: /^3(?:0[0-5]|[68][0-9])[0-9]\d+$/,
      discover: /^6(?:011|5[0-9]{2})[0-9]\d+$/,
      jcb: /^(?:2131|1800|35\d{3})\d+$/
    }

    let cctype = "blank"
    for(var key in re) {
        if(re[key].test(testvalue)) {
            cctype = key
            break
        }
    }
 
    const imgicon = document.getElementById("cc-icon");
    imgicon.src = managed_cc_path + "/" + cctype + ".png"
        
    setCardNumber(formattedValue);
  };


  const [expirationDate, setExpirationDate] = React.useState('');

  const handleExpirationDateChange = (event) => {
      setExpirationDate(event.target.value);
  };

  const [expirationDate_year, setExpirationDate_year] = React.useState('');

  const handleExpirationDateYearChange = (event) => {
      setExpirationDate_year(event.target.value);
  };

  const [cardCode, setCardCode] = React.useState('');

  const handleCardCodeChange = (event) => {
      setCardCode(event.target.value);
  };

  const validateCreditCardDetails = (cNumber, cMonth, cYear, cCVV) =>
  {
    let isValid = true;
    if (isValid &&  cNumber.trim().length < 15 )
    {
      isValid = false;
    }

    if (isValid &&  cCVV.trim().length < 3 )
    {
      isValid = false;
    }
  
    if (isValid)
    {
      const currentMonth = currentDate.getMonth() + 1; 
      const currentYear = new Date().getFullYear();

      if (cYear > currentYear || (cYear == currentYear && cMonth >= currentMonth ))
        isValid = true;
      else
        isValid = true;
    }
    
    return isValid;    
  }

  function wait(ms) {
    return new Promise((resolve, reject) => setTimeout(resolve, ms));
  }

  React.useEffect( () => {
    const { onPaymentSetup } = props.eventRegistration;
    const unsubscribe = onPaymentSetup (  async () => {
        // const myGatewayCustomData = cardNumber;
        // const customDataIsValid = !! myGatewayCustomData.length;
        console.log("Card Number: ", cardNumber)
        const customDataIsValid = validateCreditCardDetails(cardNumber, expirationDate, expirationDate_year, cardCode);
        
        if (customDataIsValid == false)
        {
          return {
            type: props.emitResponse.responseTypes.ERROR,
            message: 'Invalid creditd card details.',
          };
        }

        console.log("Token Started")

        const response = await fetch(
          'https://randomuser.me/api/',
          {
            method: 'GET',
          }
        );

        if (!response.ok) {
          return {
            type: props.emitResponse.responseTypes.ERROR,
            message: 'Token could not be created',
          };
        }
        else
        {          
          const data = await response.json();
          console.log(data)
          //wait for another 5 seconds
          await wait(5000);
          console.log("Token End")
          return {
            type: props.emitResponse.responseTypes.SUCCESS            
          };
        }
                

        // console.log("Token Start")
        // //get token
        // var form = document.getElementById('managedform');
        // console.log(form);
				// paylib.inlineForm({
				// 	'key': managed_clientkey,
				// 	'form': form,
				// 	'autoSubmit': false,
				// 	'callback': function (response) {
        //     console.log(response);
				// 		  if (response.error) {							  
				// 			  paylib.handleError(form, document.getElementById('status'), response);
        //         return {
        //           type: props.emitResponse.responseTypes.ERROR,
        //           message: 'Invalid creditd card details.',
        //         };
				// 		  }
        //       else {
        //         console.log("Token success");
        //       }
				// 	  }
				// })

        

        // if ( customDataIsValid ) {
        //     return {
        //         type: props.emitResponse.responseTypes.SUCCESS,
        //         meta: {
        //             paymentMethodData: {
        //                 cardNumber,
        //                 expirationDate,
        //                 expirationDate_year,
        //                 cardCode
        //             },
        //         },
        //     };
        // }
  
        // return {
        //     type: props.emitResponse.responseTypes.ERROR,
        //     message: 'There was an error',
        // };
    } );
    // Unsubscribes when this component is unmounted.
    return () => {
        unsubscribe();
    };
  }, [ cardNumber,expirationDate,expirationDate_year,cardCode ] );


  return React.createElement('div', {id: 'managedform', style: {marginBottom: "20px"}},
    React.createElement('p', null, window.wp.htmlEntities.decodeEntities(managed_settings.description ||  '')),
    React.createElement(CustomInputField, {id: 'clickpay_card_number', label: 'Credit Card Number ',value: cardNumber, 
      onChange: handleCardNumberChange, oninput: "CPFormatCardNumber()" }),
    React.createElement(CustomInputField2, {id: 'clickpay_card_expiration_month',id2:"clickpay_card_expiration_year", 
      label: 'Expiration Date ',value: expirationDate, onChange: handleExpirationDateChange,
      value2: expirationDate_year, onChange2: handleExpirationDateYearChange}),
    React.createElement(CustomInputField3, {id: 'clickpay_card_code', label: 'Card security code ',value: cardCode, onChange: handleCardCodeChange })
    );
};




// =========================================================================


const CP_Block_Managed_Gateway = {
  name: 'ccmanaged',
  label: managed_label,
  content: Object(window.wp.element.createElement)(clickpay_card_content, null ),
  edit: Object(window.wp.element.createElement)(clickpay_card_content, null ),
  canMakePayment: () => true,
  ariaLabel: managed_label,
  supports: {
      features: managed_settings.supports,
  },
};

window.wc.wcBlocksRegistry.registerPaymentMethod( CP_Block_Managed_Gateway );

