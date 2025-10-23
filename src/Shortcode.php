<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage;

/**
 * Class Shortcode
 * @package FrostyMedia\MSFeaturedImage
 */
class Shortcode implements WpHooksInterface
{

    use AllBlogs;

    public const IGNORE_BLOG_ID = 'ignore-blog-id';
    public const IGNORE_BLOG_IDS = 'ignore-blog-ids';
    public const SHORTCODE_SLUG = 'multisite-featured-image';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        add_action('init', [$this, 'addShortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    /**
     * Add the shortcode.
     */
    public function addShortcode(): void
    {
        add_shortcode(self::SHORTCODE_SLUG, [$this, 'allSitesShortcode']);
    }

    /**
     * Register shortcode styles.
     */
    public function enqueueScripts(): void
    {
        wp_register_style(
            FeaturedImage::PLUGIN_SLUG,
            plugins_url('assets/css/sites.css', __DIR__),
            [],
            FeaturedImage::VERSION
        );
    }

    /**
     * Get all sites in the network
     * @link http://wordpress.org/support/topic/how-to-create-a-list-of-all-multi-sites-or-blogs-within-one-site?replies=18
     * @return string
     */
    public function allSitesShortcode($atts): string
    {
        $site_id = defined('SITE_ID_CURRENT_SITE') && is_numeric(SITE_ID_CURRENT_SITE) ?
            SITE_ID_CURRENT_SITE : null;

        $atts = shortcode_atts([
            self::IGNORE_BLOG_ID => $site_id,
            self::IGNORE_BLOG_IDS => [],
        ], $atts, self::SHORTCODE_SLUG);

        wp_enqueue_style(FeaturedImage::PLUGIN_SLUG);

        ob_start();
        include dirname(__DIR__) . '/views/shortcode.php';

        return ob_get_clean();
    }
}
