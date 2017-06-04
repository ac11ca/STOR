$(document).ready(function(){
    var
		$hours = $('#hours')
        ,$minutes = null
        ,$seconds = null
		,hours = 0
        ,seconds = 0
        ,minutes = 0
        ,hourstxt = minutestxt = secondstxt = '0'
        ,interval
    ;

    if($hours.length > 0 ) {
        $minutes = $('#minutes');
        $seconds = $('#seconds');
		hours = Number($hours.text());
        seconds = Number($seconds.text());
        minutes = Number($minutes.text());
        interval = window.setInterval(function(){
   
            seconds--;
            if(seconds < 0) {
                seconds = 59;
                minutes--;
				if(minutes < 0) {
					minutes = 59;
			 	    hours--;

					if(hours < 0) {
                        seconds = 0;
                        minutes = 0;
                        hours = 0;   
						window.clearInterval(interval);
                        return;
                    }
				}
            }

            if(hours < 10) {
                hourstxt = '0' + hours.toString();
            } else {
                hourstxt = hours.toString();
            }


            if(minutes < 10) {
                minutestxt = '0' + minutes.toString();
            } else {
                minutestxt = minutes.toString();
            }


            if(seconds < 10) {
                secondstxt = '0' + seconds.toString();
            } else {
                secondstxt = seconds.toString();
            }
           
            $seconds.text(secondstxt);
            $minutes.text(minutestxt);
       		$hours.text(hourstxt); 
        },1000);
    }

});
