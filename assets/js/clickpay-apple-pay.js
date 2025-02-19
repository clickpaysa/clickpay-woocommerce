jQuery(document).ready(function () {

});

function clickpay_apple_pay() {
  console.log("apple pay button clicked.");

  if (clickpay_ap_vars.is_user_logged_in == false) {
    alert("You are not logged in. You need to log in to use Express Checkout");
    window.location.href = clickpay_ap_vars.customer_login;
    return;
  }

  if (window.ApplePaySession) {

    console.log("Apple Pay Session");

    clickpay_apple_pay_process();
    
  }

}

function clickpay_apple_pay_process() {

  var request = {
    countryCode: clickpay_ap_vars.country,
    currencyCode: clickpay_ap_vars.currency,
    supportedNetworks: ['visa', 'masterCard', 'amex', 'discover', 'mada'],
    merchantCapabilities: ['supports3DS'],
    //supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
    lineItems: [{
      label: 'Amount',
      amount: clickpay_ap_vars.total,
    }
    ],
    total: { label: 'Apple Pay', amount: clickpay_ap_vars.total },
  }

  console.log("Request", request);

  var session = new ApplePaySession(14, request);

  var orderid = '';
  var vdata = '';

  session.begin();

  try {
    session.onvalidatemerchant = function (event) {
      
      console.log("starting session.onvalidatemerchant" + JSON.stringify(event) + "<br /> Validation URL " + event.validationURL + "<br />");

      $url_validate = clickpay_ap_vars.api_url + "clickpayapplevalidate";

      fetch($url_validate, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ "validationurl": event.validationURL })
      })
        .then(response => { console.log(response); return response.json(); })
        .then(data => {          
            console.log("Merchant Validation Session");
            console.log(data);
            session.completeMerchantValidation(data);                     
        })
        .catch(err => {
          console.error("validate merchant error", err)
        });
    }
  }
  catch (error) {
    console.log("On Validate Merchant Error: " + error);
    console.error('validate:', error);
  }

  try {
      session.onpaymentauthorized = function (event) {

      var applePaymentToken = event.payment.token;
		  
      console.log("Token" + applePaymentToken);

      $url_process = clickpay_ap_vars.api_url + "clickpayappleprocess";

      fetch($url_process, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ "token": applePaymentToken, "orderid": orderid })
        })
        .then(response => { console.log("apple pay process response"); console.log(response); return response.json();})
        .then(result => {
          console.log(result)
		  
          if (result.status == "success") {
            // Define ApplePayPaymentAuthorizationResult
            console.log("success");
			console.log("redirect url " + result.redirect_url);			
            window.location.href = result.redirect_url;
          }
          else
          {
            console.log("error processing token")
            alert(result.message)
          }		  
        })
        .catch(err => {
          console.error("authorize payment error", err)
        });
		
		console.log("apple session complete");
		session.completePayment(ApplePaySession.STATUS_SUCCESS);
    };	  
  }
  catch (error) {
    console.log("On Payment Authorized Error: " + error + "<br />");
    console.log('authorized:', error);
  }

  try {
    session.oncancel = function (event) {
      console.log("starting session.oncancel" + JSON.stringify(event) + "<br />");

      // Payment cancelled by WebKit
    };
  }
  catch (error) {
    console.log("On Cancel Error: " + error + "<br />");
    console.log('cancel :', error);
  }

}
