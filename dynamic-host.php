<?php
/*
Plugin Name: Dynamic Host
Description: Allows Wordpress to run anywhere i.e. local, development, or production.
Version: 1.0.8
Author: Val Catalasan
*/

define( 'DYNAMIC_HOST', isset($_SERVER['HTTP_X_ORIGINAL_HOST']) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'] );
define( 'SITE_URL', get_option('siteurl'));

class Dynamic_Host {

    static private $instance = null;

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    function __construct() {
        add_filter('login_redirect', function($url, $query, $user) {
            return $this->dynamic_url($url);
        }, 10, 3);

        add_filter('wp_redirect', function($location, $status) {
            return $this->dynamic_url($location);
        });

        add_filter('site_url', function($url, $path, $scheme, $blog_id) {
            return $this->dynamic_url($url);
        }, 10, 4);

        add_filter('admin_url', function($url, $path, $blog_id) {
            return $this->dynamic_url($url);
        }, 10, 3);

        add_filter('plugins_url', function($url, $path, $plugin) {
            return $this->dynamic_url($url);
        }, 10, 3);

        add_filter('upload_dir', function($param) {
            $param['url'] = $this->dynamic_url($param['url']);
            $param['baseurl'] = $this->dynamic_url($param['baseurl']);
            return $param;
        });

        add_action('init', function() {
            ob_start(function ($buffer) {
                $dynamic_url = $this->dynamic_url(SITE_URL);
                $output = str_replace(SITE_URL, $dynamic_url, $buffer);
                return $output;
            });
        });

        add_action('shutdown', function() {
            ob_end_flush();
        });
    }

    function dynamic_url( $url ) {
        $result = parse_url($url);
        $scheme = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : $result['scheme'];
        if (empty($scheme)) $scheme='http';
        $host = DYNAMIC_HOST; //isset($_SERVER['HTTP_X_ORIGINAL_HOST']) ? $_SERVER['HTTP_X_ORIGINAL_HOST'] : $_SERVER['HTTP_HOST'];
        $path = isset($result['path']) ? $result['path'] : '';
        $query = isset($result['query']) ? "?{$result['query']}" : '';
        $url = $scheme . '://' . $host . $path . $query;
        return $url;
    }
}

Dynamic_Host::get_instance();