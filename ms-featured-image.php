<?php
/**
 * Plugin Name: Multisite Featured Image
 * Plugin URI: https://frosty.media/plugins/multisite-featured-image/
 * Description: Adds a featured image to each site in a WordPress Multisite Network.
 * Version: 3.0.0
 * Author: Austin Passy
 * Author URI: http://austin.passy.co
 * Text Domain: ms-featured-image
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/Frosty-Media/ms-featured-image
 * Network: true
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/src/Psr4Autoloader.php';

( new \FrostyMedia\MSFeaturedImage\Psr4Autoloader() )
    ->addNamespace( 'FrostyMedia\\MSFeaturedImage', __DIR__ . '/src' )
    ->register();

/**
 * Out of the frying pan, and into the fire.
 */
\FrostyMedia\MSFeaturedImage\FeaturedImage::instance( __FILE__ );
