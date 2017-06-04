$(document).ready(function () {
    var $editor, id;
    $editor = $('.js-editor');
    if($editor.length > 0) {
        $editor.each(function (index) {
            id = $(this).attr('id');
            CKEDITOR.replace(id);
        });    
    }

});
