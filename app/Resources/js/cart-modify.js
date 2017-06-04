$(document).ready(function() {
    initializeCartModify();
    modules_loaded.push(initializeCartModify);
});

function initializeCartModify() {
    var $orientationtoggle = $('.orientation-toggle');
    var $removecart = $('.js-remove-cart');
    var $settings = $('.settings');
    var $closecarousel = $('.js-checkmark');
    var $rotation = $('.js-rotate');
    var $entry = $('.js-entry');
    var $entryclose;
    var toggled = false;
    if($orientationtoggle.length > 0) {
        $orientationtoggle.click(function() {
            var mode = $(this).data('orientation');
            var target = $(this).data('id');
            var url = $(this).data('url');
            $.ajax({
                url: url
                ,method:'get'           
                ,success: function(data){ changeImage(data); }
                ,error: function (data) {  
                    if(data.length > 0) {
                        addMessage(data[0].text, data[0].type, data[0].icon);                
                    }   
                }
            });            
        });
    }

    if($removecart.length > 0) {
        $removecart.click(function(event){ removeMediaItem(event, $(this)) });
    }

    if($settings.length > 0) {
        $settings.click(function(event){ toggleSettingsModal(event, $(this)) });
    }

    if($closecarousel.length > 0) {
        $closecarousel.click(function(event){ closeSettingsModal(event, $(this)) } );
    }

    if($rotation.length > 0 ) {
        $rotation.click(function() {
            var url = $(this).data('url');
            $.ajax({
                url: url
                ,method:'get'           
                ,success: function(data){ changeImage(data); }
                ,error: function (data) {  
                    if(data.length > 0) {
                        addMessage(data[0].text, data[0].type, data[0].icon);                
                    }   
                }
            });            
        });
    }

    if($entry.length > 0) {
        $entryclose = $('.glyphicon-remove', '.entry-bar-container');
        $entry.each(function() {
            toggled = $(this).data('toggled') || false;

            if(!toggled) {
                $(this).off('click');
                $(this).click(function(event) { toggleEntryBar(event, $(this)); });
            } else {
                $(this).off('click');
                $(this).click(function(event) { toggleData(event, $(this)); });          
            }
        });

        $entryclose.click(function(event) { hideEntryBar(event, $(this)); });
    }
}

function hideEntryBar(event, $button) {
    var $entrybar = $('#entry_bar');
    var $entrybarcontainer = $('.entry-bar-container');
    $entrybarcontainer.fadeOut(250);
    $entrybar.attr('placeholder','');
    $entrybar.val('');
    $entrybar.removeClass();
    $entrybar.addClass('text-entry');
    $entrybar.addClass('with-icon');
    $('body').focus();
}

function toggleData(event, $button) {
    var url;
    var value = ''; 
    var target;
    var mode = $button.data('mode');
    var $entrybar = $('#entry_bar');
    url = $button.data('url');
    target = $button.data('id');
    $.ajax({
        url: url
        ,method: 'post'
        ,data: {value: ''}
        ,success: function(data) { modifyMediaData(data, $button, $entrybar);}
        ,error: function (data) {  
            if(data.length > 0) {
                addMessage(data[0].text, data[0].type, data[0].icon);                
            }   
        }
    });            
}

function toggleEntryBar(event, $button) {
    var $entrybar = $('#entry_bar');
    var $entrybarcontainer = $('.entry-bar-container');
    var mode = $button.data('mode');
    var placeholder = $button.data('placeholder');
    var value = $button.data('value') || '';
    $entrybar.off('keypress');
    $entrybar.on('keypress', function(event) { keyPressEntry(event, $button, $(this)); });
    $entrybar.val('');
    $entrybar.removeClass();
    $entrybar.addClass('text-entry');
    $entrybar.addClass('with-icon');
    $entrybar.addClass(mode);
    $entrybar.attr('placeholder', placeholder);
    $entrybar.val(value);
    setTimeout(function() {
        var value = $entrybar.val();
        var length = value.length || 0;
        $entrybar.focus();
        $entrybar[0].setSelectionRange(0,length);
    });
    $entrybar[0].setSelectionRange(0,0);
    $entrybarcontainer.fadeIn(250);
}

function keyPressEntry(event, $button, $entry) {
    var url;
    var value = $entry.val() || ''; 
    var target;
    var mode = $button.data('mode');

    if(event.which == 13 && mode != 'search') {
        url = $button.data('url');
        target = $button.data('id');
        $.ajax({
            url: url
            ,method: 'post'
            ,data: {value: value}
            ,success: function(data) { modifyMediaData(data, $button, $entry);}
            ,error: function (data) {  
                if(data.length > 0) {
                    addMessage(data[0].text, data[0].type, data[0].icon);                
                }   
            }
        });        

        hideEntryBar(event, $button);
    } else if (event.which == 13 && mode == 'search') {
        $('.entry-bar-container').fadeOut(250);      
        $('#term').val(value);
        $('#search_form').submit();        
    }
   
}

function modifyMediaData(data, $button, $entry) {
    var mode = $button.data('mode');
    var slot = $button.data('id');
    var value = data.data.media;
  
    $('#' +  mode + '_' + slot).html('');
    $('#carousel_' +  mode + '_' + slot).html('');
    $button.data('value',value);
    $('#' +  mode + '_' + slot).text(value);
    $('#carousel_' +  mode + '_' + slot).text(value);
    if(value != '') {
        $button.data('toggled', 'true');
        $button.off('click');
        $button.click(function(event) { toggleData(event, $(this)); });          
    } else {
        $button.data('toggled', '');
        $button.off('click');
        $button.click(function(event) { toggleEntryBar(event, $(this)); });           
    }


    if(mode == 'location' && value != null && value.trim() != '') {
        if($('#location_glyph_' +  slot).hasClass('hide'))
            $('#location_glyph_' +  slot).removeClass('hide');
        if($('#carousel_location_glyph_' +  slot).hasClass('hide'))
            $('#carousel_location_glyph_' +  slot).removeClass('hide');

    } else if(mode == 'location' && (value == null || value.trim() == '') ) {
        if(!$('#location_glyph_' +  slot).hasClass('hide'))
            $('#location_glyph_' +  slot).addClass('hide');
        if(!$('#carousel_location_glyph_' +  slot).hasClass('hide'))
            $('#carousel_location_glyph_' +  slot).addClass('hide');

    }

    if(mode == 'users' && value != null && value.trim() != '') {
        if($('#user_glyph_' +  slot).hasClass('hide'))
            $('#user_glyph_' +  slot).removeClass('hide');
        if($('#carousel_user_glyph_' +  slot).hasClass('hide'))
            $('#carousel_user_glyph_' +  slot).removeClass('hide');

    } else if(mode == 'users' && (value == null || value.trim() == '') ) {
        if(!$('#user_glyph_' +  slot).hasClass('hide'))
            $('#user_glyph_' +  slot).addClass('hide');
        if(!$('#carousel_user_glyph_' +  slot).hasClass('hide'))
            $('#carousel_user_glyph_' +  slot).addClass('hide');

    }


    if(value != null && value.trim() != '') {
        if(!$('#' + mode + '_entry_' +  slot).hasClass('highlight'))
            $('#' + mode + '_entry_' +  slot).addClass('highlight')
    } else {
        if($('#' + mode + '_entry_' +  slot).hasClass('highlight'))
            $('#' + mode + '_entry_' +  slot).removeClass('highlight')
    }
    
}

function closeSettingsModal(event, $checkmark) {
    var $carouselphotos = $('#carousel-photos');
    $carouselphotos.fadeOut(250);
}

function toggleSettingsModal(event, $settings) {
    var $carouselphotos = $('#carousel-photos');
    var index = $settings.data('index');
    $carouselphotos.carousel(index);
    if($carouselphotos.hasClass('hide')) {
        $carouselphotos.css('display','none');
        $carouselphotos.removeClass('hide');
    }

    $carouselphotos.fadeIn(250);
}

function removeMediaItem(event, $button) {
    var target = $button.data('id');   
}

function changeImage(data) {
    var slot = data.data.slot;
    var orientation, rotation;
    orientation = data.data.media.orientation[slot] || 'portrait-text';
    rotation = data.data.media.rotation[slot] || 0;
    switch(orientation) {
        case 'portrait-notext':
          
            window.setTimeout(function() {
    
                $('.text', '#media_card_' + slot + ', #carousel_media_card_' +  slot).fadeOut(250);
            }, 100);

 
            window.setTimeout(function() {

                if($('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('landscape-text'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('landscape-text');

                if($('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('portrait-text'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('portrait-text');

                if(!$('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('portrait-notext'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).addClass('portrait-notext');
            }, 100);

            $('#addons_' + slot).fadeOut(250);

        break;
        case 'landscape-text':
            window.setTimeout(function() {

                $('.text','#media_card_' + slot + ', #carousel_media_card_' + slot).fadeIn(250);
            }, 150);

            window.setTimeout(function() {

                if($('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('portrait-notext'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('portrait-notext');

                if($('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('portrait-text'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('portrait-text');

                if(!$('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('landscape-text'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).addClass('landscape-text');
            }, 100);

            $('#addons_' + slot).fadeIn(250);

        break;
        default:

            window.setTimeout(function() {
                if($('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('portrait-notext'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('portrait-notext');

                if($('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('landscape-text'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('landscape-text');

                if(!$('#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('portrait-text'))
                    $('#media_card_' + slot + ',#carousel_media_card_' + slot).addClass('portrait-text');
            }, 100);

            window.setTimeout(function() {
                $('.text','#media_card_' + slot + ', #carousel_media_card_' + slot).fadeIn(250);
            }, 100);

            $('#addons_' + slot).fadeIn(250);

        break;

    } 

    window.setTimeout(function() {
        if(rotation != 0) {
            if($('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('rotation-0'))
                $('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('rotation-0');
        }

        if(rotation != 1) {
            if($('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('rotation-1'))
                $('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('rotation-1');
        }

        if(rotation != 2) {
            if($('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('rotation-2'))
                $('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('rotation-2');
        }

        if(rotation != 3) {
            if($('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('rotation-3'))
                $('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).removeClass('rotation-3');
        }
       
        if(!$('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).hasClass('rotation-' + rotation))
            $('.photograph','#media_card_' + slot + ',#carousel_media_card_' + slot).addClass('rotation-' + rotation);

    }, 100);


    $('#media_card_' + slot).attr('data-orientation', orientation);
    $('#carousel_media_card_' + slot).attr('data-orientation', orientation);
    $('#media_card_' + slot).attr('data-rotation', rotation);
    $('#carousel_media_card_' + slot).attr('data-rotation', rotation);
    $('.entry-bar-container').fadeOut(250);
}
