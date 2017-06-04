$(document).ready(function(){

    initializeModal();
    modules_loaded.push(initializeModal);    //modules_loaded.push(initializeModal);
});

function initializeModal() {
    var $modal = $('.js-modal');
    if($modal.length > 0) {
        $modal.click(function(event) {triggerModal(event, $(this)); });
    }

}

function triggerModal(event, $target) {
    var 
        url = $target.data('target')
        ,content = null
        ,$modalBox = $('#main_modal')
        ,eventHookup = window[$target.data('event')]
    ;
    $.ajax( {
        url: url,
        success: function(data) {
            var $modalContent = $('.modal-body', $modalBox);
            content = data;
            $modalContent.html(content);
            $modalBox.modal('show');

            if(eventHookup)
                eventHookup();       
        },
        error: function() {
            alert('An unexpected error has occurred.');
        },
    });
}
