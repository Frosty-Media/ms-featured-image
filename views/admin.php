<div class="wrap">
    
	<?php \FrostyMedia\MSFeaturedImage\Includes\Admin\FeaturedImageAdmin::getSettingsAPI()->show_navigation(); ?>
	<?php \FrostyMedia\MSFeaturedImage\Includes\Admin\FeaturedImageAdmin::getSettingsAPI()->show_forms(); ?>

</div><?php

if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
	echo '<pre>' . print_r( get_site_option( 'ms_featured_image', array() ), true ) . '</pre>';
}
