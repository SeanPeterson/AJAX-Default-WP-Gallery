/*
	Infinite Scroll Technique Found Here: http://www.billerickson.net/infinite-scroll-in-wordpress/
	Infinite Scroll Author: Bill Erickson
*/

var $grid;
var page = 2;
var loading = false;
var scrollHandling = {
    allow: true,
    reallow: function() {
        scrollHandling.allow = true;
    },
    delay: 400 //(milliseconds) adjust to the highest acceptable value
};

var iteration = 20;
var startPoint = 0, endPoint = iteration;
var gallerySize = 999999999; //unlikly large number

jQuery(window).scroll(function(){
		if( ! loading && scrollHandling.allow ) {
			//scrollHandling.allow = false; //disable event
			//setTimeout(scrollHandling.reallow, scrollHandling.delay);

			//1. offset().top gives elemets distance from the top of the page
			//2. scrollTop() = users scroll offset from the top of the page
			// 1 - 2 --> Gives the user's current distance from the top of the page
			var offset = jQuery('#footer').offset().top - jQuery(window).scrollTop();

			if(endPoint >= gallerySize)
				jQuery("#loadingAjax").append("<h1>No more Images</h1>");

			//if we're within 2000px of the element
			if( 2000 > offset  && endPoint < gallerySize) {

				//show loading wheel
				jQuery('.loading-gif img').removeClass("hide");

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
						var $returned;

						//hide loading wheel
						jQuery('.loading-gif img').addClass("hide");

						//append new images to page
						var gallery = jQuery.parseJSON(response);

						//set size of gallery so we know when to stop executing ajax script
						gallerySize = gallery.gallerySize;

						//append new items
						for(var i=0; i<gallery.galleryArray.length; i++)
						{
							$returned = jQuery('<dl class="gallery-item"><img src="' + gallery.galleryArray[i] + '" /></dl>');

							 // append items to grid
							$grid.append( $returned );
							// add and lay out newly appended items
							$grid.masonry( 'appended', $returned );
							// layout Masonry after each image loads
							$grid.imagesLoaded().progress( function() {
							  $grid.masonry('layout');
							});
						}
					}
				});
			}
		}

	return false;
})


/*GALLERY MASONRY*/

jQuery(document).ready(function() {
	//init masonry
	$grid = jQuery('.gallery').masonry({
		//isOriginTop: false,
		//columnWidth: 320,
		gutter: 5,
		itemSelector: '.gallery-item'
		}).imagesLoaded(function() {
	});

		// layout Masonry after each image loads
		$grid.imagesLoaded().progress( function() {
		  $grid.masonry('layout');
		});

});	
