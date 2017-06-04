var CartBar = function () {
    var that = this;
    this.$cartbar = $('.cart-bar');
    this.$spacer = $('.cart-bar-spacer');
    console.log('initialized');
}

CartBar.prototype.clearCart = function (event, $button) {
    var that = this;
    var clear_url = $button.data('clearurl');
    window.location = clear_url;
    /*$.ajax({
        url: clear_url
        ,method: 'get'        
        ,success: function (event,data) { that.cartCleared(event,data); }
        ,error: function (event,result) { that.cartClearError(event,result); }         
    });*/
};

CartBar.prototype.cartCleared = function(event, data) {
    var $overlays = $('.image-card .overlay');
    var $controls = $('.image-card .controls');
	var term = '';
    this.$cartbar.fadeOut(250); 
    this.$spacer.toggle('collapse');
    $('#main_modal').modal('hide');
    if($overlays.length > 0) {
        window.setTimeout(function() { $overlays.addClass('inactive'); },100);
        window.setTimeout(function() { $controls.addClass('inactive'); },100);
    }

    if($('.media-card').length > 0) {
        $('.media-card').each(function() {
            if(!$(this).hasClass('empty')) {
                term = $('#term').val() || '';

                $(this).html(
                    '<a href="/app/en/search/instagram?term=' + term + '"><img src="/dist/img/add_photos.png" class="animation-button"></a><p>Add more photos</p>'
                );
                $(this).addClass('empty');
                $('#next_button').fadeOut(250);
            }
        });
    }
    
};

CartBar.prototype.cartClearError = function(event, data) {
    console.log(data);
    if(data.length > 0) {
        addMessage(data[0].text, data[0].type, data[0].icon);                
    }   
};


$(document).ready(function() {
    initializeCartBar();
    modules_loaded.push(initializeCartBar);
});

function initializeCartBar() {
    var cartBar;
    if($('.cart-bar').length > 0) {
        cartBar = new CartBar();
    }
}

function hookup_clear_cart_event() {
    var $ok = $('#ok');

    if($('.cart-bar').length > 0) {
        cartBar = new CartBar();
    }

    $ok.click(function(event){cartBar.clearCart(event, $(this))});
}
