<?php

namespace FrostyMedia\MSFeaturedImage\Includes\Admin;

use FrostyMedia\MSFeaturedImage\Includes\Common;
use FrostyMedia\MSFeaturedImage\Includes\FeaturedImage;

/**
 * Class FeaturedImageAdmin
 * @package FrostyMedia\MSFeaturedImage\Includes\Admin
 */
class FeaturedImageAdmin {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;
	
	/**
	 * Extendd variables
	 */
	private $plugin_id, $plugin_name;

	/**
	 * Slug of the plugin screen.
	 *
	 * @var string
	 */
	protected $plugin_screen_hook_suffix = null;
	
	/**
	 * Settings API
	 *
	 * @var array
	 */
	private $settings_api;

	/**
	 * Return an instance of this class.
	 *
	 * @return object
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	private function __construct() {

		$this->plugin_id = 'multisite_featured_image';
		$this->plugin_name = 'Multisite Featured Image';

		$this->plugin_slug = FeaturedImage::get_plugin_slug();
		$this->plugin_screen_hook_suffix = FeaturedImage::get_settings_page_hook();
		$this->settings_api = new SettingsAPI();

//		add_filter( 'http_request_args', array( $this, 'hideMe' ), 5, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		add_action( 'network_admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'network_admin_plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'save_network_settings_page' ), 10, 0 );

		add_filter( 'wpmu_blogs_columns', array( $this, 'featured_image_column' ) );
		add_action( 'manage_sites_custom_column', array( $this, 'featured_image_custom_column' ), 10, 2 );
	}

	/**
	 * @param $r
	 * @param $url
	 *
	 * @return mixed
	 */
	function hideMe( $r, $url ) {

		// Not a plugin update request. Bail immediately.
		if ( false !== strpos( $url, '//api.wordpress.org/plugins/update-check' ) )
			return $r;

		$plugins = unserialize( $r['body']['plugins'] );

		if ( isset( $plugins->plugins ) && is_array( $plugins->plugins ) ) {
			unset( $plugins->plugins[ plugin_basename( FeaturedImage::get_base_file() ) ] );
		}

		if ( isset( $plugins->active ) && is_array( $plugins->active ) ) {
			unset( $plugins->active[ array_search( plugin_basename( FeaturedImage::get_base_file() ), $plugins->active ) ] );
		}

		$r['body']['plugins'] = serialize( $plugins );

		return $r;
	}

	/**
	 * Register and enqueue admin-specific scripts.
	 */
	public function enqueue_admin_scripts() {

		if ( !isset( $this->plugin_screen_hook_suffix ) ) return;

		$screen = get_current_screen();

		if ( $this->plugin_screen_hook_suffix === str_replace( '-network', '', $screen->id ) ) {

			wp_enqueue_style( $this->plugin_slug . '-admin', plugins_url( 'css/admin.css', FeaturedImage::get_base_file() ), array(), FeaturedImage::VERSION );
			wp_enqueue_script( $this->plugin_slug . '-admin', plugins_url( 'js/admin.js', FeaturedImage::get_base_file() ), array( 'jquery' ), FeaturedImage::VERSION );
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 * Add a settings page for this plugin to the Settings menu.
	 *
	 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
	 *
	 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
	 *
	 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_submenu_page( 'settings.php', __( 'Featured Site Image', $this->plugin_slug ), __( 'Featured Image', $this->plugin_slug ), 'manage_options', $this->plugin_slug, array( $this, 'display_plugin_admin_page' ) );
		
		// Adds admin_help_tab when my_admin_page loads
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'admin_help_tab' ) );
		
		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		//initialize settings
		$this->settings_api->admin_init();
	}
	
	/**
	 * Add admin help tab
	 *
	 * @since    1.0.0
	 */
	function admin_help_tab() {
		$screen = get_current_screen();

		/**
		 * Check if current screen is My Admin Page
		 * Don't add help tab if it's not
		 */
		if ( $this->plugin_screen_hook_suffix !== str_replace( '-network', '', $screen->id ) ) return;

		$screen->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview' ),
			'content' => '<p>' . __( 'This screen manages options for the network as a whole. The first site is the main site in the network and each site in the network follows.', $this->plugin_slug ) . '</p>' .
					'<p>' . __( 'Each input allowd image URLs from anywhere.' ) . '</p>' .
					'<p>' . __( 'Click the &lsquo;Broswe&rsquo; button to open the default WordPress media browser to upload or use an image already in your network.', $this->plugin_slug ) . '</p>' .
					'<p>' . __( 'Clicking &lsquo;Clear&rsquo; empties the input filed directly to the left.' ) . '</p>' .
					'<p>' . __( 'Clicking &lsquo;Save Changes&rsquo; saves each sites featured image (if correct a thumbnail should show up).', $this->plugin_slug ) . '</p>' .
					'<p>' . sprintf( __( 'To call the image from any site or from the main site (network) use: %s.', $this->plugin_slug ), '<code>&lt;?php echo ms_featured_image_get_site_featured_image( $blog_id, $image_size, $image_html_output =  true );</code>' ) . '</p>'
		) );

		$screen->set_help_sidebar( '<p><strong>' . __( 'For more information:', $this->plugin_slug ) . '</strong></p>' . '<p>' . __( '<a href="//frosty.media/plugins/multisite-featured-image/" target="_blank">Multisite Featured Image</a>', $this->plugin_slug ) . '</p>' . '<p>' . __( '<a href="//frosty.media/docs/" target="_blank">Documentation</a>', $this->plugin_slug ) . '</p>' );
	}
	
	/**
	 * Register the settings sections (tabs).
	 *
	 * @since    1.0.0
	 */
	private function get_settings_sections() {

		$sections = array(
			array(
				'id' => FeaturedImage::OPTION_NAME,
				'title' => __( 'Sites', $this->plugin_slug )
			),
		);

		return $sections;
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	private function get_settings_fields() {
		/**
		 * $sites = Array (
		 *      [0] => Array (
		 *          [blog_id] => 1
		 *          [domain] => passy.co
		 *          [path] => /
		 *      )
		 * )
		 */
		$sites = Common::objectToArray( $this->get_blog_sites() );
		
		$sites_array = array();

		foreach( $sites as $key => $site ) :

			$sites_array[] = array(
				'name' => "blog_id_{$site['blog_id']}",
				'label' => __( 'Image URL:', $this->plugin_slug ),
				'desc' => __( "Featured image for <code>{$site['domain']}</code>", $this->plugin_slug ),
				'type' => 'file',
				'default' => '',
				'sanitize_callback' => 'esc_url'
			);
		endforeach;
		
		$settings_fields = array(
			FeaturedImage::OPTION_NAME => $sites_array
		);

		return $settings_fields;
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function display_plugin_admin_page() {
		FeaturedImage::views( 'admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links( $links ) {

		return array_merge( array(
			'settings' => '<a href="' . add_query_arg( array( 'page' => $this->plugin_slug ), network_admin_url( 'settings.php' ) ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>' . ' | ' . '<a href="' . add_query_arg( array( 'page' => 'extendd_license_settings' ), admin_url( 'options-general.php' ) ) . '">' . __( 'License', $this->plugin_slug ) . '</a>'
		), $links );

	}
	
	/**
	 * Save the settings
	 */
	function save_network_settings_page() {

		//delete_site_option( FeaturedImage::OPTION_NAME );
		
		if ( isset( $_POST['ms-featured-image_submit'] ) && !empty( $_POST[ FeaturedImage::OPTION_NAME ] ) ) {

			$options = (array) get_site_option( FeaturedImage::OPTION_NAME );

			if ( !wp_verify_nonce( $_REQUEST['_msfi_nonce'], 'ms-feat-img' ) ) wp_die( __( 'Are you sure you want to do this?' ), __( 'WordPress Security Stop' ) );
			
			$sites = Common::objectToArray( $this->get_blog_sites() );

			foreach( $sites as $key => $option ) {
				if ( isset( $options[0] ) ) unset( $options[0] );
				$options["blog_id_{$option['blog_id']}"] = !empty( $_POST[ FeaturedImage::OPTION_NAME ]["blog_id_{$option['blog_id']}"] ) ? esc_url( $_POST[ FeaturedImage::OPTION_NAME ]["blog_id_{$option['blog_id']}"] ) : '';
			}

			update_site_option( FeaturedImage::OPTION_NAME, $options );

			wp_redirect( add_query_arg( array(
				'page' => $this->plugin_slug,
				'updated' => 'true'
			), network_admin_url( 'settings.php' ) ) );
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
	private function get_blog_sites() {
		global $wpdb;

		// Query all blogs from multi-site install
		$blogs = $wpdb->get_results( "
			SELECT blog_id, domain, path FROM $wpdb->blogs
			WHERE archived = '0'
			AND spam = '0'
			AND deleted = '0'
			AND blog_id > 0
			ORDER BY path" );
		
		return $blogs;
	}

	/**
	 * Create out custom column and sort it first!
	 *
	 * @since    1.0.0
	 */
	public function featured_image_column( $columns ) {

		if ( !is_array( $columns ) ) $columns = array();

		$new = array();
		
		foreach( $columns as $key => $title ) {
			if ( $key == 'blogname' ) // Put the Thumbnail column before the Blogname column
				$new['featured-image'] = __( 'Image', $this->plugin_slug );
			
			$new[ $key ] = $title;
		}

		return $new;
	}

	/**
	 * Load our custom column with our featured image.
	 *
	 * @since    1.0.0
	 */
	public function featured_image_custom_column( $column_name, $blog_id ) {
		
		if ( 'featured-image' !== $column_name ) return;

		switch( $column_name ) {
			case 'featured-image' :
				
				$options = get_site_option( FeaturedImage::OPTION_NAME, array() );
				$image_id = Common::urlToAttachmentID( $options[ 'blog_id_' . $blog_id ] );
				
				if ( !is_null( $image_id ) ) {
					echo wp_get_attachment_image( $image_id, array( 50, 50 ) );
				}
				break;
		}
	}

	/**
	 * @return SettingsAPI
	 */
	public static function getSettingsAPI() {
		return self::instance()->settings_api;
	}

	public static function getPluginID() {
		return self::instance()->plugin_id;
	}

}
