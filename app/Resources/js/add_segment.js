$(document).ready(function () {

    var $segment_container = $('.ChangeRemaining .field-container, .PriceBreak .field-container');

    if($segment_container.length > 0) {
        hookupSegmentFunctionality();
    }


} );

function hookupSegmentFunctionality() {
        var $segment_container = $('.ChangeRemaining .field-container, .PriceBreak .field-container');
        var $segment_button = $('.js-add-segment button');
        var $segment_remove = $('.js-segment-remove');

        $segment_button.unbind();
        $segment_remove.unbind();

        $segment_button.click(function (event) {
            var $prototype = null, prototype = null, index = null;
            event.preventDefault();
            index = $segment_button.data('counter');
            prototype = $segment_container.data('prototype');
            prototype = prototype.replace(/\*\*index\*\*/g, index);
            $prototype = $(prototype);
            $segment_container.append($prototype);
            index++;
            $segment_button.data('counter',index);
            hookupSegmentFunctionality();
        });

        $segment_remove.click(function (event) {
            var $target = null;
            event.preventDefault();
            if(confirm('Are you sure you wish to remove this?')) {
                $target = $('#' + $(this).data('target'));
                $target.fadeOut();
                $target.remove();
            }
        });

}
