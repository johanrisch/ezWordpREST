<?php
/*
 * Plugin Name: ezWordpREST
 * Version: 1.0
 * Plugin URI: https://github.com/johanrisch/ezWordpREST
 * Description: Easy wordpress REST plugin. Allows you to easily query post types in a RESTful manner (only GET requests)
 * Author: Johan Risch
 * Author URI: https://github.com/johanrisch
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: ezwordprest
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Johan Risch
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-ezwordprest.php' );
// Load plugin libraries
require_once( 'includes/lib/class-ezwordprest-post-type.php' );

/**
 * Returns the main instance of ezWordpREST to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object ezWordpREST
 */
function ezWordpREST () {
	$instance = ezWordpREST::instance( __FILE__, '1.0.0' );

	return $instance;
}

$ezWordpREST = ezWordpREST();

$ezWordpREST->register_post_type( 'listing', __( 'Listings', 'ezWordpREST' ), __( 'Listing', 'wordpress-plugin-template' ) );

add_action( 'wp', 'ezWordpRESTRequestCheck' );


function ezWordpRESTRequestCheck($wp) {
	// If the request does not have ezpordprest in it, return.
	if(strpos($wp->request, "ezwordprest") === false)
		return;
	header('Content-Type: application/json');
	$queryData = ezWordpREST()->parseQuery($wp);

	if($queryData->showDebug){
		echo json_encode($wp);
	} else if(ezWordpREST()->isPostTypeAllowed($queryData->postType)){
		status_header(200);
		if(isset($queryData->postId)){
			echo json_encode(ezWordpREST()->queryById($queryData->postId));
			die();
		} else {
			$args = array();
			$args['post_type'] = $queryData->postType;
			$args['orderby'] = $queryData->orderData->orderBy;
			$args['order'] = $queryData->orderData->order;
			$args['pust_status'] = 'publish';
			$args['suppress_filters'] = true;
			$posts_array = get_posts( $args );
			$ret = array();
			$customConverter = ezWordpREST()->hasConverter($queryData->postType);
			foreach ($posts_array as $post){
				$post->custom_fields = get_post_custom($post->ID);
				if($customConverter === true){
					array_push($ret,ezWordpREST()->convert($queryData->postType, $post));
				} else {
					array_push($ret,$post);
				}
			}
			if(ezWordpREST()->hasCustomSorter($queryData->postType)){
				$sortFunc = ezWordpREST()->getCustomSorter($queryData->postType);
				usort($ret, $sortFunc);
			}
			echo json_encode($ret);
		}
	} else {
		status_header(403);
	}
	die();
	
}
