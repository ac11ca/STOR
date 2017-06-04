$(document).ready(function () {
    initializeMessages();
} );

function initializeMessages()  {
    var $messages = $('.messages'), wordcount = 0;
    if($messages.length > 0) {
        $messages.hide();
        if($messages.hasClass('hide')) {
            $messages.removeClass('hide');
        }

        $messages.fadeIn(1000);

        $('.message', $messages).each(function() {
            wordcount += $(this).text().trim().split(' ').length;
        } );

        if(wordcount > 0)
            window.setTimeout(function() { $messages.fadeOut(1000); }, 800 * (wordcount+1));
    }
}

function addMessage(text, type, icon) {
    var $messages = $('.messages');    
    var html = $messages.data('prototype');   
    $messages.html('');
    html = html.replace('{type}', type);
    html = html.replace('{text}', text);
    html = html.replace('{icon}', icon);
    $messages.append(html);
    initializeMessages();
}
