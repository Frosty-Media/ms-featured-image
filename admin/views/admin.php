<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   MS_Featured_Image
 * @author    Austin Passy <austin@thefrosty.com>
 * @license   GPL-2.0+
 * @link      http://austinpassy.com
 * @copyright 2013 Austin Passy
 */
?>

<div class="wrap">
    
	<?php $this->settings_api->show_navigation(); ?>
	<?php $this->settings_api->show_forms(); ?>

</div><?php

if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
	$options = get_site_option( 'ms_featured_image', array() );
	echo '<pre>' . print_r( $options, true ) . '</pre>';
}