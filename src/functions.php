<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage;

/**
 * Get the attachment image for a specific blog.
 * @param int $blog_id
 * @param string $size Can be any custom image size registered
 *          defaults to 'thumbnail'
 *          optional: medium, large, full
 *          OR array( $size, $size )
 * @param bool $image Use `wp_get_attachment_image` vs `wp_get_attachment_image_src`
 * @return string
 */
function get_site_featured_image(int $blog_id, string $size = 'thumbnail', bool $image = true): string
{
    return Common::getSiteFeaturedImage($blog_id, $size, $image);
}

/**
 * Output the attachment image for a specific blog.
 * @param int $blog_id
 * @param string $size Can be any custom image size registered
 *          defaults to 'thumbnail'
 *          optional: medium, large, full
 *          OR array( $size, $size )
 */
function site_featured_image(int $blog_id, string $size = 'thumbnail'): void
{
    Common::siteFeaturedImage($blog_id, $size);
}
