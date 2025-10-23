<?php

namespace FrostyMedia\MSFeaturedImage;

/**
 * Interface WpHooksInterface
 *
 * Provides a contract for classes that add WordPress hooks
 *
 * @package FrostyMedia\MSFeaturedImage
 */
interface WpHooksInterface {

    public function addHooks(): void;
}
