$(document).ready(function ($) {
  'use strict';
  var $body    = $('html, body'), // Define jQuery collection 
  $page = $('#app'), options = {
        // onStart runs as soon as link has been activated
        onStart : {
          
          // Set the duration of our animation
          duration: 250,
          
          // Alterations to the page
          render: function ($container) {

            // Quickly toggles a class and restarts css animations
            $container.addClass('is-exiting');
            smoothState.restartCSSAnimations();
          }
        },
        onReady: {
            duration: 0
            ,render: function ($container, $newContent) {
                $container.removeClass('is-exiting');
                $container.html($newContent);
            }
        },
        onAfter: function ($container, $newContent) {
            for(var i = 0; i < modules_loaded.length; i++)  {
                modules_loaded[i]();
            }
        }
        ,blacklist: 'form' 
      },
      smoothState = $page.smoothState(options).data('smoothState'); // makes public methods available
});
