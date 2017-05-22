<?php

namespace FrostyMedia\MSFeaturedImage;

use FrostyMedia\MSFeaturedImage\Admin\FeaturedImageAdmin;
use FrostyMedia\MSFeaturedImage\Admin\FrostyMediaLicense;

/**
 * Class FeaturedImage
 *
 * @package FrostyMedia\MSFeaturedImage\Includes
 */
class FeaturedImage {

    const VERSION = '2.1.0';
    const OPTION_NAME = 'ms_featured_image';
    const PLUGIN_SLUG = 'ms-featured-image';

    /**
     * Instance of this class.
     *
     * @var FeaturedImage $instance
     */
    protected static $instance;

    /**
     * @var string $base_file
     */
    private $base_file;

    /**
     * Return an instance of this class.
     *
     * @return self|object
     */
    public static function instance( $base_file = null ) {

        if ( null === self::$instance ) {
            self::$instance = new self;
            if ( ! is_null( $base_file ) ) {
                self::$instance->base_file = $base_file;
            }
            self::$instance->includes();
            self::$instance->instansiations();
            self::$instance->addActions();
        }

        return self::$instance;
    }

    /**
     * Include classes.
     */
    private function includes() {
        require_once plugin_dir_path( self::get_base_file() ) . 'includes/functions.php';
    }

    /**
     * Setup our admin class.
     */
    private function instansiations() {
        if ( $this->isAdmin() ) {
            FeaturedImageAdmin::instance();
            new FrostyMediaLicense();
        } else {
            Shortcode::instance()->addShortcode();
        }
    }

    private function addActions() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    }

    /**
     * Register shortcode styles.
     */
    public function enqueueScripts() {
        wp_register_style(
            self::PLUGIN_SLUG,
            plugins_url( '/css/sites.css', dirname( __FILE__ ) ),
            [],
            self::VERSION
        );
    }

    /**
     * @param bool $doing_ajax
     *
     * @return bool
     */
    private function isAdmin( $doing_ajax = true ) {
        return $doing_ajax ?
            is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) :
            is_admin();
    }

    /**
     * Return the plugin file path.
     *
     * @return string
     */
    public static function get_base_file() {
        return self::instance()->base_file;
    }

    /**
     * Return the plugin settings slug.
     *
     * @return string.
     */
    public static function get_settings_page_hook() {
        return 'settings_page_' . self::PLUGIN_SLUG;
    }

    /**
     * @param string $file
     * @param null|array $atts
     */
    public static function views( $file = '', $atts = null ) {
        include plugin_dir_path( self::get_base_file() ) . "views/$file";
    }
}
