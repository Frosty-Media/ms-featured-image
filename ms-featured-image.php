<?php
/**
 * Plugin Name: Multisite Featured Image
 * Plugin URI: https://frosty.media/plugins/multisite-featured-image/
 * Description: Adds a featured image to each site in a WordPress Multisite Network.
 * Version: 3.3.0
 * Author: Austin Passy
 * Author URI: https://austin.passy.co
 * Text Domain: ms-featured-image
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Network: true
 * GitHub Plugin URI: https://github.com/Frosty-Media/ms-featured-image
 * GitHub Branch: master
 * Requires WP: 6.0
 * Requires PHP: 7.4
 */

use FrostyMedia\MSFeaturedImage\FeaturedImage;
use FrostyMedia\MSFeaturedImage\Psr4Autoloader;

// Exit if accessed directly
defined('ABSPATH') || exit;

if (version_compare(phpversion(), '7.4', '>=')) {
    require_once __DIR__ . '/src/Psr4Autoloader.php';

    (new Psr4Autoloader())->addNamespace('FrostyMedia\MSFeaturedImage', __DIR__ . '/src')->register();

    define(FeaturedImage::class . '_FILE', __FILE__);

    FeaturedImage::instance();
} else {
    if (defined('WP_CLI') && WP_CLI && method_exists('WP_CLI', 'warning')) {
        WP_CLI::warning(_ms_featured_image_php_version_text());
    } else {
        return add_action('network_admin_notices', '_ms_featured_image_php_version_error');
    }
}

/**
 * Admin notice for incompatible versions of PHP.
 */
function _ms_featured_image_php_version_error(): void
{
    printf('<div class="error"><p>%s</p></div>', _ms_featured_image_php_version_text());
}

/**
 * String describing the minimum PHP version.
 * @return string
 */
function _ms_featured_image_php_version_text(): string
{
    return esc_html__(
        'Multisite Featured Image plugin error: Your version of PHP is too old to run this plugin. You must be running PHP 7.4 or higher.',
        'ms-featured-image'
    );
}
