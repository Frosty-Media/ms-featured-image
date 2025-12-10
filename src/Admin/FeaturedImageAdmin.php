<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage\Admin;

use FrostyMedia\MSFeaturedImage\AllBlogs;
use FrostyMedia\MSFeaturedImage\Common;
use FrostyMedia\MSFeaturedImage\FeaturedImage;
use FrostyMedia\MSFeaturedImage\WpHooksInterface;
use function __;
use function _x;
use function absint;
use function esc_attr;
use function esc_attr__;
use function esc_url;
use function get_blog_details;
use function get_network;
use function sprintf;

/**
 * Class FeaturedImageAdmin
 * @package FrostyMedia\MSFeaturedImage\Admin
 */
class FeaturedImageAdmin implements WpHooksInterface
{

    use AllBlogs;

    public const COLUMN_NAME = FeaturedImage::PLUGIN_SLUG;

    /** @var string|null $plugin_screen_hook_suffix */
    private ?string $plugin_screen_hook_suffix = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a settings page and menu.
     * @param SettingsAPI $settings_api
     */
    public function __construct(private SettingsAPI $settings_api)
    {
        $this->settings_api->setSettingsPageHook(FeaturedImage::PLUGIN_SLUG);
        $this->settings_api->addHooks();
    }

    public function addHooks(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_action('network_admin_menu', [$this, 'addPluginAdminMenu']);
        add_action(
            'load-' . $this->settings_api->getSettingsPageHook(),
            [$this, 'saveNetworkSettings',],
            10,
            0
        );

        add_filter('wpmu_blogs_columns', [$this, 'featuredImageColumn']);
        add_action('manage_sites_custom_column', [$this, 'featuredImageCustomColumn',], 10, 2);
    }

    /**
     * @return SettingsAPI
     */
    public function getSettingsApi(): SettingsAPI
    {
        return $this->settings_api;
    }

    /**
     * Register and enqueue admin-specific scripts.
     */
    public function enqueueAdminScripts(): void
    {
        if (!isset($this->plugin_screen_hook_suffix)) {
            return;
        }

        if ($this->plugin_screen_hook_suffix === str_replace('-network', '', get_current_screen()->id)) {
            wp_enqueue_style(
                FeaturedImage::PLUGIN_SLUG . '-admin',
                plugins_url('assets/css/admin.css', Common::getBaseFile()),
                [],
                FeaturedImage::VERSION
            );
            wp_enqueue_script(
                FeaturedImage::PLUGIN_SLUG . '-admin',
                plugins_url('assets/js/admin.js', Common::getBaseFile()),
                ['jquery'],
                FeaturedImage::VERSION
            );
        }
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     * Add a settings page for this plugin to the Settings menu.
     */
    public function addPluginAdminMenu(): void
    {
        $this->plugin_screen_hook_suffix = add_submenu_page(
            'settings.php',
            __('Multisite Featured Image(s)', 'ms-featured-image'),
            __('Featured Image(s)', 'ms-featured-image'),
            'manage_options',
            FeaturedImage::PLUGIN_SLUG,
            [$this, 'settingsCallback',]
        );

        //set the settings
        $this->settings_api->setSections($this->getSettingsSections());
        $this->settings_api->setFields($this->getSettingsFields());

        //initialize settings
        $this->settings_api->adminInit();
    }

    /**
     * Render the settings page for this plugin.
     */
    public function settingsCallback(): void
    {
        include dirname(__DIR__, 2) . '/views/admin.php';
    }

    /**
     * Save the settings
     */
    public function saveNetworkSettings(): void
    {
        if (
            isset($_POST[FeaturedImage::SUBMIT]) &&
            !empty($_POST[FeaturedImage::OPTION_NAME])
        ) {
            if (!wp_verify_nonce($_REQUEST[SettingsAPI::NONCE_KEY], FeaturedImage::PLUGIN_SLUG)) {
                wp_die(__('Nonce error!'));
            }

            $options = get_site_option(FeaturedImage::OPTION_NAME, []);
            $sites = $this->getBlogSites();

            foreach ($sites as $site) {
                if (isset($options[0])) {
                    unset($options[0]);
                }
                $blog_id = $site->blog_id;
                $options["blog_id_$blog_id"] = [
                    'url' => !empty($_POST[FeaturedImage::OPTION_NAME]["blog_id_$blog_id"]['url']) ?
                        esc_url($_POST[FeaturedImage::OPTION_NAME]["blog_id_$blog_id"]['url']) : '',
                    'id' => !empty($_POST[FeaturedImage::OPTION_NAME]["blog_id_$blog_id"]['id']) ?
                        absint($_POST[FeaturedImage::OPTION_NAME]["blog_id_$blog_id"]['id']) : '',
                ];
            }

            update_site_option(FeaturedImage::OPTION_NAME, $options);

            wp_safe_redirect(
                add_query_arg(
                    [
                        'page' => FeaturedImage::PLUGIN_SLUG,
                        'updated' => 'true',
                    ],
                    network_admin_url('settings.php')
                )
            );
            exit;
        }
    }

    /**
     * Create out custom column and sort it first!
     * @param array $columns
     * @return array
     */
    public function featuredImageColumn(mixed $columns): array
    {
        if (!is_array($columns)) {
            $columns = [];
        }

        $new = [];

        foreach ($columns as $key => $title) {
            // Put the Thumbnail column before the Blogname column
            if ($key === 'blogname') {
                $new[self::COLUMN_NAME] = __('Image', 'ms-featured-image');
            }

            $new[$key] = $title;
        }

        return $new;
    }

    /**
     * Load our custom column with our featured image.
     * @param string $column_name
     * @param int $blog_id
     */
    public function featuredImageCustomColumn(string $column_name, mixed $blog_id): void
    {
        if (self::COLUMN_NAME !== $column_name) {
            return;
        }

        $options = get_site_option(FeaturedImage::OPTION_NAME, []);
        $image_id = $options['blog_id_' . $blog_id]['id'] ?? null;

        if (!empty($image_id)) {
            echo wp_get_attachment_image($image_id, [50, 50]);
        } else {
            echo apply_filters(
                'ms_featured_image_placeholder_image',
                '<img src="https://placeholdit.com/50/dddddd/999999?text=FM" alt="Placeholder">'
            );
        }
    }

    /**
     * Register the settings sections (tabs).
     * @return array
     */
    private function getSettingsSections(): array
    {
        return [
            [
                'id' => FeaturedImage::OPTION_NAME,
                'title' => sprintf(
                    __('Sites in the %s Network', 'ms-featured-image'),
                    $this->getNetworkName()
                ),
            ],
        ];
    }

    /**
     * Returns all the settings fields
     * @return array settings fields
     */
    private function getSettingsFields(): array
    {
        $network = get_network();
        $sites = $this->getBlogSites(['network_id' => (int)$network?->blog_id]);
        $sites_array = [];

        foreach ($sites as $site) {
            $blog_id = $site->blog_id;
            $blog_details = get_blog_details(absint($blog_id));
            $sites_array[] = [
                'name' => "blog_id_$blog_id",
                'label' => sprintf(
                    _x(
                        '<span title="%s">Featured Image</span>',
                        'Settings page setting title for site featured image.',
                        'ms-featured-image'
                    ),
                    sprintf(
                        esc_attr__(
                            'Featured Image for site titled &ldquo;%s&rdquo; with site ID &ldquo;%s&rdquo;',
                            'ms-featured-image'
                        ),
                        esc_attr($blog_details->blogname),
                        esc_attr($blog_id)
                    )
                ),
                'desc' => sprintf(
                    __(
                        'Featured Image for <code title="Site ID: &ldquo;%2$s&rdquo;">%1$s</code>, URL:<code>%3$s</code>',
                        'ms-featured-image'
                    ),
                    esc_attr($blog_details->blogname),
                    esc_attr($blog_id),
                    esc_attr($site->domain . $site->path)
                ),
                'type' => 'file',
                'default' => '',
                'sanitize_callback' => 'esc_url',
            ];
        }

        return [
            FeaturedImage::OPTION_NAME => $sites_array,
        ];
    }
}
