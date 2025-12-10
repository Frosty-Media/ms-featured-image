<?php

namespace FrostyMedia\MSFeaturedImage;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class Init
 * @package FrostyMedia\MSFeaturedImage
 */
class Init implements IteratorAggregate
{
    /**
     * A container for objects that have been initiated.
     * @var array $initiated
     */
    protected array $initiated = [];

    /**
     * A container for objects that implement WpHooksInterface.
     * @var WpHooksInterface[] $wp_hooks
     */
    private array $wp_hooks = [];

    /**
     * Adds an object to $container property
     * @param WpHooksInterface $wp_hooks
     * @return Init
     */
    public function add(WpHooksInterface $wp_hooks): Init
    {
        $this->wp_hooks[] = $wp_hooks;

        return $this;
    }

    /**
     * All the methods that need to be performed upon plugin initialization should
     * be done here.
     */
    public function initialize(): void
    {
        foreach ($this as $wp_hook) {
            if ($wp_hook instanceof WpHooksInterface && !array_key_exists($wp_hook::class, $this->initiated)) {
                $this->initiated[$wp_hook::class] = true;
                $wp_hook->addHooks();
            }
        }
    }

    /**
     * Provides an iterator over the $container property
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->wp_hooks);
    }
}
