/*
	Infinite Scroll Technique Found Here: http://www.billerickson.net/infinite-scroll-in-wordpress/
	Infinite Scroll Author: Bill Erickson
*/

/*-----------------Globals----------------------*/
var isLarge = true;
const ITERATION = 20;

var Gallery = (function(){
	var galleryJson = null;
	var gallerySize = null;
	var loading = false;
	var start = 20;
	var end = start + ITERATION;

	return{
		setJson: function(response){
			galleryJson = jQuery.parseJSON(response);
			gallerySize = galleryJson.gallerySize;
		},
		setLoading: function(value){
			loading = value;
		},
		isLoading: function(){
			return loading;
		},
		isInitialized: function(){
			if(galleryJson === null)
				return false;
			else
				return true;
		},
		moreImagesExist: function(){

			if(start < gallerySize)
				return true;
			else
				return false;
		},
		appendItems: function(){
			console.log("FUNCTION");
			//ensure that page json data exists
			if(galleryJson === null)
				return;

			for(var i=start; i<end; i++)
			{
				//ensure that loop stays within array
				if(i >= galleryJson.gallerySize)
					break;

				//append the items
				var $element = jQuery('<dl class="gallery-item"><a href="' + galleryJson.galleryArray[i] + '"> <img src="' + galleryJson.galleryArray[i] + '" /></a><dd class="gallery-caption">' + galleryJson.captionArray[i] + '</dd></dl>');

				 // append items to grid
				$grid.append( $element );
				// add and lay out newly appended items
				$grid.masonry( 'appended', $element );
				// layout Masonry after each image loads
				$grid.imagesLoaded().progress( function() {
				  $grid.masonry('layout');
				});
			}

			//update positions
			start += ITERATION;
			end += ITERATION;

			//Layout complete
			$grid.one( 'layoutComplete',
			  function( event, laidOutItems ) {
			    //hide loading wheel
				jQuery('.loading-gif img').addClass("hide");
				loading = false;
			  }
			);
		}
	}
})();

function initGallery(){
	//ajax call /  initialize gallery
	jQuery.ajax({
		url : postinfiniteArray.ajax_url, //string that was passed
		type : 'post', //get or post 
		data : { //parameters that I want to send
			action : 'loadImages', //wordpress requires an action
			post_id : postinfiniteArray.postID, //info to pass to the php function
		},
		success : function( response ) {
			 Gallery.setJson(response);
			 console.log("SET IT");
		}
	});
}


/*-----------------Initialize----------------------*/

if (jQuery(window).width() > 925) {

	initMasonryGallery('0.4s');	
}
else
{
	 isLarge = false;
	 initMasonryGallery(0);	    
}
initGallery()
/*-----------------EVENTS----------------------*/
jQuery(window).ready(function() {
	jQuery(window).scroll(function(){

		if($grid !== null && Gallery.isInitialized() && !Gallery.isLoading() && Gallery.moreImagesExist())
		{
			//1. offset().top gives elemets distance from the top of the page
			//2. scrollTop() = users scroll offset from the top of the page
			// 1 - 2 --> Gives the user's current distance from the top of the page
			var offset = jQuery('#footer').offset().top - jQuery(window).scrollTop();
			console.log("OFFSET IS " + offset);
			//if we're within 2000px of the element
			if( 1000 > offset) {

				Gallery.setLoading(true);
				//show loading wheel
				jQuery('.loading-gif img').removeClass("hide");

				//append next ITERATION of images
				Gallery.appendItems();
			}
		}
	});
});	

//window resizing
jQuery(document).ready(function() {

	var isLarge = true;

	//init masonry
	if (jQuery(window).width() > 925) {
		initMasonryGallery('0.4s');	
	}
	else
	{
		 isLarge = false;
		 initMasonryGallery(0);	    
	}

	//enable/disable animations on screen resize
	jQuery(window).resize(function(){
	    if ((jQuery(window).width() > 925) && (!isLarge)){ //already large, no need to re-init
	    	isLarge = true;
			initMasonryGallery(0); //don't want to animate for window resize 	 	
		}
		else if((jQuery(window).width() < 925) && (isLarge))
		{
			 isLarge = false;
			 initMasonryGallery(0);	
		}
	});
});	


/*-----------------Functions----------------------*/

//Init or re-init the masonty gallery.
//For mobile transitions are disabled
function initMasonryGallery(durationTime)
{
	var opts = {
	        itemSelector: '.gallery-item',
	        gutter: 5,
	        transitionDuration: durationTime
	    }
	    $grid = jQuery('.gallery').masonry(opts);  
		// layout Masonry after each image loads
		$grid.imagesLoaded().progress( function() {
		  $grid.masonry('layout');
		});	
}
