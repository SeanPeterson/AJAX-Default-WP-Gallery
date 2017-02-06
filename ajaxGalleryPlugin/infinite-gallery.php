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
	global $post;

	//only load scripts for specified page
	if(is_page('photo-gallery'))
    {
        wp_enqueue_script('masonry');

        wp_register_script( 'imagesloaded', plugins_url( '/js/imagesloaded.pkgd.min.js', __FILE__ ));
        wp_enqueue_script( 'imagesloaded' );

		wp_enqueue_style( 'infinte-style', plugins_url( '/css/infinite.css', __FILE__ ) );

		wp_enqueue_script( 'infinite', plugins_url( '/js/infinite.js', __FILE__ ), array('jquery'), '1.0', true ); //load script, delcare jquery as a dependancy

		//pass string ('postinfinite.ajax_url') to the script (can pass as many strings as you want).
		wp_localize_script( 'infinite', 'postinfiniteArray', array( 
			'ajax_url' => admin_url( 'admin-ajax.php' ), //postinfinite.ajax_url will output the url of the admin-ajax.php file
			'postID' => $post->ID //pass post id
		));
	}

}

//insert the loading gif into the_content()
add_filter( 'the_content', 'post_content', 99 ); 
function post_content( $content ) {
        
    if(is_page('photo-gallery')){
    	$loading = '';
    	$pluginUrl = plugins_url();
    	
    	//Insert the loading div into the content. (Will also mark the point to prepend new images)
    	$loading = '<div id="loading-ajax"></div><div class="loading-gif"><img class="hide" src="' . plugins_url('/ajaxGalleryPlugin/images/spin.gif" alt="loading animation" /></div>');
    	

    	return $content . $loading;
    }
    else
        return $content;

}

add_action( 'wp_ajax_nopriv_loadImages', 'loadImages' ); //hook is executed for guest users
add_action( 'wp_ajax_post_loadImages', 'loadImages' ); //hook is executed for logged in users

//Called by ajax
function loadImages() {

	$post->ID = $_POST['post_id'];
	$endPoint = $_POST['endPoint'];
	$startPoint = $_POST['startPoint'];

	$post = get_post($post->ID);

	if(has_shortcode( $post->post_content, 'gallery' ) )
	{
		$ids = get_post_gallery( $post->ID, false );
		$response = array();
		
		//Seperate string by comma
		$idArray = explode(",", $ids['ids']);
		$gallerySize = count($idArray); 
        $urlArray = [];
        $captionArray = [];
        $i = 0;
		foreach($idArray as $id)
		{

            //ugly but I suck at PHP right now UPDATE!!!!!!!!!!!!!!!
            if($i < $startPoint)
            {
                $i++;
                continue;
            }
            if($i >= $endPoint)
                break;

            $urlArray[] = wp_get_attachment_url($id);
            $captionArray[] = get_post($id)->post_excerpt;
            $i++;
		}	

		//associative array that's returned
		$response['galleryArray'] = $urlArray;
        $response['captionArray'] = $captionArray;
		$response['gallerySize'] = $gallerySize;

		echo json_encode($response);
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
        'order'      => 'asc',
        'orderby'    => 'menu_order ID',
        'id'         => $post->ID,
        'itemtag'    => 'dl',
        'icontag'    => 'dt',
        'captiontag' => 'dd',
        'columns'    => 3,
        'size'       => 'full',
        'include'    => '',
        'exclude'    => '',
        'start'		 => '0',
        'end' => 20
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

