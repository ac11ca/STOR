$(document).ready(function () {
    initializeDateFormatting();
});

function initializeDateFormatting() {
    var $dateformatted =  $('.js-date');
    if($dateformatted.length > 0) {
        formatDates();
    }
}

function formatDates() {
    var $dates = $('.js-date'), $date, format, momentdate, index, value = '';

    if($dates.length > 0) {
        for(index = 0;index < $dates.length; index++) {
            $date = $($dates[index]);
            format = $date.data('format') || 'ddd, MM/DD/YY h:mm A';
            timestamp = $date.data('timestamp') || null;   
            momentdate = moment.unix(timestamp);        
            if(momentdate.isValid() && timestamp) {                                               
                value = momentdate.format(format);
            } else {
                value = timestamp || '';
            }

            if($date.prop('tagName') == 'INPUT' || $date.prop('tagName') == 'SELECT'){ 
                $date.val(value || '');                
            } else {                   
                $date.html(value || '');
            }
        }
    }

}
