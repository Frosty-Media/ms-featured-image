# Multisite Featured Image

* Contributors: [thefrosty](https://github.com/thefrosty)
* Tags: plugin, wordpress, multisite, multisite-network, featured-image 
* Requires at least: 4.6
* Requires PHP: 7.0.1
* Tested up to: 4.7
* Stable tag: master
* Donate link: [PayPal](https://www.paypal.me/AustinPassy)
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a featured image to each site in a WordPress Multisite Network.

## Description

This plugin can only be Network Activated.

To stay up to date with this plugin, I suggest installing [GitHub Updater](https://github.com/afragen/github-updater)
 
## Example Usage

#### Via a shortcode
This will get **all** sites in the network, except the ID's passed to the `ignore-blog-ids`
attribute as a comma separated list.

```
[multisite-featured-image ignore-blog-ids="1,3,5"]
```

#### Via a function
This will get the featured image of a specific site in the network. It excepts three
parameters and return the URL (string):
```php
namesapce FrostyMedia\MSFeaturedImage;
 
/**
 * Get the attachment image for a specific blog.
 *
 * @param int $blog_id
 * @param string $size Can be any custom image size registered
 *          defaults to 'thumbnail'
 *          optional: medium, large, full
 *          OR array( $size, $size )
 * @param bool $image Use `wp_get_attachment_image` vs `wp_get_attachment_image_src`
 *
 * @return string
 */
function get_site_featured_image( int $blog_id, $size = 'thumbnail', $image = true ): string {
    return Common::getSiteFeaturedImage( $blog_id, $size, $image );
}
```

**Use the function:**
```php
use function FrostyMedia\MSFeaturedImage\get_site_featured_image;
 
$current_site_id = get_current_site()->id;
$featured_image = get_site_featured_image( $current_site_id, 'thumbnail' );
echo '<img src="' . esc_url( $featured_image ) . '">';
```

**Or use the method directly:**
```php
use FrostyMedia\MSFeaturedImage\Common;
 
$current_site_id = get_current_site()->id;
$featured_image = Common::getSiteFeaturedImage( $current_site_id, 'thumbnail' );
echo '<img src="' . esc_url( $featured_image ) . '">';
```