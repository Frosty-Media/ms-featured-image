<?php

namespace FrostyMedia\MSFeaturedImage\Admin;

use FrostyMedia\MSFeaturedImage\Common;
use FrostyMedia\MSFeaturedImage\FeaturedImage;
use FrostyMedia\MSFeaturedImage\WpHooksInterface;

/**
 * Class FeaturedImageAdmin
 *
 * @package FrostyMedia\MSFeaturedImage\Admin
 */
class FeaturedImageAdmin implements WpHooksInterface {

    const COLUMN_NAME = FeaturedImage::PLUGIN_SLUG;

    /**
     * Settings API
     *
     * @var SettingsApi
     */
    private $settings_api;

    /** @var string $plugin_screen_hook_suffix */
    private $plugin_screen_hook_suffix;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a settings page and menu.
     *
     * @param SettingsApi $settings_api
     */
    public function __construct( SettingsApi $settings_api ) {
        $this->settings_api = $settings_api;
        $this->settings_api->setSettingsPageHook( FeaturedImage::PLUGIN_SLUG );
        $this->settings_api->addHooks();
    }

    public function addHooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminScripts' ] );
        add_action( 'network_admin_menu', [ $this, 'addPluginAdminMenu' ] );
        add_action( 'load-' . $this->settings_api->getSettingsPageHook(),
            [ $this, 'saveNetworkSettings', ], 10, 0 );

        add_filter( 'wpmu_blogs_columns', [ $this, 'featuredImageColumn' ] );
        add_action( 'manage_sites_custom_column', [ $this, 'featuredImageCustomColumn', ], 10, 2 );
    }

    /**
     * @return SettingsApi
     */
    public function getSettingsApi(): SettingsApi {
        return $this->settings_api;
    }

    /**
     * Register and enqueue admin-specific scripts.
     */
    public function enqueueAdminScripts() {
        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }

        if ( $this->plugin_screen_hook_suffix === str_replace( '-network', '', get_current_screen()->id ) ) {
            wp_enqueue_style( FeaturedImage::PLUGIN_SLUG . '-admin', plugins_url( 'assets/css/admin.css', Common::getBaseFile() ), [], FeaturedImage::VERSION );
            wp_enqueue_script( FeaturedImage::PLUGIN_SLUG . '-admin', plugins_url( 'assets/js/admin.js', Common::getBaseFile() ), [ 'jquery' ], FeaturedImage::VERSION );
        }
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     * Add a settings page for this plugin to the Settings menu.
     */
    public function addPluginAdminMenu() {
        $this->plugin_screen_hook_suffix = add_submenu_page(
            'settings.php',
            __( 'Featured Site Image', FeaturedImage::PLUGIN_SLUG ),
            __( 'Featured Image', FeaturedImage::PLUGIN_SLUG ),
            'manage_options',
            FeaturedImage::PLUGIN_SLUG,
            [ $this, 'settingsCallback', ]
        );

        // Adds admin_help_tab when my_admin_page loads
        add_action( 'load-' . $this->plugin_screen_hook_suffix, [ $this, 'adminHelpTab' ] );

        //set the settings
        $this->settings_api->setSections( $this->getSettingsSections() );
        $this->settings_api->setFields( $this->getSettingsFields() );

        //initialize settings
        $this->settings_api->adminInit();
    }

    /**
     * Add admin help tab
     */
    function adminHelpTab() {
        $screen = get_current_screen();

        /**
         * Check if current screen is My Admin Page
         * Don't add help tab if it's not
         */
        if ( $this->plugin_screen_hook_suffix !== str_replace( '-network', '', $screen->id ) ) {
            return;
        }

        $screen->add_help_tab(
            [
                'id' => 'overview',
                'title' => __( 'Overview' ),
                'content' => '<p>' . __( 'This screen manages options for the network as a whole. The first site is the main site in the network and each site in the network follows.', FeaturedImage::PLUGIN_SLUG ) . '</p>' .
                             '<p>' . __( 'Each input allowd image URLs from anywhere.', FeaturedImage::PLUGIN_SLUG ) . '</p>' .
                             '<p>' . __( 'Click the &lsquo;Broswe&rsquo; button to open the default WordPress media browser to upload or use an image already in your network.', FeaturedImage::PLUGIN_SLUG ) . '</p>' .
                             '<p>' . __( 'Clicking &lsquo;Clear&rsquo; empties the input filed directly to the left.', FeaturedImage::PLUGIN_SLUG ) . '</p>' .
                             '<p>' . __( 'Clicking &lsquo;Save Changes&rsquo; saves each sites featured image (if correct a thumbnail should show up).', FeaturedImage::PLUGIN_SLUG ) . '</p>' .
                             '<p>' . sprintf( __( 'To call the image from any site or from the main site (network) use: %s.', FeaturedImage::PLUGIN_SLUG ), '<code>&lt;?php echo ms_featured_image_get_site_featured_image( $blog_id, $image_size, $image_html_output =  true );</code>' ) . '</p>',
            ]
        );

        $screen->set_help_sidebar( '<p><strong>' . __( 'For more information:', FeaturedImage::PLUGIN_SLUG ) . '</strong></p>' . '<p>' . __( '<a href="//frosty.media/plugins/multisite-featured-image/" target="_blank">Multisite Featured Image</a>', FeaturedImage::PLUGIN_SLUG ) . '</p>' . '<p>' . __( '<a href="//frosty.media/docs/" target="_blank">Documentation</a>', FeaturedImage::PLUGIN_SLUG ) . '</p>' );
    }

    /**
     * Render the settings page for this plugin.
     */
    public function settingsCallback() {
        include dirname( dirname( __DIR__ ) ) . '/views/admin.php';
    }

    /**
     * Save the settings
     */
    public function saveNetworkSettings() {

        //delete_site_option( FeaturedImage::OPTION_NAME );

        if ( isset( $_POST[ FeaturedImage::PLUGIN_SLUG . '_submit' ] ) &&
             ! empty( $_POST[ FeaturedImage::OPTION_NAME ] )
        ) {

            if ( ! wp_verify_nonce( $_REQUEST[ SettingsApi::NONCE_KEY ], FeaturedImage::PLUGIN_SLUG ) ) {
                wp_die( __( 'Nonce error!' ) );
            }

            $options = get_site_option( FeaturedImage::OPTION_NAME, [] );
            $sites   = Common::objectToArray( $this->getBlogSites() );

            foreach ( $sites as $key => $option ) {
                if ( isset( $options[0] ) ) {
                    unset( $options[0] );
                }
                $options["blog_id_{$option['blog_id']}"] = ! empty( $_POST[ FeaturedImage::OPTION_NAME ]["blog_id_{$option['blog_id']}"] ) ?
                    esc_url( $_POST[ FeaturedImage::OPTION_NAME ]["blog_id_{$option['blog_id']}"] ) :
                    '';
            }

            update_site_option( FeaturedImage::OPTION_NAME, $options );

            wp_safe_redirect( add_query_arg(
                [
                    'page' => FeaturedImage::PLUGIN_SLUG,
                    'updated' => 'true',
                ],
                network_admin_url( 'settings.php' )
            ) );
            exit;
        }
    }

    /**
     * Get all blog ids, domains & path of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @return   array|false    The blog ids, domain & path | false if no matches.
     */
    private function getBlogSites() {
        global $wpdb;

        // Query all blogs from multi-site install
        $blogs = $wpdb->get_results( "
			SELECT blog_id, domain, path FROM $wpdb->blogs
			WHERE archived = '0'
			AND spam = '0'
			AND deleted = '0'
			AND blog_id > 0
			AND public = 1
			ORDER BY blog_id" );

        return $blogs;
    }

    /**
     * Create out custom column and sort it first!
     *
     * @param array $columns
     *
     * @return array
     */
    public function featuredImageColumn( $columns ) {
        if ( ! is_array( $columns ) ) {
            $columns = [];
        }

        $new = [];

        foreach ( $columns as $key => $title ) {
            // Put the Thumbnail column before the Blogname column
            if ( $key == 'blogname' ) {
                $new[ self::COLUMN_NAME ] = __( 'Image', FeaturedImage::PLUGIN_SLUG );
            }

            $new[ $key ] = $title;
        }

        return $new;
    }

    /**
     * Load our custom column with our featured image.
     *
     * @param string $column_name
     * @param int $blog_id
     */
    public function featuredImageCustomColumn( $column_name, $blog_id ) {
        if ( self::COLUMN_NAME !== $column_name ) {
            return;
        }

        switch ( $column_name ) {
            case self::COLUMN_NAME :
                $options  = get_site_option( FeaturedImage::OPTION_NAME, [] );
                $image_id = Common::urlToAttachmentID( $options[ 'blog_id_' . $blog_id ] );

                if ( ! is_null( $image_id ) ) {
                    echo wp_get_attachment_image( $image_id, [ 50, 50 ] );
                }
                break;
        }
    }

    /**
     * Register the settings sections (tabs).
     *
     * @return array
     */
    private function getSettingsSections(): array {
        $sections = [
            [
                'id' => FeaturedImage::OPTION_NAME,
                'title' => __( 'Sites', FeaturedImage::PLUGIN_SLUG ),
            ],
        ];

        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    private function getSettingsFields(): array {
        /**
         * $sites = Array (
         *      [0] => Array (
         *          [blog_id] => 1
         *          [domain] => passy.co
         *          [path] => /
         *      )
         * )
         */
        $sites = Common::objectToArray( $this->getBlogSites() );

        $sites_array = [];

        foreach ( $sites as $key => $site ) {
            $sites_array[] = [
                'name' => "blog_id_{$site['blog_id']}",
                'label' => __( 'Featured Image', FeaturedImage::PLUGIN_SLUG ),
                'desc' => sprintf(
                    __( 'Featured image for Site: <strong title="Site ID &ldquo;%1$s&rdquo;">%1$s</strong>. <code>%2$s</code>', FeaturedImage::PLUGIN_SLUG ),
                    abs( $site['blog_id'] ),
                    esc_attr( $site['domain'] . $site['path'] )
                ),
                'type' => 'file',
                'default' => '',
                'sanitize_callback' => 'esc_url',
            ];
        }

        $settings_fields = [
            FeaturedImage::OPTION_NAME => $sites_array,
        ];

        return $settings_fields;
    }
}
