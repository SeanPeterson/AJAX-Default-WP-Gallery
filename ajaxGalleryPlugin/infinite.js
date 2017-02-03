/*
	Infinite Scroll Technique Found Here: http://www.billerickson.net/infinite-scroll-in-wordpress/
	Infinite Scroll Author: Bill Erickson
*/

var page = 2;
var loading = false;
var scrollHandling = {
    allow: true,
    reallow: function() {
        scrollHandling.allow = true;
    },
    delay: 400 //(milliseconds) adjust to the highest acceptable value
};


var iteration = 9;
var startPoint = 0, endPoint = iteration;

jQuery(window).scroll(function(){
		if( ! loading && scrollHandling.allow ) {
			scrollHandling.allow = false; //disable event
			setTimeout(scrollHandling.reallow, scrollHandling.delay);

			//1. offset().top gives elemets distance from the top of the page
			//2. scrollTop() = users scroll offset from the top of the page
			// 1 - 2 --> Gives the user's current distance from the top of the page
			var offset = jQuery('footer').offset().top - jQuery(window).scrollTop();

			//if we're within 2000px of the element
			if( 2000 > offset ) {

				//show loading wheel
				jQuery('.loading-gif img').addClass("show");

				endPoint += iteration;
				startPoint += iteration;
				jQuery.ajax({
					url : postinfiniteArray.ajax_url, //string that was passed
					type : 'post', //get or post 
					data : { //parameters that I want to send
						action : 'loadImages', //wordpress requires an action
						post_id : postinfiniteArray.postID, //info to pass to the php function
						endPoint : endPoint,
						startPoint : startPoint
					},
					success : function( response ) {
						//hide loading wheel
						jQuery('.loading-gif img').removeClass("show");

						//append new images to page
						var gallery = jQuery.parseJSON(response);

						jQuery('#loading-ajax').append( gallery.shortcode );
					}
				});
			}
		}

	return false;
})