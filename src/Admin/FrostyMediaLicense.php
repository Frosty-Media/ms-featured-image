<?php

namespace FrostyMedia\MSFeaturedImage\Admin;

use FrostyMedia\MSFeaturedImage\FeaturedImage;

/**
 * Class FrostyMediaLicense
 *
 * @package FrostyMedia\MSFeaturedImage\Admin
 */
class FrostyMediaLicense {

    protected $plugin;

    /**
     * Load required actions, filters and classes for Frosty.Media plugins
     */
    public function __construct() {
        add_filter( 'frosty_media_add_plugin_license', [ $this, 'addPluginLicense' ] );
    }

    /**
     * Add our license data.
     *
     * @param array $plugins
     *
     * @return array
     */
    public function addPluginLicense( array $plugins ) {
        $plugins[] = [
            'id' => FeaturedImageAdmin::getPluginID(),
            'title' => 'Multisite Featured Image', //Must match EDD post_title!
            'version' => FeaturedImage::VERSION,
            'file' => FeaturedImage::get_base_file(),
            'basename' => plugin_basename( FeaturedImage::get_base_file() ),
            'download_id' => '114',
            'author' => 'Austin Passy' // author of this plugin
        ];

        return $plugins;
    }
}
