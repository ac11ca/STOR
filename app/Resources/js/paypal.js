$(document).ready(function () {

    // Fetch the button you are using to initiate the PayPal flow
    var $paypalconfig = $('#paypal-config');
    var $paypalbutton = $('#paypal_button');    
    var token, amount, currency, locale;

    if($paypalconfig.length > 0) {
        token = $paypalconfig.data('token');        
        amount = $paypalconfig.data('amount');
        currency = $paypalconfig.data('currency') || 'USD';
        locale = $paypalconfig.data('locale') || 'en_US';
        // Create a Client component
        braintree.client.create({
          authorization: token
        }, function (clientErr, clientInstance) {
          // Create PayPal component
          braintree.paypal.create({
            client: clientInstance
          }, function (err, paypalInstance) {
            $paypalbutton.click(function (event) {
              event.preventDefault();
              // Tokenize here!
              paypalInstance.tokenize({
                flow: 'checkout', // Required
                amount: amount, // Required
                currency: currency, // Required
                locale: locale,
                enableShippingAddress: false,
              }, function (err, tokenizationPayload) {
                if(tokenizationPayload) {
                    console.log(tokenizationPayload);
                    $('#paypal_token').val(tokenizationPayload.nonce);
                    $('form').submit();
                } else {
                    console.log(err);
                }
              });
            });
          });
        });
    }

} );
