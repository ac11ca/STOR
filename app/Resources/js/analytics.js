function trackEvent(event,category,label) {
    var message = event + ' Category:' + category + ', Label: ' + label;
    var url = $('body').data('trackurl');

    $.ajax({
        url: url
        ,data: {event:event, category:category, label:label}
        ,success: function () { console.log('Tracked event: ' + message); }
        ,error: function () { console.log('Failed to track event: ' + message); }
    });
}
