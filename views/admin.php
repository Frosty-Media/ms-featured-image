<?php

if ( ! ( $this instanceof \FrostyMedia\MSFeaturedImage\Admin\FeaturedImageAdmin ) ) {
    return;
}

?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php $this->getSettingsApi()->showForms(); ?>
</div>
<?php
