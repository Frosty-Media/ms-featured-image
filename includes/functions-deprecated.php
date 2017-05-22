<?php

if ( !function_exists( 'objectToArray' ) ) :

	/**
	 * Function to Convert stdClass Objects to Multidimensional Arrays
	 *
	 * @ref	http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
	 *
	 * @return array
	 */
	function objectToArray( $input ) {
		_deprecated_function( __FUNCTION__, '2.0.0', 'ms_featured_image_object_to_array' );

		\FrostyMedia\MSFeaturedImage\Includes\Common::objectToArray( $input );
	}
endif;

if ( !function_exists( 'url_to_attachmentid' ) ) :

	/**
	 * Retrieves the attachment ID from the file URL
	 *
	 * @ref		http://pippinsplugins.com/retrieve-attachment-id-from-image-url/
	 * @ref		http://wordpress.org/support/topic/need-to-get-attachment-id-by-image-url?replies=20
	 *
	 * @param $image_url
	 *
	 * @return void|string
	 */
	function url_to_attachmentid( $image_url ) {
		_deprecated_function( __FUNCTION__, '2.0.0', 'ms_featured_image_url_to_attachment_id' );

		\FrostyMedia\MSFeaturedImage\Includes\Common::urlToAttachmentID( $image_url );
	}
endif;

if ( !function_exists( 'get_site_featured_image' ) ) :

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
	function get_site_featured_image( $blog_id = '', $size = 'thumbnail', $image = true ) {
		_deprecated_function( __FUNCTION__, '2.0.0', 'ms_featured_image_get_site_featured_image' );

		\FrostyMedia\MSFeaturedImage\Includes\Common::getSiteFeaturedImage( $blog_id, $size, $image );
	}
endif;

if ( !function_exists( 'site_featured_image' ) ) :

	/**
	 * Output the featured image
	 */
	function site_featured_image( $blog_id = '', $size = 'thumbnail' ) {
		_deprecated_function( __FUNCTION__, '2.0.0', 'ms_featured_image_site_featured_image' );

		\FrostyMedia\MSFeaturedImage\Includes\Common::siteFeaturedImage( $blog_id, $size );
	}
endif;