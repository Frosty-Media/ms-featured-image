<?php

if ( ! ( $this instanceof \FrostyMedia\MSFeaturedImage\Admin\FeaturedImageAdmin ) ) {
    return;
}

?>
<div class="wrap">
    <?php $this->getSettingsApi()->showForms(); ?>
</div>
<?php
