<?php
/*
Plugin Name: Dynamic Host
Description: Allows Wordpress to run anywhere i.e. local, development, or production.
Version: 1.0.0
Author: Val Catalasan
*/

function dynamic_url($url) {
	$result = parse_url( $url );
	$scheme = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : $result['scheme'];
	$host   = isset($_SERVER['HTTP_X_ORIGINAL_HOST']) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'];
	$path = isset($result['path']) ? $result['path'] : '';
	$url = $scheme . '://' . $host . $path;
	return $url;
}

add_filter('option_siteurl', 'dynamic_url');

foreach( [ 'post', 'page', 'attachment', 'post_type' ] as $type )
{
	add_filter( $type . '_link', function ( $url, $post_id, $sample ) use ( $type )
	{
		return apply_filters( 'wpse_link', $url );
	}, PHP_INT_MAX, 3 );
}

add_filter( 'wpse_link', function(  $url )
{
	return dynamic_url($url);
}, 10, 1);