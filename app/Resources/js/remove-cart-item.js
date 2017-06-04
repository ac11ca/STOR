function hookup_delete_cart_item_event() {
    var $ok = $('#ok');
    var $cancel = $('#cancel');
    var $modal = $('#main_modal');
    $ok.click(function(event) {deleteCartItem(event, $(this))});
    $cancel.click(function(event) { $modal.modal('hide'); } );
} 

function deleteCartItem(event, $button) {
    var id = $button.data('id');
    var slot = $button.data('slot');
    var url = $button.data('deleteurl');
    var redirect = $button.data('redirecturl');
    $.ajax({
        url: url
        ,success: function(data) { successDeleteCartItem(data, redirect); }
        ,error: function(data) { errorDeleteCartItem(data); }
    });
}

function successDeleteCartItem(data, redirect) {
    if(data.success) {
        $modal = $('#main_modal').modal('hide');
        window.location = redirect;
    } else {
        addMessage(data.data[0].text, data.data[0].type, data.data[0].icon);  
    }

}

function errorDeleteCartItem(data) {
	addMessage('An error occurred while attempting to delete this photo from your order.', 'danger', 'exclamation-mark');  
}
