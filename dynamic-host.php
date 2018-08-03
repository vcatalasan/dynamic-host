<?php
/*
Plugin Name: Dynamic Host
Description: Allows Wordpress to run anywhere i.e. local, development, or production.
Version: 1.0.3
Author: Val Catalasan
*/

define( 'DYNAMIC_HOST', isset($_SERVER['HTTP_X_ORIGINAL_HOST']) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'] );
define( 'SITE_URL', get_option('siteurl'));

function dynamic_url($url) {
	$result = parse_url( $url );
	$scheme = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : $result['scheme'];
	$host   = DYNAMIC_HOST; //isset($_SERVER['HTTP_X_ORIGINAL_HOST']) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'];
	$path = isset($result['path']) ? $result['path'] : '';
    $query = isset($result['query']) ? "?{$result['query']}" : '';
	$url = $scheme . '://' . $host . $path . $query;
	return $url;
}

add_filter( 'login_redirect', function( $url, $query, $user ) {
	return dynamic_url($url);
}, 10, 3 );

add_filter( 'wp_redirect', function( $location, $status ){
	return dynamic_url($location);
});

add_action( 'init', function() {
    ob_start(function($buffer){
        $dynamic_url = dynamic_url(SITE_URL);
	    $output = str_replace(SITE_URL, $dynamic_url, $buffer);
        return $output;
    });
});

add_action( 'shutdown', function() {
    ob_end_flush();
});