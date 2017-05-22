<?php
/**
 * Plugin Name.
 *
 * @package   MS_Featured_Image_Admin
 * @author    Austin Passy <austin@thefrosty.com>
 * @license   GPL-2.0+
 * @link      http://austinpassy.com
 * @copyright 2013 Austin Passy
 */

class MS_Featured_Image_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;
	
	/**
	 * Extendd variables
	 */
	private $plugin_id,
			$plugin_name;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;
	
	/**
	 * Settings API
	 *
	 * @since 1.0.0
	 *
	 * @var		array
	 */
	private $settings_api;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 *
		if ( !is_super_admin() )
			return;
		 */
		
		/*
		 * Extendd variables
		 */
		$this->plugin_id	= 'extendd_multisite_featured_image';
		$this->plugin_name	= 'Multisite Featured Image';
		
		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = MS_Featured_Image::get_instance();
		$this->plugin_slug	= $plugin->get_plugin_slug();
        $this->plugin_screen_hook_suffix = $plugin->get_settings_page_hook();
        $this->settings_api = new MS_Featured_Image_Settings_API;

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'network_admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'network_admin_plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		
		// Save the images for each site
		add_action( 'load-' . $this->plugin_screen_hook_suffix, array( $this, 'save_network_settings_page' ), 10, 0 );
		
		// Create the custom column on network/sites.php
		add_filter( 'wpmu_blogs_columns', array( $this, 'featured_image_column' ) );
		add_action( 'manage_sites_custom_column', array( $this, 'featured_image_custom_column' ), 10, 2 );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$instance->extendd_settings();
		}

		return self::$instance;
	}	
	
	/**
	 * Upgrade script
	 *
	 * @since	1.0.0
	 */
	function extendd_settings() {
		if ( !class_exists( 'extendd_settings_api' ) ) {
			include( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/extendd-settings.php' );
		}
		add_filter( 'extendd_add_settings_sections', 	array( $this, 'add_settings_section' ) );
		add_filter( 'extendd_add_settings_fields',		array( $this, 'add_settings_fields' ) );
		
		// Remove 'admin_menu' for non super admins
		if ( !is_super_admin() || !is_main_site() )
	        remove_action( 'admin_menu', array( EXTENDD_settings_init(), 'admin_menu' ) );
				
		$options = get_option( $this->plugin_id, array() );
		if ( isset( $options ) && ( !empty( $options['license_key'] ) && 'valid' === $options['license_active'] ) ) {
			if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				// load our custom updater
				include( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/EDD_SL_Plugin_Updater.php' );
			}
		
			$edd_updater = new EDD_SL_Plugin_Updater( 'http://extendd.com/', MS_FEATURED_IMAGE_FILE,
				array(
					'version'   => MS_Featured_Image::VERSION, // current version number
					'license'   => trim( $options['license_key'] ), // license key
					'item_name' => $this->plugin_name, // name of this plugin in the Easy Digital Downloads system
					'author'    => 'Austin Passy' // author of this plugin
				)
			);
		}
	}
	
	/**
	 * Returns new settings section for this plugin
	 *
	 * @updated 3/6/13
	 * @return 	array settings fields
	 */
	function add_settings_section( $sections ) {
		$sections[] = array(
			'id' 		=> $this->plugin_id,
			'title' 	=> $this->plugin_name, //Must match EDD post_title!
			'basename'	=> MS_FEATURED_IMAGE_BASENAME,
			'version'   => MS_Featured_Image::VERSION,
		);
		return $sections;
	}
	
	/**
	 * Returns new settings fields for this plugin
	 *
	 * @return 	array settings fields
	 */
	function add_settings_fields( $settings_fields ) {
		$settings_fields[$this->plugin_id] = array(
			array(
				'name' 			=> 'license_key',
				'label' 		=> __( 'License Key', $this->plugin_slug ),
				'desc' 			=> sprintf( __( 'Enter your license for %s to receive automatic updates', $this->plugin_slug ), $this->plugin_name ),
				'type' 			=> 'text',
				'default' 		=> '',
				'placeholder'	=> __( 'Enter your license key', $this->plugin_slug )
			),
			array(
				'name' 			=> 'license_active',
				'label' 		=> '',
				'desc' 			=> '',
				'type' 			=> 'hidden',
				'default' 		=> ''
			),
			array(
				'name' 			=> 'license_deactivate',
				'label' 		=> __( 'De-activate License?', $this->plugin_slug ),
				'desc' 			=> '',
				'type' 			=> 'checkbox',
				'default' 		=> ''
			),
		);	
		return $settings_fields;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "MS_Featured_Image" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( !isset( $this->plugin_screen_hook_suffix ) )
			return;

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix === str_replace( '-network', '', $screen->id ) ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), MS_Featured_Image::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @TODO:
	 *
	 * - Rename "MS_Featured_Image" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( !isset( $this->plugin_screen_hook_suffix ) ) 
			return;

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix === str_replace( '-network', '', $screen->id ) ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), MS_Featured_Image::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_submenu_page(
			'settings.php',
			__( 'Featured Site Image', $this->plugin_slug ),
			__( 'Featured Image', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
		
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
	 * @since	1.0.0
	 */
	function admin_help_tab() {
		$screen = get_current_screen();

		/**
		 * Check if current screen is My Admin Page
		 * Don't add help tab if it's not
		 */
		if ( $this->plugin_screen_hook_suffix !== str_replace( '-network', '', $screen->id ) )
			return;
			
		$screen->add_help_tab( array(
			'id'      => 'overview',
			'title'   => __('Overview'),
			'content' =>
				'<p>' . __( 'This screen manages options for the network as a whole. The first site is the main site in the network and each site in the network follows.', $this->plugin_slug ) . '</p>' .
				'<p>' . __( 'Each input allowd image URLs from anywhere.') . '</p>' .
				'<p>' . __( 'Click the &lsquo;Broswe&rsquo; button to open the default WordPress media browser to upload or use an image already in your network.', $this->plugin_slug ) . '</p>' .
				'<p>' . __( 'Clicking &lsquo;Clear&rsquo; empties the input filed directly to the left.') . '</p>' .
				'<p>' . __( 'Clicking &lsquo;Save Changes&rsquo; saves each sites featured image (if correct a thumbnail should show up).', $this->plugin_slug ) . '</p>' .
				'<p>' . sprintf( __( 'To call the image from any site or from the main site (network) use: %s.', $this->plugin_slug ), '<code>&lt;?php echo get_site_featured_image( $blog_id, $image_size, $image_html_output =  true );</code>' ) . '</p>'
		) );
	
		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', $this->plugin_slug ) . '</strong></p>' .
			'<p>' . __( '<a href="http://extendd.com/plugin/multisite-featured-image/" target="_blank">Documentation</a>', $this->plugin_slug ) . '</p>' .
			'<p>' . __( '<a href="http://extendd.com/support/forum/multisite-featured-image/" target="_blank">Support Forums</a>', $this->plugin_slug ) . '</p>'
		);
	}
	
	/**
	 * Register the settings sections (tabs).
	 *
	 * @since    1.0.0
	 */
	private function get_settings_sections() {
        $sections = array(
			array(
                'id'	=> 'ms_featured_image',
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
		 * $sites =
		 *		Array ( 
		 			[0] => Array ( 
						[blog_id] => 1
						[domain] => passy.co.dev
						[path] => /
					)
				) 
		 */
		$sites	= objectToArray( $this->get_blog_sites() );
		
		$sites_array = array();	
		foreach ( $sites as $key => $site ) :
			$sites_array[] = array(
				'name'				=> "blog_id_{$site['blog_id']}",
				'label'				=> __( 'Image URL:', $this->plugin_slug ),
				'desc'				=> __( "Featured image for <code>{$site['domain']}</code>", $this->plugin_slug ),
				'type'				=> 'file',
				'default'			=> '',
				'sanitize_callback'	=> 'esc_url'
			); 
		endforeach;
		
		//echo '<pre>' . print_r( $sites_array, true ) . '</pre>'; exit;
		
        $settings_fields = array(
        	'ms_featured_image' => $sites_array
        );

        return $settings_fields;
    }

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => 
					'<a href="' . add_query_arg( array( 'page' => $this->plugin_slug ), network_admin_url( 'settings.php' ) ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>' . ' | ' .
					'<a href="' . add_query_arg( array( 'page' => 'extendd_license_settings' ), admin_url( 'options-general.php' ) ) . '">' . __( 'License', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}
	
	/**
	 * Save the settings
	 */	
	function save_network_settings_page() {
			
		//delete_site_option( 'ms_featured_image' );
		
		if ( isset( $_POST['ms-featured-image_submit'] ) && !empty( $_POST['ms_featured_image'] ) ) {
	
			$options = (array) get_site_option( 'ms_featured_image' );
	
			if ( !wp_verify_nonce( $_REQUEST['_msfi_nonce'], 'ms-feat-img' ) )			
				 wp_die( __( 'Are you sure you want to do this?' ), __( 'WordPress Security Stop' ) );
			
			$sites	= objectToArray( $this->get_blog_sites() );
			
			if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
				/**
				echo '<pre>' . print_r( $_REQUEST['_msfi_nonce'], true ) . '</pre>';
				echo '<pre>' . print_r( $sites, true ) . '</pre>';
				echo '<pre>' . print_r( $_POST, true ) . '</pre>';
				exit;
				**/
			}
				
			foreach ( $sites as $key => $option ) {
				if ( isset( $options[0] ) ) unset( $options[0] );
				$options["blog_id_{$option['blog_id']}"] = 
					!empty( $_POST['ms_featured_image']["blog_id_{$option['blog_id']}"] ) ?
					esc_url( $_POST['ms_featured_image']["blog_id_{$option['blog_id']}"] ) :
					'';
			}
		
			update_site_option( 'ms_featured_image', $options );
			wp_redirect( add_query_arg( array( 'page' => $this->plugin_slug, 'updated' => 'true' ), network_admin_url( 'settings.php' ) ) );
			exit;
		}
	}
	
	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}
	
	/**
	 * Get all blog ids, domains & path of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
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
			ORDER BY path"
		);
		
		return $blogs;
	}

	/**
	 * Create out custom column and sort it first!
	 *
	 * @since    1.0.0
	 */
	public function featured_image_column( $columns ) {
		if ( !is_array( $columns ) )
			$columns = array();
			
		$new = array();
		
		foreach( $columns as $key => $title ) {
			if ( $key == 'blogname' ) // Put the Thumbnail column before the Blogname column
				$new['featured-image'] = __( 'Image', $this->plugin_slug );
			
			$new[$key] = $title;
		}
		return $new;
	}

	/**
	 * Load our custom column with our featured image.
	 *
	 * @since    1.0.0
	 */
	public function featured_image_custom_column( $column_name, $blog_id ) {
		
		if ( 'featured-image' !== $column_name )
			return;
				
		switch ( $column_name ) {
			case 'featured-image' :
				
				$options = get_site_option( 'ms_featured_image', array() );
				
				$image_id = url_to_attachmentid( $options['blog_id_' . $blog_id] );
				
				if ( !is_null( $image_id ) ) {					
					echo wp_get_attachment_image( $image_id, array( 50,50 ) );
				}
				
			break;
		}
	}

}