$(document).ready(function () {
    initializeTagHighlighter();
    modules_loaded.push(initializeTagHighlighter);
});

function initializeTagHighlighter() {
    var $taghighlighter = $('.js-taghighlighter');
    var searchurl = $taghighlighter.data('searchurl');
    if($taghighlighter.length > 0) {        
        $taghighlighter.each(function() {
            var text = $(this).text();
            var words = text.split(" ");
            for(var i = 0; i < words.length; i++) {
                if(words[i][0] == '#') {
                    words[i] = '<a href="' + searchurl + '?term=' + words[i].substr(1, words[i].length) + '">' +  words[i] + '</a>';
                
                }
                $(this).html(words.join(' '));
            }
        });
    }
}
