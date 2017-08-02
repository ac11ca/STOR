$(document).ready(function() {
    var $filter, $filter_control, index =1, prototype;

    $filter_control = $('#filter_control');
    $filter = $('.js-add-filter');

    if($filter_control.length > 0) {
        prototype = $filter_control.data('prototype');
        $filter.click(function(e) {
            var $new_filter;
            e.preventDefault();
            index = $filter_control.data('index');
            $new_filter = $(prototype.replace(/\*\*index\*\*/g,index));
            $filter_control.append($new_filter);
            $('#filter_remove_' + index).click(function() { removeFilter($(this));});
            index++;
            $filter_control.data('index',index);

        });
    }

});

function removeFilter($this) {
    var $row;
    if(confirm('Are you sure?')) {
        $row = $($this.data('target'));
        $row.fadeOut(250);
        window.setTimeout(
            function() {
                $row.remove();
            }, 250
        );
    }
}
