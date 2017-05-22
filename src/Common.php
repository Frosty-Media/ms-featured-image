<?php

namespace FrostyMedia\MSFeaturedImage;

/**
 * Class Common
 *
 * @package FrostyMedia\MSFeaturedImage
 */
class Common {

    /**
     * Get's the cached transient key.
     *
     * @return string
     */
    public static function getTransientKey( $input ) {
        $len = is_multisite() ? 40 : 45;
        $key = FeaturedImage::OPTION_NAME . '_';
        $key = $key . substr( md5( $input ), 0, $len - strlen( $key ) );

        return $key;
    }

    /**
     * Function to Convert stdClass Objects to Multidimensional Arrays
     *
     * @link http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
     *
     * @param mixed $input
     *
     * @return array
     */
    public static function objectToArray( $input ) {
        if ( is_object( $input ) ) {
            /**
             * Gets the properties of the given object
             * with get_object_vars public static function
             */
            $input = get_object_vars( $input );
        }

        if ( is_array( $input ) ) {
            /**
             * Return array converted to object
             * Using __METHOD__ (Magic constant)
             * for recursive call
             */
            return array_map( __METHOD__, $input );
        }

        return $input;
    }

    /**
     * Retrieves the attachment ID from the file URL.
     *
     * @link http://pippinsplugins.com/retrieve-attachment-id-from-image-url/
     * @link http://wordpress.org/support/topic/need-to-get-attachment-id-by-image-url?replies=20
     *
     * @param string $image_url
     *
     * @return string
     */
    public static function urlToAttachmentID( $image_url ) {
        if ( empty( $image_url ) ) {
            return '';
        }

        $key = self::getTransientKey( $image_url );

        if ( false === ( $attachment = wp_cache_get( $key, null ) ) ) {
            global $wpdb;

            $attachment = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT ID FROM $wpdb->posts WHERE guid = '%s'",
                    esc_url( $image_url )
                )
            );
            wp_cache_add( $key, $attachment, null );
        }

        return isset( $attachment[0] ) ? $attachment[0] : '';
    }

    /**
     * Get the attachment image.
     *
     * @param int|string $blog_id
     * @param string $size Can be any custom image size registered
     *          defaults to 'thumbnail'
     *          optional: medium, large, full
     *          OR array( $size, $size )
     * @param bool $image Output image or return image URL
     *
     * @return string
     */
    public static function getSiteFeaturedImage( $blog_id = '', $size = 'thumbnail', $image = true ) {
        $options  = get_site_option( FeaturedImage::OPTION_NAME, [] );
        $image_id = self::urlToAttachmentID( $options[ 'blog_id_' . absint( $blog_id ) ] );

        if ( $image ) {
            return wp_get_attachment_image( $image_id, $size );
        } else {
            $image_attributes = wp_get_attachment_image_src( $image_id, $size );

            return $image_attributes[0];
        }
    }

    /**
     * Output the featured image
     *
     * @param int|string $blog_id
     * @param string $size
     */
    public static function siteFeaturedImage( $blog_id = '', $size = 'thumbnail' ) {
        echo self::getSiteFeaturedImage( $blog_id, $size );
    }
}
