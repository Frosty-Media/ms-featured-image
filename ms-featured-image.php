<?php
/**
 * Plugin Name: Multisite Featured Image
 * Plugin URI: https://frosty.media/plugins/multisite-featured-image/
 * Description: Adds a featured image to each site in a WordPress Multisite Network.
 * Version: 3.0.3
 * Author: Austin Passy
 * Author URI: https://austin.passy.co
 * Text Domain: ms-featured-image
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Network: true
 * GitHub Plugin URI: https://github.com/Frosty-Media/ms-featured-image
 * GitHub Branch: master
 * Requires WP: 4.6
 * Requires PHP: 7.0.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( version_compare( phpversion(), '7.0.1', '>=' ) ) {
    require_once __DIR__ . '/src/Psr4Autoloader.php';

    ( new \FrostyMedia\MSFeaturedImage\Psr4Autoloader() )
        ->addNamespace( 'FrostyMedia\MSFeaturedImage', __DIR__ . '/src' )
        ->addNamespace( 'FrostyMedia\MSFeaturedImage\Admin', __DIR__ . '/src/Admin' )
        ->register();

    define( \FrostyMedia\MSFeaturedImage\FeaturedImage::class . '_FILE', __FILE__ );

    \FrostyMedia\MSFeaturedImage\FeaturedImage::instance();
} else {
    if ( defined( 'WP_CLI' ) ) {
        WP_CLI::warning( _ms_featured_image_php_version_text() );
    } else {
        add_action( 'network_admin_notices', '_beachbody_entities_php_version_error' );
    }
}

/**
 * Admin notice for incompatible versions of PHP.
 */
function _ms_featured_image_php_version_error() {
    printf( '<div class="error"><p>%s</p></div>', _ms_featured_image_php_version_text() );
}

/**
 * String describing the minimum PHP version.
 *
 * @return string
 */
function _ms_featured_image_php_version_text() {
    return esc_html__( 'Multisite Featured Image plugin error: Your version of PHP is too old to run this plugin. You must be running PHP 7 or higher.', 'ms-featured-image' );
}
