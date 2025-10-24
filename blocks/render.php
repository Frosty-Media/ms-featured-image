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
$size = $attributes['size'] ?? 'medium';

// Get all sites in the network
$sites = get_sites([
        'public' => 1,
        'archived' => 0,
        'deleted' => 0,
        'spam' => 0,
        'number' => 100, // Limit to 100 sites for performance
]);

if (empty($sites)) {
    printf(
            '<div class="multisite-grid-error">%s</div>',
            __('No sites found in the multisite network.', 'ms-featured-image')
    );
}

// Start output
$block_wrapper_attributes = get_block_wrapper_attributes([
        'class' => 'container-fluid multisite-grid-columns-' . $columns,
        'id' => 'sites',
]);

?>
<section <?php
echo $block_wrapper_attributes; ?>>
    <header>
        <h2><?php
            esc_html_e('Sites in the network', 'ms-featured-image'); ?></h2>
    </header>

    <article>
        <div class="row">
            <?php

            $classes = apply_filters('ms_featured_image_wrapper_classes', [
                    'col-xs-12',
                    'col-sm-6',
                    'col-md-4',
            ]);
            foreach ($sites as $blog) :
                $blogname = $allBlogs->getBlogNames((int)$blog->blog_id);

                foreach ($blogname as $name) {
                    $url = get_site_url($blog->blog_id);
                    $image = Common::getSiteFeaturedImage($blog->blog_id, $size, false);
                    if (empty($image) && $show_placeholder) {
                        $image = sprintf(
                                'https://placeholdit.com/600x400/dddddd/999999?text=%s',
                                sanitize_text_field($name->option_value)
                        );
                    } ?>
                    <div class="<?php
                    echo implode(' ', array_map('sanitize_html_class', (array)$classes)); ?>">
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
    </article>
</section>
