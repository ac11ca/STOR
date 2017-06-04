$(document).ready(function() {
    var $offset = $('.js-datetime');

	if($offset.length > 0)
	{
		$offset.each(function(){
			var key = $(this).data('key'), $self;
            var offset = -new Date().getTimezoneOffset()/60;
            var now = new Date();
	        $(this).val(offset);
            $('#' + key + '_date').attr('min', now.getFullYear() + '-' + (now.getMonth()+1) + '-' + now.getDate());
			$self = $(this);             
			$('#' + key + '_date').change(function(event) {resetOffset(event,$self,key)});
			$('#' + key + '_hour').change(function(event) {resetOffset(event,$self,key)});
		});
	}
});

function resetOffset(event, $this, key)
{
	var timestring = $('#' + key + '_date').val() + ' '  + $('#' + key + '_hours').val() + ':00';
    var offset = new Date(timestring).getTimezoneOffset()/60;
	$this.val(offset);
}
