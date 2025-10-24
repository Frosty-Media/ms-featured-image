<?php

declare(strict_types=1);

/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

// Exit if not in a multisite environment
use FrostyMedia\MSFeaturedImage\AllBlogs;
use FrostyMedia\MSFeaturedImage\Common;

$allBlogs = new class {
    use AllBlogs;
};
$attributes ??= [];
// Get block attributes with defaults
$columns = $attributes['columns'] ?? 3;
$show_description = !isset($attributes['showDescription']) || $attributes['showDescription'];
$show_placeholder = !isset($attributes['showPlaceholder']) || $attributes['showPlaceholder'];

// Get all sites in the network
$sites = get_sites([
        'public' => 1,
        'archived' => 0,
        'deleted' => 0,
        'spam' => 0,
        'number' => 100, // Limit to 100 sites for performance
]);

if (empty($sites)) {
    return sprintf(
            '<div class="multisite-grid-error">%s</div>',
            __('No sites found in the multisite network.', 'ms-featured-image')
    );
}

// Start output
$block_wrapper_attributes = get_block_wrapper_attributes([
        'class' => 'multisite-grid-columns-' . $columns,
]);

ob_start();
?>
    <div <?php
    echo $block_wrapper_attributes; ?>>
        <div class="multisite-grid">
            <?php
            foreach ($sites as $blog) :
                $blogname = $allBlogs->getBlogNames((int)$blog->blog_id);

                foreach ($blogname as $name) {
                    $url = get_site_url($blog->blog_id);
                    $image = Common::getSiteFeaturedImage($blog->blog_id, 'full', false); ?>
                    <div>
                        <figure>
                            <figcaption>
                                <a href="<?php
                                echo esc_url($url); ?>" class="animate">
                                    <?php
                                    echo esc_html($name->option_value); ?></a>
                            </figcaption>
                            <a href="<?php
                            echo esc_url($url); ?>">
                                <img src="<?php
                                echo esc_url($image); ?>" alt="<?php
                                echo esc_attr($name->option_value); ?>">
                            </a>
                        </figure>
                    </div>
                    <?php
                }
            endforeach; ?>
        </div>
    </div>
<?php

return ob_get_clean();
