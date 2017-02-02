
/* Return number of divs on a page
jQuery(document).ready( function($) {

	$.ajax({
		url: "http://localhost/gallery/",
		success: function( data ) {
			alert( 'Your home page has ' + $(data).find('div').length + ' div elements.'); //how man div elements are on the page?
		}
	})

})
*/
