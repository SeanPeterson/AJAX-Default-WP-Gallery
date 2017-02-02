

	jQuery('.post-listing').append( '<span class="load-more"></span>' );
	var button = jQuery('.post-listing .load-more');
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
			scrollHandling.allow = false;
			setTimeout(scrollHandling.reallow, scrollHandling.delay);
			var offset = jQuery(button).offset().top - jQuery(window).scrollTop();
			if( 2000 > offset ) {
				var post_id = jQuery(this).data('id'); //grab the id of the current post
				endPoint += iteration;
				startPoint += iteration;
				jQuery.ajax({
					url : postlove.ajax_url, //string that was passed
					type : 'post', //get or post 
					data : { //parameters that I want to send
						action : 'post_love_add_love', //wordpress requires an action
						post_id : post_id, //so we know which post to associate the love to
						endPoint : endPoint,
						startPoint : startPoint
					},
					success : function( response ) {
						jQuery('#love-count').append( response ); //on success update the value on the page
						console.log("success");
					}
				});
			}
		}

	return false; //URL of the button is not followed for people who have javascript enabled
})