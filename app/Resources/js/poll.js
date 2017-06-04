$(document).ready(function(){

   initializePoller();
   modules_loaded.push(initializePoller);
});

function initializePoller() {
    var $pollapi = $('.js-poll-api');
    var url, printurl, rooturl;
    if($pollapi.length > 0) {
        url = $pollapi.data('target');
        printurl = $pollapi.data('printurl');
        rooturl  = $pollapi.data('rooturl');
        var poller = window.setInterval(
            function() {
                pollTransaction(url, printurl, rooturl);
            }, 5000
        );
    }
}

function pollTransaction(url, printurl, rooturl) {
    $.ajax({
        url: url
        ,method: 'get'
        ,success: function (data) {
            console.log(data);
            if (data.data.Transaction.status == 3){
                window.location = rooturl;
            } else if(data.data.Transaction.status == 1) {
                window.location = printurl; 
            } else {
                $('#inserted_total').html((data.data.Transaction.paid | 0) + ' &euro;');
                if(data.data.Transaction.paid > 0)
                {
                    $('form').fadeOut(250);
                }
            }
        }
        ,error: function () {

        }
    });
}
