<?php

namespace FrostyMedia\MSFeaturedImage;

/**
 * Class Shortcode
 *
 * @package FrostyMedia\MSFeaturedImage
 */
class Shortcode {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * @return Shortcode|object
     */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public static function addShortcode() {
        add_shortcode( 'multisite-featured-image', [ self::instance(), 'allSitesShortcode' ] );
    }

    /**
     * Get all sites in the network
     *
     * @ref http://wordpress.org/support/topic/how-to-create-a-list-of-all-multi-sites-or-blogs-within-one-site?replies=18
     *
     * @return string
     */
    public function allSitesShortcode( $atts ) {
        $site_id = defined( 'SITE_ID_CURRENT_SITE' ) ? SITE_ID_CURRENT_SITE : null;
        $atts    = shortcode_atts( [
            'ignore-blog-id' => $site_id,
        ], $atts, 'multisite-featured-image' );

        wp_enqueue_style( 'ms-featured-image' );

        ob_start();
        FeaturedImage::views( 'shortcode.php', $atts );

        return ob_get_clean();
    }

    /**
     * Query all blogs from the multisite install
     *
     * @param int|null $ignore_blog_id The blog ID to ignore.
     *
     * @return array|null|object
     */
    public static function getAllBlogs( $ignore_blog_id = null ) {
        global $wpdb;

        $where = ! empty( $ignore_blog_id ) ?
            "WHERE blog_id != $ignore_blog_id AND public = 1" :
            'WHERE public = 1';

        return $wpdb->get_results(
            "SELECT blog_id, domain, path FROM $wpdb->blogs $where ORDER BY path"
        );
    }

    /**
     * Get all blog names.
     *
     * @param object $blog
     *
     * @uses get_blog_prefix
     * @ref http://wordpress.stackexchange.com/a/5096/9065
     *
     * @return array|null|object
     */
    public static function getBlogNames( $blog ) {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT option_value FROM {$wpdb->get_blog_prefix( $blog->blog_id )}options WHERE option_name = 'blogname'"
        );
    }
}
