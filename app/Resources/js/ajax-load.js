var AjaxLoader = function () {};

$(document).ready(function(){
    var $loadbutton = $('.js-load-ajax');
    var Loader = new AjaxLoader();
    if($loadbutton.length > 0) {
        $loadbutton.click(function(event){ Loader.loadMore(event, $loadbutton); });
    }
});

AjaxLoader.prototype.loadMore = function(event, $loadbutton) {
    var self;
    this.$loadbutton = $loadbutton;
    event.preventDefault();
    self = this; 
    this.$target = $(this.$loadbutton.data('target'));
    this.prototype = this.$target.data('prototype');
    this.page = this.$loadbutton.data('page');
    this.url = this.$loadbutton.data('url');
    this.url_with_page = this.url.replace(/\*\*page\*\*/, this.page);
    this.visit = this.$loadbutton.data('visit');    
    this.$loadbutton.data('page', this.page+1);
    this.max = this.$loadbutton.data('max');
    this.term = null;

    if($loadbutton.hasClass('product-loader'))
        this.loaderFunction = this.loadProducts;
    else
        this.loaderFunction = this.loadReviews;

    $.ajax({
        url: this.url_with_page
        ,cache: false
        ,data: {term:this.term}
        ,success: function(response)  { self.loaderFunction(response); }
        ,error: function (data) {
            console.log('error');
        }
    });    
};

AjaxLoader.prototype.loadReviews = function(response) {    
    var current_review, timestamp, explode, international_date;
    if(response.data.reviews.length) {       
        for(var i = 0; i < response.data.reviews.length; i++) {                   
            index = this.$target.data('index');
            explode = response.data.reviews[i].Created.split('/');
            international_date = explode[2] + '-' + explode[1] + '-' + explode[0];
            timestamp = Math.floor(new Date(international_date).getTime() / 1000);
            console.log(timestamp);
            current_review = this.prototype;            
            current_review = current_review.replace(/\*\*id\*\*/g, response.data.reviews[i].Id);
            current_review = current_review.replace(/\*\*comment\*\*/g, response.data.reviews[i].Comment);
            current_review = current_review.replace(/\*\*created\*\*/g, timestamp);
            current_review = current_review.replace(/\*\*helpscore\*\*/g, response.data.reviews[i].HelpScore);
            current_review = current_review.replace(/\*\*rating_width\*\*/g, (response.data.reviews[i].Rating/5)*100);
            current_review = current_review.replace(/\*\*title\*\*/g, response.data.reviews[i].Title);
            current_review = current_review.replace(/\*\*reviewer\*\*/g, response.data.reviews[i].Reviewer);
            current_review = current_review.replace(/\*\*index\*\*/g, index);
             
            this.$target.append(current_review);
            if($(current_review).hasClass('track-pageview')) {
                trackPageview($(current_review));
            }

            initializeDateFormatting();
            this.$target.data('index', index+1);
        }

        if(index >= this.max-1) 
            this.$loadbutton.fadeOut(250);                
    }
};

AjaxLoader.prototype.loadProducts = function(response) {
    var current_product, rating_obj, rating, rating_count, index;
    if(response.data.products.length) {
        for(var i = 0; i < response.data.products.length; i++) {                   
            index = this.$target.data('index');
            rating_obj = response.data.ratings[response.data.products[i].Id];
            rating = rating_obj && rating_obj.rating ? rating_obj.rating : 0;
            rating_count = rating_obj && rating_obj.count ?  rating_obj.count : 0;
            current_product = this.prototype;
            current_product = current_product.replace(/\*\*id\*\*/g, response.data.products[i].Id);
            current_product = current_product.replace(/\*\*name\*\*/g, response.data.products[i].Name);
            current_product = current_product.replace(/\*\*author\*\*/g, response.data.products[i].Authors);
            current_product = current_product.replace(/\*\*price\*\*/g, response.data.products[i].Price);
            current_product = current_product.replace(/\*\*image\*\*/g, response.data.products[i].Image);
            current_product = current_product.replace(/\*\*rating\*\*/g,  rating);
            current_product = current_product.replace(/\*\*rating_width\*\*/g,  (rating/5)*100);
            current_product = current_product.replace(/\*\*rating_count\*\*/g,  rating_count);
            current_product = current_product.replace(/\*\*index\*\*/g,  index);
            current_product = current_product.replace(/\*\*visit\*\*/g,  this.visit);
            this.$target.append(current_product);
            if($(current_review).hasClass('track-pageview')) {
                trackPageview($(current_review));
            }

            this.$target.data('index', index+1);
        }

        if(index >= this.max-1) 
            this.$loadbutton.fadeOut(250);                
    }

};
