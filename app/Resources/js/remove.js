$(document).ready(function() {
    var $delete_button = $('.js-remove');
    var $message, $list, delete_url;

    if($delete_button.length > 0) {
        $message = $('.toast-message');
        $list = $('#list');
        delete_url = $list.data('deleteurl');
        $delete_button.click(function(){
            var id = null;
            if(
                confirm('Are you sure?') 
                && confirm('No, really, are you sure? This CANNOT be undone. Click cancel if you do not want to do this.')
            ) {
        
                id = $(this).data('target');                    
                $.ajax({
                    url: delete_url + '/' + id,
                    method: 'POST',
                    success: function (data) {
                        var $target;
                        $target = $('#content_' + id);
                        if(data.code == '200') {
                            $target.fadeOut();
                        } else {
                            $message.attr('class', 'alert alert-danger toast-message');
                            $message.html('<span class="glyphicon glyphicon-exclamation-sign"></span><span>' + data.message + '</span>');                               
                            $message.fadeIn(2000);
                            window.setTimeout(function(){
                                $message.fadeOut(2000);
                            },14500);               
                        }
                    },
                    error: function () {
                        $message.attr('class', 'alert alert-danger toast-message');
                        $message.html('<span class="glyphicon glyphicon-exclamation-sign"></span><span>An unexpected error occurred while trying to communicate with the server. Please refresh the page and try your action again. If the problem persists, try again at a later time.</span>');                                
                        $message.fadeIn(2000);
                        window.setTimeout(function(){
                            $message.fadeOut(2000);
                        },14500);               
                    }
                });
            }
        });
    }
});
