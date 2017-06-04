$(document).ready(function() {
    initializeIgLogin();
    modules_loaded.push(initializeIgLogin);
});

function initializeIgLogin() {
    var $loginbutton = $('.js-login');

    if($loginbutton.length > 0) {
        $loginbutton.off('click');
        $loginbutton.click(function(event) {igLogin(event, $(this));} );
    }

}

function igLogin(event, $button) {
    var href;
    var img = new Image();
    event.preventDefault();
    href = $button.attr('href');
    $button.text('Redirecting...');
    img.src = 'http://instagram.com/accounts/logout/';
    window.setTimeout(function () { window.location = href; }, 500);  
}
