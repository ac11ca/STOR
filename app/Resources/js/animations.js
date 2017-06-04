$(document).ready(function() {
    initializeAnimations();
    modules_loaded.push(initializeAnimations);
});

function initializeAnimations() {
    var $buttoncard = $('.js-button-card');
    var $buttonanimation = $('.animation-button');
    var $toggleanimation = $('.animation-toggle');

    if($buttoncard.length > 0) {
        $buttoncard.off('click');
        $buttoncard.click(function(event) { animateButtoncard(event, $(this));});
    }

    if($buttonanimation.length > 0) {
        $buttonanimation.off('click');
        $buttonanimation.click(function(event) { animateButtonPress(event, $(this));});
    }


    if($toggleanimation.length > 0) {
        $toggleanimation.off('click');
        $toggleanimation.click(function(event) { animateTogglePress(event, $(this));});
    }

}

function animateButtoncard(event, $button) {
   event.preventDefault();
   event.stopPropagation();
   if(!$button.hasClass('animate'))
        $button.addClass('animate');

    window.setTimeout(function(){
        if($button.hasClass('animate'))
            $button.removeClass('animate');
        window.setTimeout(function(){
            window.location = $button.attr('href');
        },250);
    },250); 
}

function animateButtonPress(event, $button) {
   if(!$button.hasClass('animate'))
        $button.addClass('animate');

    window.setTimeout(function(){
        if($button.hasClass('animate'))
            $button.removeClass('animate');
    },250); 
}

function animateTogglePress(event, $button)  {
   var group = $button.data('group');
   var $group = $('[data-group="' + group + '"]');

   if(!$button.hasClass('animate'))
        window.setTimeout(function() {
            $button.addClass('animate');
        }, 100);

    $group.each(function() {
        if($(this).attr('id') != $button.attr('id') && $(this).hasClass('animate')) {
            $(this).removeClass('animate');
        }
    });   
       
}
