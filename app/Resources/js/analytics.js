function trackEvent(event,category,label) {
    var message = event + ' Category:' + category + ', Label: ' + label;
    var url = $('body').data('trackurl');
    var async = event=='unload' ? false :  true;
    $.ajax({
        url: url
        ,method: 'POST'
        ,async: async
        ,data: {event:event, category:category, label:label}
        ,success: function () { console.log('Tracked event: ' + message); }
        ,error: function () { console.log('Failed to track event: ' + message); }
    });
}

$(window).on('unload', function () {
    var category = $('body').data('category'), category_array, start, end, label;
    category_array = category.split('_') || [category];
    category = category_array[0] + '_Duration';
    label = $('body').data('label');
    
});

$(document).ready(function () {
    var $pageviews = $('.track-pageview');
    var $eventtracks = $('.track-event');
    var track_event, category, label;
    if($pageviews.length > 0) {
        $pageviews.each(function() {
            var $this = $(this);
            $this.category = $this.data('category');
            $this.label = $this.data('label');
            trackEvent('pageview', $this.category, $this.label);
        });
        
    }

    if($eventtracks.length > 0) {
        $eventtracks.each(function() {
            var track_event = $(this).data('event');
            var events = [];
            var trackFunc = function () {                                 
                var category, label = $(this).data('category');
                var $this = track_event == 'unload' ? $('body') : $(this);
                label = $this.data('label');
                category = $this.data('category');
                trackEvent(track_event, category, label);
            };
          
            if(track_event.indexOf(',') > 0) {
                events = track_event.split(',');
            } else {
                events = [track_event];
            }
 
            for(var i=0; i < events.length; i++) {
                if(events[i] == 'unload') {
                    $(window).on(events[i], trackFunc);
                } else {
                    $(this).on(events[i],trackFunc);
                }
            }
        });
        
    }

} );
