<?php

if ( ! ( $this instanceof \FrostyMedia\MSFeaturedImage\Shortcode ) ) {
    return;
}

use FrostyMedia\MSFeaturedImage\Common;
use FrostyMedia\MSFeaturedImage\Shortcode;

?>
<section class="container-fluid" id="sites">

    <header>
        <h2><?php esc_html_e( 'Sites in the network', 'ms-featured-image' ); ?></h2>
    </header>

    <article>
        <div class="row">
            <?php

            $ignore_blog_ids = ! empty( $atts[ Shortcode::IGNORE_BLOG_IDS ] ) ?
                explode( ',', $atts[ Shortcode::IGNORE_BLOG_IDS ] ) :
                [];

            // Fallback for version 2's `ignore-blog-id` attribute.
            if ( empty( $ignore_blog_ids ) ) {
                $ignore_blog_ids = isset( $atts[ Shortcode::IGNORE_BLOG_ID ] ) &&
                                   is_numeric( $atts[ Shortcode::IGNORE_BLOG_ID ] ) ?
                    [ $atts[ Shortcode::IGNORE_BLOG_ID ] ] : [];
            }

            /**
             * Foreach blog search for blog name in respective options table
             * $blogs = stdClass Object
             * (
             * [blog_id] => int
             * [domain] => string
             * [path] => /
             * )
             *
             * @access stdClass $blog->blog_id
             */
            $blogs   = $this->getAllBlogs( $ignore_blog_ids );
            $classes = apply_filters( 'ms_featured_image_wrapper_classes', [
                'col-xs-12',
                'col-sm-6',
                'col-md-4',
            ] );

            if ( is_array( $blogs ) ) {
                foreach ( $blogs as $key => $blog ) {

                    // Query for name from options table
                    $blogname = $this->getBlogNames( $blog->blog_id );

                    foreach ( $blogname as $name ) {
                        $url   = is_ssl() ?
                            'https://' . $blog->domain . $blog->path :
                            'http://' . $blog->domain . $blog->path;
                        $image = Common::getSiteFeaturedImage( $blog->blog_id, 'full', false ); ?>
                        <div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', (array) $classes ) ); ?>">
                            <figure>
                                <figcaption>
                                    <a href="<?php echo esc_url( $url ); ?>" class="animate">
                                        <?php echo esc_html( $name->option_value ); ?></a>
                                </figcaption>
                                <a href="<?php echo esc_url( $url ); ?>"><img
                                            src="<?php echo esc_url( $image ); ?>"
                                            alt="<?php echo esc_attr( $name->option_value ); ?>"></a>
                            </figure>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>
    </article>
</section><!-- #sites -->
