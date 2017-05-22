<section class="container-fluid" id="sites">

	<header>
		<h2><?php _e( 'Sites in the network' ); ?></h2>
	</header>

	<article>
		<div class="row">
			<?php

			$self = \FrostyMedia\MSFeaturedImage\Includes\Shortcode::instance();

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
			$ignore_blog_id = is_int( $atts['ignore-blog-id'] ) ? absint( $atts['ignore-blog-id'] ) : null;
			$blogs = $self::getAllBlogs( $ignore_blog_id );

			foreach( $blogs as $key => $blog ) {

				// Query for name from options table
				$blogname = $self::getBlogNames( $blog );

				foreach( $blogname as $name ) {

					$url = esc_url( is_ssl() ? 'https://' . $blog->domain . $blog->path : 'http://' . $blog->domain . $blog->path );
					$title = esc_attr( $name->option_value );
					$image = \FrostyMedia\MSFeaturedImage\Includes\Common::getSiteFeaturedImage( $blog->blog_id, 'full', false );

					echo '<div class="col-xs-12 col-sm-6 col-md-4">
						<figure>
							<figcaption><a href="' . $url . '" class="animate"> ' . $title . '</a></figcaption>
							<a href="' . $url . '"><img src="' . $image . '" alt="' . esc_attr( $title ) . '"></a>
						</figure>
					</div>';
				}
			} ?>

		</div>
	</article>
</section><!-- #sites -->
