
<?php 
/**
 * Plugin Name: Infinite Scroll Functionality
 * Plugin URI: https://github.com/SeanPeterson/AJAX-Default-WP-Gallery
 * Description: Plugin adds lazy-loading(AJAX) and infinite scroll to the default WP Gallery
 * Version: 1.0.0
 * Author: Sean Peterson
 * Author URI: http://seanpetersonwebdesign.com/
 * License: the unlicensed
 */


add_action( 'wp_enqueue_scripts', 'ajax_test_enqueue_scripts' );
function ajax_test_enqueue_scripts() {

		wp_enqueue_style( 'love', plugins_url( '/love.css', __FILE__ ) );
	

	wp_enqueue_script( 'love', plugins_url( '/love.js', __FILE__ ), array('jquery'), '1.0', true ); //load script, delcare jquery as a dependancy

	wp_localize_script( 'love', 'postlove', array( //pass string ('postlove.ajax_url') to the script (can pass as many strings as you want).
		'ajax_url' => admin_url( 'admin-ajax.php' ) //postlove.ajax_url will output the url of the admin-ajax.php file
	));

}

add_filter( 'the_content', 'post_love_display', 99 ); //tie into the_content() filter
function post_love_display( $content ) {
	$love_text = '';

	
		
		$love = get_post_meta( get_the_ID(), 'post_love', true ); //get post_love object
		$love = ( empty( $love ) ) ? 0 : $love; //if empty love = 0 else love = love

		//href for the button should be the same as the target of our ajax call (for fallback purposes)
		$love_text = '<p class="love-received"><button class="love-button" 
		href="' . admin_url( 'admin-ajax.php?action=post_love_add_love&post_id=' . get_the_ID() ) . '"" 
		data-id="' . get_the_ID() . '">give love</button><span id="love-count">' . $love . '</span></p>'; //add a button into the_content stream
	
	

	return $content . $love_text;

}
$GLOBAL_GALLERY = 2;
add_action( 'wp_ajax_nopriv_post_love_add_love', 'post_love_add_love' ); //hook is executed for guest users
add_action( 'wp_ajax_post_love_add_love', 'post_love_add_love' ); //hook is executed for logged in users

//When the button is clicked the jquery posts it to the server. 
//This server code increments the love value
function post_love_add_love() {

	/*
	$love = get_post_meta( $_POST['post_id'], 'post_love', true ); //get love value for this post
	$love++; //increment the value
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
		update_post_meta( $_POST['post_id'], 'post_love', $love ); //save the new love value
		echo $love;
		die(); //end
	}
	else{ //for people who weren't redirected by javascript
		wp_redirect( get_permalink( $_REQUEST['post_id'] ) );
		exit();
	}
	*/

		$post->ID = $_REQUEST['post_id'];
		$endPoint = $_REQUEST['endPoint'];
		$startPoint = $_REQUEST['startPoint'];
		echo "THE END POINT IS " . $endPoint . "END THIS";
		echo "THE START POINT IS " . $startPoint . "START THIS";
		$args = array(
		'post_parent' => $post->ID,
	
		'numberposts' => -1,
		'post_status' => 'published' 
	);
	$children = get_children( $args );

	$ids = get_gallery_attachments();

	$value = current($ids);

	$thisPost = get_post($post->ID);
	$post_content = $thisPost->post_content;
	preg_match('/\[gallery.*ids=.(.*).\]/', $post_content, $ids);
	$images_id = explode(",", $ids[1]);

	echo do_shortcode('[gallery end="' . $endPoint . '" start="' . $startPoint . '" ids="85,84,83,82,80,81,79,78,77,76,66,67,68,69,70,71,72,73,74,75,64,63,62,61,60,59,58,57,56,46,47,49,48,50,51,52,53,54,55,45,43,42,41,40,44,39,38,37,36,23,24,25,26,27,28,29,30,31,21,20,19,18,17,16,15,14,13,5,4,6,8,9,7,10,11,12"]');

	foreach ($images_id as $id) {
  		//echo  wp_get_attachment_image($id);
  		
	 }


	 die(); //end
}


function get_gallery_attachments(){
	global $post;
	
	$post_content = $post->post_content;
	preg_match('/\[gallery.*ids=.(.*).\]/', $post_content, $ids);
	$images_id = explode(",", $ids[1]);
	
	return $images_id;
}

add_filter( 'post_gallery', 'my_post_gallery', 10, 2 );
function my_post_gallery( $output, $attr) {
  global $post, $wp_locale;

    static $instance = 0;
    $instance++;


$GLOBAL_GALLERY++;
    // We're trusting author input, so let's at least make sure it looks like a valid orderby statement
    if ( isset( $attr['orderby'] ) ) {
        $attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
        if ( !$attr['orderby'] )
            unset( $attr['orderby'] );
    }

    extract(shortcode_atts(array(
        'order'      => 'ASC',
        'orderby'    => 'menu_order ID',
        'id'         => $post->ID,
        'itemtag'    => 'dl',
        'icontag'    => 'dt',
        'captiontag' => 'dd',
        'columns'    => 3,
        'size'       => 'thumbnail',
        'include'    => '',
        'exclude'    => '',
        'start'		 => '0',
        'end' => 9
    ), $attr));

    $id = intval($id);
    if ( 'RAND' == $order )
        $orderby = 'none';

    if ( !empty($include) ) {
        $include = preg_replace( '/[^0-9,]+/', '', $include );
        $_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

        $attachments = array();
        foreach ( $_attachments as $key => $val ) {
            $attachments[$val->ID] = $_attachments[$key];
        }
    } elseif ( !empty($exclude) ) {
        $exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
        $attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
    } else {
        $attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
    }

    if ( empty($attachments) )
        return '';

    if ( is_feed() ) {
        $output = "\n";
        foreach ( $attachments as $att_id => $attachment )
            $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
        return $output;
    }

    $itemtag = tag_escape($itemtag);
    $captiontag = tag_escape($captiontag);
    $columns = intval($columns);
    $start = intval($start);
    $end = intval($end);
    $itemwidth = $columns > 0 ? floor(100/$columns) : 100;
    $float = is_rtl() ? 'right' : 'left';

    $selector = "gallery-{$instance}";

    $output = apply_filters('gallery_style', "
        <style type='text/css'>
            #{$selector} {
                margin: auto;
            }
            #{$selector} .gallery-item {
                float: {$float};
                margin-top: 10px;
                text-align: center;
                width: {$itemwidth}%;           }
            #{$selector} img {
                border: 2px solid #cfcfcf;
            }s
            #{$selector} .gallery-caption {
                margin-left: 0;
            }
        </style>
        <!-- see gallery_shortcode() in wp-includes/media.php -->
        <div id='$selector' class='gallery galleryid-{$id}'>");

    $i = 0;

    foreach ( $attachments as $id => $attachment ) {

    	//ugly but I suck at PHP right now UPDATE!!!!!!!!!!!!!!!
    	if($i < $start)
    	{
    		$i++;
    		continue;
    	}
    	if($i >= $end)
    		break;

        $link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);

        $output .= "<{$itemtag} class='gallery-item'>";
        $output .= "
            <{$icontag} class='gallery-icon'>
                $link
            </{$icontag}>";
        if ( $captiontag && trim($attachment->post_excerpt) ) {
            $output .= "
                <{$captiontag} class='gallery-caption'>
                " . wptexturize($attachment->post_excerpt) . "
                </{$captiontag}>";
        }
        $output .= "</{$itemtag}>";
        if ( $columns > 0 && ++$i % $columns == 0 )
            $output .= '<br style="clear: both" />';
    }


    $output .= "
            <br style='clear: both;' />
        </div>\n";


    $endPoint += 3;

    return $output;
}

