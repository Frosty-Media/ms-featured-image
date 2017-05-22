<?php

namespace FrostyMedia\MSFeaturedImage\Includes\Admin;

use FrostyMedia\MSFeaturedImage\Includes\FeaturedImage;

/**
 * Class FrostyMediaLicense
 * @package FrostyMedia\MSFeaturedImage\Includes\Admin
 */
class FrostyMediaLicense {

	protected $plugin;

	/**
	 * Load required actions, filters and classes for Frosty.Media plugins
	 */
	public function __construct() {

		add_filter( 'frosty_media_add_plugin_license', array( $this, 'addPluginLicense' ) );
	}

	/**
	 * Add our license data.
	 */
	public function addPluginLicense( $plugins ) {

		$plugins[] = array(
			'id' 			=> FeaturedImageAdmin::getPluginID(),
			'title' 		=> 'Multisite Featured Image', //Must match EDD post_title!
			'version'		=> FeaturedImage::VERSION,
			'file'			=> FeaturedImage::get_base_file(),
			'basename'		=> plugin_basename( FeaturedImage::get_base_file() ),
			'download_id'	=> '114',
			'author'		=> 'Austin Passy' // author of this plugin
		);

		return $plugins;
	}

}
