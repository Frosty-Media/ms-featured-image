<?php

if ( ! ( $this instanceof \FrostyMedia\MSFeaturedImage\Admin\FeaturedImageAdmin ) ) {
    return;
}

?>
<div class="wrap">
	<h1><?php  _e('Multisite Featured Image Settings','ms-featured-image'); ?></h1>
    <?php $this->getSettingsApi()->showForms(); ?>
</div><?php

if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
    echo '<pre>' . print_r( get_site_option( 'ms_featured_image', [] ), true ) . '</pre>';
}
