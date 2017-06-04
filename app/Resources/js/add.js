$(document).ready(function() {
    if($('.js-click-add-account').length > 0) 
        $('.js-click-add-account').click();
});


function hookup_add_events() {
    $('.js-add-account').click(function () {
        var url = $(this).data('url');
        var $username, $password, error = '';
        $username = $('#username');
        $password = $('#password');


        $.ajax( {
            url: url,
            type: 'POST'
            ,data: {username: $username.val(), password: $password.val()}
            ,success: function(data) {
                if(!data.success)
                    return addErrorHandler(data.data);
                console.log('hi');
                addSuccessHandler(data.data);                        
            }
            ,error: function(data) {
                addErrorHandler([{'text':'An unexpected error has occurred. Please try again in a bit. If the problem persists, please contact technical support.', 'type':'alert alert-danger', 'icon':'glyphicon glyphicon-danger'}]);
            },
        });
        
    });
}

function addErrorHandler(data) {
    if(data.length > 0) {
        addMessage(data[0].text, data[0].type, data[0].icon);                
    }
}

function addSuccessHandler(data) {
    window.location = data.url;
}
