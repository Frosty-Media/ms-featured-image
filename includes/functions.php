<?php

/**
 * Function to Convert stdClass Objects to Multidimensional Arrays.
 *
 * @ref	http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
 *
 * @return array
 */
function ms_featured_image_object_to_array( $input ) {
	\FrostyMedia\MSFeaturedImage\Includes\Common::objectToArray( $input );
}

/**
 * Retrieves the attachment ID from the file URL.
 *
 * @param string $image_url
 *
 * @return string
 */
function ms_featured_image_url_to_attachment_id( $image_url ) {
	\FrostyMedia\MSFeaturedImage\Includes\Common::urlToAttachmentID( $image_url );
}

/**
 * Get the attachment image.
 *
 * @param int $blog_id
 * @param string $size Can be any custom image size registered
 *          defaults to 'thumbnail'
 *          optional: medium, large, full
 *          OR array( $size, $size )
 * @param bool $image Output image or return image URL
 *
 * @return string
 */
function ms_featured_image_get_site_featured_image( $blog_id = '', $size = 'thumbnail', $image = true ) {
	\FrostyMedia\MSFeaturedImage\Includes\Common::getSiteFeaturedImage( $blog_id, $size, $image );
}

/**
 * Output the featured image
 */
function ms_featured_image_site_featured_image( $blog_id = '', $size = 'thumbnail' ) {
	\FrostyMedia\MSFeaturedImage\Includes\Common::siteFeaturedImage( $blog_id, $size );
}