<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage;

use function add_action;
use function dirname;
use function register_block_type;

/**
 * Class Block
 * @package FrostyMedia\MSFeaturedImage
 */
class Block implements WpHooksInterface
{

    use AllBlogs;

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        add_action('init', [$this, 'registerBlock']);
    }

    /**
     * Register block(s).
     */
    public function registerBlock(): void
    {
        register_block_type(dirname(__DIR__) . '/blocks/');
    }
}
