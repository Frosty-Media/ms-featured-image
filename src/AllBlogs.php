<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage;

use function array_map;
use function get_site_option;
use function get_sites;
use function is_array;
use function ucfirst;
use function wp_parse_args;

/**
 * Trait AllBlogs
 * @package FrostyMedia\MSFeaturedImage
 */
trait AllBlogs
{

    /**
     * Get all blog ids, domains & path of blogs in the current network that are:
     * @param array $args
     * @return \WP_Site[].
     */
    public function getBlogSites(array $args = []): array
    {
        $defaults = [
            'public' => 1,
            'archived' => 0,
            'spam' => 0,
            'deleted' => 0,
        ];
        return get_sites(wp_parse_args($args, $defaults));
    }

    /**
     * Get all blogs in the network
     * @param array $ignore_blog_ids The blog ID's to ignore.
     * @return \WP_Site[]
     */
    public function getAllBlogs(array $ignore_blog_ids = []): array
    {
        if (!empty($ignore_blog_ids)) {
            $args = ['site__not_in' => array_map('absint', $ignore_blog_ids)];
        }

        return $this->getBlogSites($args ?? []);
    }

    public function getNetworkName() {
        global $current_site;

        $site_name = get_site_option( 'site_name' );
        if ( ! $site_name ) {
            $site_name = ucfirst( $current_site->domain );
        }

        return $site_name;
    }

    /**
     * Get all blog names.
     * @link http://wordpress.stackexchange.com/a/5096/9065
     * @param int $blog_id
     * @return array
     */
    public function getBlogNames(int $blog_id): array
    {
        global $wpdb;

        $blogs = $wpdb->get_results(
            "SELECT option_value FROM {$wpdb->get_blog_prefix( $blog_id )}options WHERE option_name = 'blogname'"
        );

        return is_array($blogs) ? $blogs : [];
    }
}
