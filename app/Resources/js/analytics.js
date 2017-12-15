function trackEvent(event,category,label,duration) {
    var message = event + ' Category:' + category + ', Label: ' + label;
    var url = $('body').data('trackurl');
    var async = event=='unload'||'duration' ? false :  true;
	duration = duration ||  null;
    $.ajax({
        url: url
        ,method: 'POST'
        ,async: async
        ,data: {event:event, category:category, label:label, duration:  duration}
        ,success: function () { console.log('Tracked event: ' + message); }
        ,error: function () { console.log('Failed to track event: ' + message); }
    });
}

$(window).on('unload', function () {
    debugger;
    var category = $('body').data('category'), category_array, start, end, label, duration;
    category_array = category.split('_') || [category];
    category = category_array[0];
    if(category_array[1].length == '36')
        category += '_' +  category_array[1];
    category += '_Duration';
    label = $('body').data('label');
	start = $('body').data('starttime');
	end = Math.floor((+ new Date()) / 1000);
	duration = end - start;
	trackEvent('duration', category, label, duration);
});

$(document).ready(function () {
    var $pageviews = $('.track-pageview');
    var $eventtracks = $('.track-event');
    var track_event, category, label;
    $('body').data('starttime', Math.floor((+ new Date() / 1000)));
    if($pageviews.length > 0) {
        $pageviews.each(function() {
            var $this = $(this);
            trackPageview($this);
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

function trackPageview($this) {
    $this.event = $this.data('event') || 'pageview';
    $this.category = $this.data('category');
    $this.label = $this.data('label');
    trackEvent($this.event, $this.category, $this.label);
}
