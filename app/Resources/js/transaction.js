$(document).ready(function () {
    var $formremove = $('.form-remove');
    var $addformbutton = $('#add_form_button');

    if($formremove.length > 0) {
        initializeFormRemove($formremove);
    }

    if($addformbutton.length > 0) {
        $addformbutton.click(function(event){
            var $addform = $('#add_form');
            var $selected = $('option:selected', $addform);
            var $prototype = $($selected.data('prototype'));
            var $forms = $('#forms');
            $forms.append($prototype);            
            initializeFormRemove($formremove);
            event.preventDefault();            
        });
    }

} );

function initializeFormRemove() {
    $formremove = $('.form-remove');
    $formremove.unbind();
    $formremove.click(function(event) {            
        var target = $(this).data('target');
        var $target = $('#' +  target);
        event.preventDefault();
        if(confirm('Delete entire form from transaction?')) {
            $target.remove();
        }
    } );

}
