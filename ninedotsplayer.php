<?php
/*
Plugin Name: AudioDots Player
Plugin URI: https://audiodots.com
Description: AudioDots allows site owners to stream their content as audio to their audience on the go, over mobile and connected devices.
Version: 1.0.0
Author: AudioDots
Author URI: https://audiodots.com
*/


function adp_menu_panel() {
	add_menu_page('Nine Dots Player', 'Nine Dots Player', 'manage_options', 'ninedotsplayer.php', 'adp_view','dashicons-format-audio', 11);
	function adp_view() {
		echo include 'view-admin.php';
		die;
	}
}
add_action('admin_menu', 'adp_menu_panel');

function adp_set_player(){
	 if( current_user_can('editor') || current_user_can('administrator') ) {
	 	$dataCheck = sanitize_text_field($_POST['check']);
	    $check = isset( $dataCheck ) ? $dataCheck : '';
	    update_option('add_ninedotsplayer', $check);
	    echo json_encode($check);
	}
    die;
}
add_action('wp_ajax_adp_set_player', 'adp_set_player');

function adp_check_player( $content){
	 global $post;
	 if(($post->post_type == 'post')){
	 	$display_ninedotsplayer = get_option( 'add_ninedotsplayer' );
		if($display_ninedotsplayer == 1){
			$content = adp_get_data() . $content;
	  		return $content;	
		} else {
			return $content;
		}
	 }
}
add_filter( 'the_content', 'adp_check_player', 1); 

add_action( 'wp_enqueue_scripts', 'adp_preload_css' );
function adp_preload_css() {
    wp_enqueue_style( 'preload9dots-style',plugins_url( '/preload9dots.css', __FILE__ ),array(), '1.0.0', 'all' ); 
}

function adp_get_data(){
	wp_enqueue_script( 'ninedotsplayer-js', 'https://app.audiodots.com/js/ninedotsplayer.js', true );
    wp_enqueue_style( 'ninedotsplayer-css', 'https://app.audiodots.com/css/ninedotsplayer.css', true);
    wp_enqueue_style( 'ninedotsplayer-css-plugin', plugins_url( '/9dots.css', __FILE__ ), true );
    wp_enqueue_script( 'ninedotsplayer-css-plugin',  plugins_url( '/9dots.js', __FILE__ ), true );

    return '<div class="wp-nine-dots-player"><div id="nine-dots-player" class="visible"></div></div>';
}

function adp_shortcode($atts) { 
	$display_ninedotsplayer = get_option( 'add_ninedotsplayer' );
	if($display_ninedotsplayer == 0){
		return adp_get_data();
	}
}
add_shortcode('ninedotsplayer', 'adp_shortcode');

function adp_player_button() { 
    echo adp_get_data();
} 

add_action( 'rest_api_init', function () {
	register_rest_route( 'api/json', 'GetLastToArticle?dateStart=(?P<dateStart>\d+)&dateEnd=(?P<dateEnd>\d+)', array(
		'methods'  => 'GET',
		'callback' => 'adp_get_articles',
	));
});

add_action( 'rest_api_init', function () {
	register_rest_route( 'api/json', 'GetLastToArticle', array(
		'methods'  => 'GET',
		'callback' => 'adp_get_articles',
	));
});

function adp_get_articles( WP_REST_Request $request ) {
	$getDateStart = sanitize_text_field($request['dateStart']);
	$getDateEnd = sanitize_text_field($request['dateEnd']);

	if(!empty($getDateStart) || !empty($getDateEnd)){

		if(!empty($getDateStart)){
			$dateStart = date('Y-m-d H:i:s', $getDateStart);
		}

		if(!empty($getDateEnd)){
			$dateEnd = date('Y-m-d H:i:s', $getDateEnd);
		}

		if(empty($getDateStart)){
			$dateStart = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s', current_time('timestamp'))." -7 day"));
		}

		if(empty($getDateEnd)){
			$dateEnd = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s', current_time('timestamp'))." +7 day"));
		}

		$posts = get_posts(array(
					'numberposts' => -1,
					'post_type' => 'post',
					'orderby'          => 'publish',
					'order'            => 'DESC',
					'date_query' => array(
				        array(
				        	'column' => 'post_modified',
				            'after'     => $dateStart,
				            'before'    => $dateEnd,
				            'inclusive' => true,
				        ),
				    )
				));
	} else {
		$posts = get_posts( array(	
					'numberposts' => -1,
					'post_type' => 'post',
					'orderby'          => 'publish_date',
					'order'            => 'DESC',
					'date_query' => array(
				        array(
				        	'column' => 'post_modified',
				            'after'     => date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s', current_time('timestamp'))." -7 day")),
				            'before'    => date('Y-m-d H:i:s', current_time('timestamp')),
				            'inclusive' => true,
				        ),
				    )
				));
	}

	$allposts = array();
	$allposts['article'] = [];
	$allposts['publisher'] = home_url();

	if(count($posts) == 0){
		return $allposts;
	}
	
	foreach ($posts as $key => $value) {
		$taxonomies = array('category');
		$args = array('parent' => get_the_category($value->ID)[0]->term_id); 
		$terms = get_terms($taxonomies, $args);
		$arrPst = array(
			'articleID' => $value->ID,
			'articleTitle' =>$value->post_title,
			'articleCategory' =>get_the_category($value->ID)[0]->name,
			'articleSubCategory' =>$terms[0]->name,
			'articleBody' =>str_replace("[ninedotsplayer]","",preg_replace("/\r\n|\r|\n/", '', strip_tags($value->post_content))),
			'articleCreator' => get_author_name(  $value->post_author ),
			'articleFriendlyURL' =>get_permalink($value->ID),
			'publisher' =>home_url(),
			'programId' =>$value->ID,
			'articleCreatedDate' => date("Y-m-d\TH:i:s", strtotime($value->post_date)),
			'articleRealUpdateDate' =>date("Y-m-d\TH:i:s", strtotime($value->post_modified))
		);
		array_push($allposts['article'], $arrPst);
	}
	return $allposts;
}

?>