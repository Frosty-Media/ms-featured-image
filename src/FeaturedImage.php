<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage;

use FrostyMedia\MSFeaturedImage\Admin\FeaturedImageAdmin;
use FrostyMedia\MSFeaturedImage\Admin\SettingsApi;
use function add_action;
use function defined;
use function get_site_option;
use function is_admin;
use function is_network_admin;

/**
 * Class FeaturedImage
 * @package FrostyMedia\MSFeaturedImage
 */
class FeaturedImage
{

    public const VERSION = '3.4.0';
    public const OPTION_NAME = 'ms_featured_image';
    public const PLUGIN_SLUG = 'ms-featured-image';

    /**
     * Instance of this class.
     * @var FeaturedImage|null $instance
     */
    protected static ?FeaturedImage $instance = null;

    /**
     * Return an instance of this class.
     * @return $this
     */
    public static function instance(): FeaturedImage
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->includes();
            self::$instance->instantiations();
            self::$instance->update();
        }

        return self::$instance;
    }

    /**
     * Include functions file.
     */
    private function includes(): void
    {
        include_once __DIR__ . '/functions.php';
        if ($this->isAdmin() && !class_exists(FeaturedImageAdmin::class)) {
            include_once __DIR__ . '/Admin/FeaturedImageAdmin.php';
        }
        if (!class_exists(SettingsApi::class)) {
            include_once __DIR__ . '/Admin/SettingsAPI.php';
        }
    }

    /**
     * Setup our classes.
     */
    private function instantiations(): void
    {
        (new Block())->addHooks();
        (new RestApi())->addHooks();
        if ($this->isAdmin()) {
            (new FeaturedImageAdmin(new SettingsApi()))->addHooks();
        } else {
            (new Shortcode())->addHooks();
        }
    }

    private function update(): void
    {
        if (!is_network_admin() && !is_super_admin()) {
            return;
        }
        add_action('shutdown', static function (): void {
            $has_run = get_site_option(self::OPTION_NAME . '_update');
            if ($has_run) {
                return;
            }
            $options = get_site_option(self::OPTION_NAME, []);
            foreach ($options as $option => $value) {
                if (is_array($value)) {
                    continue;
                }
                $image_id = Common::urlToAttachmentID($value);
                $options[$option]['url'] = $value;
                $options[$option]['id'] = $image_id;
            }
            update_site_option(self::OPTION_NAME, $options);
            update_site_option(self::OPTION_NAME . '_update', 1);
        });
    }

    /**
     * Helper for is_admin function.
     * @return bool
     */
    private function isAdmin(): bool
    {
        return is_admin() || defined('DOING_AJAX') || defined('DOING_CRON');
    }
}
