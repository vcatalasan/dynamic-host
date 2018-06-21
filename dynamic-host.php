<?php
/*
Plugin Name: Dynamic Host
Description: Allows Wordpress to run anywhere i.e. local, development, or production.
Version: 1.0.1
Author: Val Catalasan
*/

define( 'DYNAMIC_HOST', isset($_SERVER['HTTP_X_ORIGINAL_HOST']) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'] );

function dynamic_url($url) {
	$result = parse_url( $url );
	$scheme = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : $result['scheme'];
	$host   = DYNAMIC_HOST; //isset($_SERVER['HTTP_X_ORIGINAL_HOST']) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'];
	$path = isset($result['path']) ? $result['path'] : '';
	$url = $scheme . '://' . $host . $path;
	return $url;
}

add_filter('option_siteurl', 'dynamic_url');

foreach( [ 'post', 'page', 'attachment', 'post_type' ] as $type )
{
	add_filter( $type . '_link', function ( $url, $a1 = null, $a2 = null, $a3 = null ) use ( $type )
	{
		return apply_filters( 'wpse_link', $url );
	}, PHP_INT_MAX, 4 );
}

add_filter( 'wpse_link', 'dynamic_url');

add_filter( 'bloginfo', function($output, $show){
	return preg_match('/url|home/', $show) ? dynamic_url($output) : $output;
}, 10, 2);

add_filter( 'get_blogs_of_user', function($sites, $user_id, $all){
	return sites.array_map(function($site){
		$site->blogname = dynamic_url($site->blogname);
		$site->siteurl = dynamic_url($site->siteurl);
		return $site;
	});
}, 10, 3);

add_action( 'init', function() {
    ob_start(function($buffer){
        $site_url = get_option('site_url');
        $dynamic_url = dynamic_url(DYNAMIC_HOST);
	    $output = str_replace($site_url, $dynamic_url, $buffer);
        return $output;
    });
});

add_action( 'shutdown', function() {
    ob_end_flush();
});