<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage;

/**
 * Class Common
 * @package FrostyMedia\MSFeaturedImage
 */
class Common
{

    /**
     * Gets the cached transient key value.
     * @param string $input
     * @return string
     */
    public static function getTransientKey(string $input): string
    {
        $len = is_multisite() ? 40 : 45;
        $key = FeaturedImage::OPTION_NAME . '_';
        $key .= substr(md5($input), 0, $len - strlen($key));

        return $key;
    }

    /**
     * Function to Convert stdClass Objects to Multidimensional Arrays
     * @link https://www.if-not-true-then-false.com/?p=454
     * @param mixed $input
     * @return array
     */
    public static function objectToArray($input): array
    {
        if (is_object($input)) {
            /**
             * Gets the properties of the given object
             * with get_object_vars public static function
             */
            $input = get_object_vars($input);
        } elseif (is_array($input)) {
            /**
             * Return array converted to object
             * Using __METHOD__ (Magic constant)
             * for recursive call
             */
            return array_map(__METHOD__, $input);
        }

        return $input;
    }

    /**
     * Retrieves the attachment ID from the file URL.
     * @link http://pippinsplugins.com/retrieve-attachment-id-from-image-url/
     * @link http://wordpress.org/support/topic/need-to-get-attachment-id-by-image-url?replies=20
     * @param string $image_url
     * @return string
     */
    public static function urlToAttachmentID(string $image_url): string
    {
        if (empty($image_url)) {
            return '';
        }

        $key = self::getTransientKey($image_url);

        if (($attachment = wp_cache_get($key, null)) === false) {
            global $wpdb;

            $attachment = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT ID FROM $wpdb->posts WHERE guid = '%s'",
                    esc_url_raw($image_url)
                )
            );
            wp_cache_add($key, $attachment, null);
        }

        return $attachment[0] ?? '';
    }

    /**
     * Get the attachment image for a specific blog.
     * @param int|string $blog_id
     * @param string $size Can be any custom image size registered
     *          defaults to 'thumbnail'
     *          optional: medium, large, full
     *          OR array( $size, $size )
     * @param bool $image Use `wp_get_attachment_image` vs `wp_get_attachment_image_src`
     * @return string
     */
    public static function getSiteFeaturedImage(
        int|string $blog_id = '',
        string $size = 'thumbnail',
        bool $image = true
    ): string {
        $options = get_site_option(FeaturedImage::OPTION_NAME, []);
        $image_id = $options['blog_id_' . $blog_id]['id'] ?? null;

        if ($image) {
            return wp_get_attachment_image($image_id, $size);
        }

        $image_attributes = wp_get_attachment_image_src($image_id, $size);

        return $image_attributes[0] ?? '';
    }

    /**
     * Output the featured image
     * @param int|string $blog_id
     * @param string $size
     */
    public static function siteFeaturedImage(int|string $blog_id = '', string $size = 'thumbnail'): void
    {
        echo self::getSiteFeaturedImage($blog_id, $size);
    }

    /**
     * Return the plugin file path.
     * @return string
     */
    public static function getBaseFile(): string
    {
        return constant(FeaturedImage::class . '_FILE');
    }

    /**
     * Return the plugin settings slug.
     * @return string.
     */
    public static function getSettingsPageHook(): string
    {
        return 'settings_page_' . FeaturedImage::PLUGIN_SLUG;
    }
}
