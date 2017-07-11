$(document).ready(function(){
    var $loadbutton = $('.js-load-ajax');
    if($loadbutton.length > 0) {
        $loadbutton.click(function(event){ loadMore(event, $loadbutton); });
    }
});


function loadMore(event, $loadbutton) {
    var page, index, $target, prototype, url, visit, max;
    event.preventDefault();
    $target = $($loadbutton.data('target'));
    prototype = $target.data('prototype');
    page = $loadbutton.data('page');
    url = $loadbutton.data('url');
    url_with_page = url.replace(/\*\*page\*\*/, page);
    visit = $loadbutton.data('visit');    
    $loadbutton.data('page', page+1);
    max = $loadbutton.data('max');
    term = null;
 
    $.ajax({
        url: url_with_page
        ,method:'post'
        ,data: {term:term}
        ,success: function(response) {
            var current_product, rating_obj, rating, rating_count;
            if(response.data.products.length) {
                for(var i = 0; i < response.data.products.length; i++) {                   
                    index = $target.data('index');
                    rating_obj = response.data.ratings[response.data.products[i].Id];
                    rating = rating_obj && rating_obj.rating ? rating_obj.rating : 0;
                    rating_count = rating_obj && rating_obj.count ?  rating_obj.count : 0;
                    current_product = prototype;
                    current_product = current_product.replace(/\*\*id\*\*/g, response.data.products[i].Id);
                    current_product = current_product.replace(/\*\*name\*\*/g, response.data.products[i].Name);
                    current_product = current_product.replace(/\*\*author\*\*/g, response.data.products[i].Authors);
                    current_product = current_product.replace(/\*\*price\*\*/g, response.data.products[i].Price);
                    current_product = current_product.replace(/\*\*image\*\*/g, response.data.products[i].Image);
                    current_product = current_product.replace(/\*\*rating\*\*/g,  rating);
                    current_product = current_product.replace(/\*\*rating_width\*\*/g,  (rating/5)*100);
                    current_product = current_product.replace(/\*\*rating_count\*\*/g,  rating_count);
                    current_product = current_product.replace(/\*\*index\*\*/g,  index);
                    current_product = current_product.replace(/\*\*visit\*\*/g,  visit);
                    $target.append(current_product);
                    $target.data('index', index+1);
                }

                if(index >= max-1) 
                    $loadbutton.fadeOut(250);                
            }
        }
        ,error: function (data) {
            console.log('error');
        }
    });    
}
