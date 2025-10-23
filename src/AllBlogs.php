<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage;

use function array_map;
use function implode;
use function is_array;

/**
 * Trait AllBlogs
 * @package FrostyMedia\MSFeaturedImage
 */
trait AllBlogs
{

    /**
     * Get all blogs in the network
     * @param array $ignore_blog_ids The blog ID's to ignore.
     * @return array
     */
    public function getAllBlogs(array $ignore_blog_ids = []): array
    {
        global $wpdb;

        $where = 'WHERE public = 1 AND archived = 0';

        if (!empty($ignore_blog_ids)) {
            $not_in = implode(',', array_map('absint', $ignore_blog_ids));
            $where = "WHERE blog_id NOT IN ($not_in) AND public = 1 AND archived = 0";
        }

        $blogs = $wpdb->get_results("SELECT blog_id, domain, path FROM $wpdb->blogs $where ORDER BY path");

        return is_array($blogs) ? $blogs : [];
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
