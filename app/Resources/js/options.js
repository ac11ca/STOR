$(document).ready(function () {
    var $type = $('#type');
    var $widget = $('#options_widget');
    var $button = $('#add_option_button');
    var $list = $('#option_list');

    if($widget.length > 0) {
        $type.change(function () {
            if($type.val() == 3 || $type.val() == 4 || $type.val() == 5) {
                if($widget.hasClass('hide')) 
                    $widget.removeClass('hide');                   
            } else {
                if(!$widget.hasClass('hide')) 
                    $widget.addClass('hide');
            }

        } );
        $button.click(function () {
            var $div = $('<div class="form-group"></div>');
            var $label = $('<label for="">Option / Value</label>');
            var $option = $('<input type="text" name="option[]" class="form-control" />');
            var $value = $('<input type="text" name="value[]" class="form-control" />');
            $div.append($label);
            $div.append($option);
            $div.append($value);

            $list.append($div);
        } );
    }   
} );
