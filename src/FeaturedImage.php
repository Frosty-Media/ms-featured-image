<?php

namespace FrostyMedia\MSFeaturedImage;

use FrostyMedia\MSFeaturedImage\Admin\FeaturedImageAdmin;
use FrostyMedia\MSFeaturedImage\Admin\SettingsApi;

/**
 * Class FeaturedImage
 *
 * @package FrostyMedia\MSFeaturedImage
 */
class FeaturedImage {

    const VERSION = '3.2.0';
    const OPTION_NAME = 'ms_featured_image';
    const PLUGIN_ID = 'multisite_featured_image';
    const PLUGIN_NAME = 'Multisite Featured Image';
    const PLUGIN_SLUG = 'ms-featured-image';

    /**
     * Instance of this class.
     *
     * @var FeaturedImage $instance
     */
    protected static $instance;

    /**
     * Return an instance of this class.
     *
     * @return $this
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->includes();
            self::$instance->instantiations();
        }

        return self::$instance;
    }

    /**
     * Include functions file.
     */
    private function includes() {
        include_once __DIR__ . '/functions.php';
        if ( ! class_exists( FeaturedImageAdmin::class ) ) {
            include_once __DIR__ . '/Admin/FeaturedImageAdmin.php';
        }
        if ( ! class_exists( SettingsApi::class ) ) {
            include_once __DIR__ . '/Admin/SettingsAPI.php';
        }
    }

    /**
     * Setup our admin class.
     */
    private function instantiations() {
        if ( $this->isAdmin() ) {
            ( new FeaturedImageAdmin( new SettingsApi() ) )->addHooks();
        } else {
            ( new Shortcode() )->addHooks();
        }
    }

    /**
     * Helper for is_admin function.
     * @return bool
     */
    private function isAdmin(): bool {
        return \is_admin() || \defined('DOING_AJAX') || \defined('DOING_CRON');
    }
}
