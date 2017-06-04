$(document).ready(function() {
    var $jsclickable = $('.js-clickable');
    var $jsback = $('.js-back');

    if($jsclickable.length > 0) {
        $jsclickable.click(function (event) {        
            var target = $(this).data('target');
            event.preventDefault();
            window.location = target;
        } );
    }

    if($jsback.length > 0) {
        $jsback.click(function (event) { 
            event.preventDefault();
            window.history.back();
        });
    }

} );
