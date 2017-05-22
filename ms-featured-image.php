<?php
/**
 * Plugin Name: Multisite Featured Image
 * Plugin URI: https://frosty.media/plugins/multisite-featured-image/
 * Description: Adds a featured image to each site in the multisite network.
 * Version: 2.1.0
 * Author: Austin Passy
 * Author URI: http://austin.passy.co
 * Text Domain: ms-featured-image
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/austyfrosty/multisite-featured-image
 * Network: true
 */

namespace FrostyMedia\MSFeaturedImage;

use FrostyMedia\MSFeaturedImage\Includes\FeaturedImage;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * The base plugin class. Plugin definitions and functions used by other classes.
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/FeaturedImage.php' );

/**
 * Out of the frying pan, and into the fire.
 */
FeaturedImage::instance( __FILE__ );
