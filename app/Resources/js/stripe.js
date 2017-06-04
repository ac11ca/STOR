var Checkout = function () {
    var reference = this, logo, locale;
    this.$selector = $('#checkout_stripe');   
    this.$token = $('#stripe_token') || null;
    this.$email = $('#email');
    this.$form = $('form');
    logo = this.$selector.data('logo') || null;
    locale = this.$selector.data('locale');
    this.handler = StripeCheckout.configure({
        key: this.$selector.data('key') || '',
        image: logo,
        locale: 'auto',
        email: this.$email.val(),
        token: function(token) {
            reference.$token.val(token.id);
            reference.$form.submit();
        }
    });
}


Checkout.prototype.checkoutClick = function (event) {
    this.handler.open({
            name: this.$selector.data('name'),
            description: this.$selector.data('description'),
            amount: this.$selector.data('amount'),
            zipCode: true,
            allowRememberMe: true
    });
    event.preventDefault();

};

$(document).ready(function ()  {
    var CheckoutWidget = new Checkout();

    $('#checkout_stripe').click( CheckoutWidget.checkoutClick.bind(CheckoutWidget) );   
    $(window).on('popstate', function() {
        CheckoutWidget.handler.close();
    });
});
