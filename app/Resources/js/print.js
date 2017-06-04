$(document).ready(function(){
    initializePrint();
    modules_loaded.push(initializePrint);
});

function initializePrint() {
    var $print = $('#print');
    var time, rooturl;
    if($print.length > 0) {
        time = $print.data('time');
        rooturl = $print.data('rooturl');
        animatePrintProgress(time, rooturl);
    }
}

function animatePrintProgress(time, rooturl) {
    var counter = 0;
    var $progressbar = $('.progress-bar');
    var $time = $('#time');
    var remaining;
    var interval = setInterval(function(){
        ratio = (time > 0 ? (counter/time) : 0) * 100;
        remaining = time - counter;
        remaining = remaining > 0 ? remaining : 0; 
        $progressbar.width((ratio < 100 ? (ratio) : '100') + '%');
        counter+=250;
        $time.text(Math.ceil(remaining/1000));
        if(counter >= time+5000) {
            window.location = rooturl;
        }
    }, 250);
}
