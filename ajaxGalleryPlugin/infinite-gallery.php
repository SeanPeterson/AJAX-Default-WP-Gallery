
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


add_action( 'wp_enqueue_scripts', 'ajax_enqueue_scripts' );
function ajax_enqueue_scripts() {
	//only load scripts for specified page
	if(is_page())
	{
		wp_enqueue_style( 'infinte-style', plugins_url( '/infinite.css', __FILE__ ) );

		wp_enqueue_script( 'infinite', plugins_url( '/infinite.js', __FILE__ ), array('jquery'), '1.0', true ); //load script, delcare jquery as a dependancy

		//pass string ('postinfinite.ajax_url') to the script (can pass as many strings as you want).
		wp_localize_script( 'infinite', 'postinfinite', array( 
			'ajax_url' => admin_url( 'admin-ajax.php' ) //postinfinite.ajax_url will output the url of the admin-ajax.php file
		));
	}

}

//insert the loading gif into the_content()
add_filter( 'the_content', 'post_love_display', 99 ); 
function post_love_display( $content ) {
	$loading = '';
	$pluginUrl = plugins_url();
	
	//Insert the loading div into the content. (Will also mark the point to prepend new images)
	$loading = '<div id="loading-ajax"></div><div class="loading-gif"><img class="hide" src="' . plugins_url() . '/infinite-gallery/spin.gif" alt="loading animation" /></div>';
	

	return $content . $loading;

}

add_action( 'wp_ajax_nopriv_loadImages', 'loadImages' ); //hook is executed for guest users
add_action( 'wp_ajax_post_loadImages', 'loadImages' ); //hook is executed for logged in users

//Called by ajax
function loadImages() {

	//get passed ajax vairables
	$post->ID = $_REQUEST['post_id'];
	$endPoint = $_REQUEST['endPoint'];
	$startPoint = $_REQUEST['startPoint'];

	//find the gallery shortcode img ids
	$thisPost = get_post($post->ID);
	$post_content = $thisPost->post_content;
	preg_match('/\[gallery.*ids=.(.*).\]/', $post_content, $ids);
	$images_id = explode(",", $ids[1]);

	//FIX AND REMOVE THIS!!!!!!!!!!!!!!!!!!!!!!!!!
	echo do_shortcode('[gallery end="' . $endPoint . '" start="' . $startPoint . '" ids="85,84,83,82,80,81,79,78,77,76,66,67,68,69,70,71,72,73,74,75,64,63,62,61,60,59,58,57,56,46,47,49,48,50,51,52,53,54,55,45,43,42,41,40,44,39,38,37,36,23,24,25,26,27,28,29,30,31,21,20,19,18,17,16,15,14,13,5,4,6,8,9,7,10,11,12"]');

	foreach ($images_id as $id) {
  		//echo  wp_get_attachment_image($id);
  		
	 }


	 die();
}

add_filter( 'post_gallery', 'my_post_gallery', 10, 2 );
function my_post_gallery( $output, $attr) {
  global $post, $wp_locale;

    static $instance = 0;
    $instance++;

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

