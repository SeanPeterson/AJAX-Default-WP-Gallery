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
			var offset = jQuery('#loading-ajax').offset().top - jQuery(window).scrollTop();

			//if we're within 2000px of the element
			if( 2000 > offset ) {
				var post_id = jQuery(this).data('id'); //grab the id of the current post
				endPoint += iteration;
				startPoint += iteration;
				jQuery.ajax({
					url : postinfinite.ajax_url, //string that was passed
					type : 'post', //get or post 
					data : { //parameters that I want to send
						action : 'loadImages', //wordpress requires an action
						post_id : post_id, //info to pass to the php function
						endPoint : endPoint,
						startPoint : startPoint
					},
					success : function( response ) {
						jQuery('#loading-ajax').append( response );
						console.log("success");
					}
				});
			}
		}

	return false;
})