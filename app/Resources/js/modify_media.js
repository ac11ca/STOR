var MediaCart = function () {
    var that = this;
    this.$remove_media_buttons = $('.js-reduce-media');
    this.$add_media_buttons = $('.js-add-media');
    this.$total_media = $('.js-total-media'); 
    this.$view = $('.media-cart-view');
    this.$add_media_buttons.off('click');
    this.$images = $('.image-card');
    this.$selectedImage = null;
    this.$remove_media_buttons.click(function (event) { that.modifyMediaQuantity(event, $(this),'remove'); });
    this.$add_media_buttons.click(function (event) { that.modifyMediaQuantity(event, $(this),'add'); });
    this.$images.click(function (event) { that.modifyMediaQuantity(event, $(this),'add'); });
    this.add_url = this.$view.data('addurl');
    this.remove_url = this.$view.data('removeurl');
    this.max_quantity = this.$view.data('maxquantity');
};

MediaCart.prototype.modifyCart = function(result) {
    
    var media_id = '#media_' + result.data.media_id;

    if(result.data.cart_data.total < this.max_quantity) {
        this.$add_media_buttons.prop('disabled', false);
    } else {
        this.$add_media_buttons.prop('disabled', true);
    }

    if(result.data.cart_data.total < 1 || (result.data.cart_data.media[result.data.media_id] && result.data.cart_data.media[result.data.media_id]['quantity'] < 1)) {
        this.$remove_media_buttons.prop('disabled', true);
        if(!$('.overlay', media_id).hasClass('inactive'))
            $('.overlay', media_id).addClass('inactive');
        if(!$('.controls', media_id).hasClass('inactive'))
            $('.controls', media_id).addClass('inactive');  

        if(result.data.cart_data.total < 1){
            window.setTimeout(function() { $('.cart-bar').fadeOut(250); }, 100);
            window.setTimeout(function() {$('.cart-bar-spacer').collapse('hide');}, 100);
        }

    } else {
        this.$remove_media_buttons.prop('disabled', false);

        if($('.overlay', media_id).hasClass('inactive'))
            $('.overlay', media_id).removeClass('inactive');
        if($('.controls', media_id).hasClass('inactive'))
            $('.controls', media_id).removeClass('inactive');

        window.setTimeout(function() { $('.cart-bar').fadeIn(250);}, 100);
        window.setTimeout(function() {$('.cart-bar-spacer').collapse('show');}, 100);
    }

    if(result.data.cart_data.total > 0) {
        if($('#cart_bar_total').length > 0) {
            $('#cart_bar_total').text(result.data.cart_data.total);
        }

        if($('#cart_bar_price').length > 0) {
            $('#cart_bar_price').text(Math.ceil(result.data.cart_data.price * result.data.cart_data.total));
            if($('#cart_bar_price').text() > 1) {
                $('#total_label').text('photos');
            } else {
                $('#total_label').text('photo');
            }
        }

        if($('#next_button').hasClass('hide')) {
            $('#next_button').removeClass('hide');
        }
    } else {
        if(!$('#next_button').hasClass('hide')) {
            $('#next_button').addClass('hide');
        }
    }

    for(var media_cart_id in result.data.cart_data.media) {
        $('#total_media_' + media_cart_id).text(result.data.cart_data.media[media_cart_id]['quantity']);
        if(result.data.cart_data.media[media_cart_id]['quantity'] < 1) {
            $('#reduce_media_' + media_cart_id).prop('disabled', true);
        } else {
            $('#reduce_media_' + media_cart_id).prop('disabled', false);
        }

    }        

};

MediaCart.prototype.cartError = function(error) {
};


MediaCart.prototype.modifyMediaQuantity = function(event, $button, mode) {
    var data = {}; 
    var target_id = $button.data('id'); 
    var that = this;
    var api_url = mode == 'remove' ? this.remove_url : this.add_url;
    var thumbnail, standardres, lowres,caption, date, gps, users;
    this.$selectedImage = $('#media_' + target_id);
    thumbnail = this.$selectedImage.data('thumbnail');
    standardres = this.$selectedImage.data('standardres');
    lowres = this.$selectedImage.data('lowres');   
    caption = this.$selectedImage.data('caption');
    date = this.$selectedImage.data('date');
    gps = this.$selectedImage.data('location');
    users = this.$selectedImage.data('users');
    data.thumbnail = thumbnail;
    data.standardres = standardres;
    data.lowres = lowres;
    data.data = {caption:caption, date:date, gps:gps, users:users};
    event.preventDefault();
    event.stopPropagation();
    data.id = target_id;
    $.ajax({
        url: api_url
        ,method: 'POST'
        ,data: data
        ,success: function (data) { that.modifyCart(data) }
        ,error: function (error) { that.cartError(error) }
    });
}

$(document).ready(function() {
    initializeModifyMedia();
    modules_loaded.push(initializeModifyMedia);
});

function initializeModifyMedia() {
    var mediaCart;
    var cartBar;

    if($('.media-cart-view').length > 0) {
        mediaCart = new MediaCart();
    }   
}
