<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   MSFeaturedImage
 * @author    Austin Passy <austin@frosty.media>
 * @license   GPL-2.0+
 * @link      http://austin.passy.co
 * @copyright 2013-2016 Austin Passy
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_site_option( 'ms_featured_image' );