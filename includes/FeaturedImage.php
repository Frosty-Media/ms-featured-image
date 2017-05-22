<?php

namespace FrostyMedia\MSFeaturedImage\Includes;

use FrostyMedia\MSFeaturedImage\Includes\Admin\FeaturedImageAdmin;
use FrostyMedia\MSFeaturedImage\Includes\Admin\FrostyMediaLicense;

/**
 * Class FeaturedImage
 * @package FrostyMedia\MSFeaturedImage\Includes
 */
class FeaturedImage {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @var string
	 */
	const VERSION = '2.1.0';

	/**
	 * DB option string name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'ms_featured_image';

	/**
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @var string
	 */
	private $plugin_slug = 'ms-featured-image';

	/**
	 * @var string
	 */
	private $base_file;

	/**
	 * Return an instance of this class.
	 *
	 * @return self|object
	 */
	public static function instance( $base_file = null ) {

		if ( null === self::$instance ) {
			self::$instance = new self;
			if ( !is_null( $base_file ) ) {
				self::$instance->base_file = $base_file;
			}
			self::$instance->includes();
			self::$instance->instansiations();
			self::$instance->addActions();
		}

		return self::$instance;
	}

	/**
	 * Don't play with fire.
	 */
	private function __construct() {
	}

	/**
	 * Include classes.
	 */
	private function includes() {

		require_once( plugin_dir_path( self::get_base_file() ) . 'includes/Common.php' );
		require_once( plugin_dir_path( self::get_base_file() ) . 'includes/functions.php' );
		require_once( plugin_dir_path( self::get_base_file() ) . 'includes/functions-deprecated.php' );

		if ( $this->isAdmin() ) {
			require_once( plugin_dir_path( self::get_base_file() ) . 'includes/Admin/FrostyMediaLicense.php' );
			require_once( plugin_dir_path( self::get_base_file() ) . 'includes/Admin/class-frosty-media-requires.php' );
			require_once( plugin_dir_path( self::get_base_file() ) . 'includes/Admin/SettingsAPI.php' );
			require_once( plugin_dir_path( self::get_base_file() ) . 'includes/Admin/FeaturedImageAdmin.php' );
		} else {
			require_once( plugin_dir_path( self::get_base_file() ) . 'includes/Shortcode.php' );
		}
	}

	/**
	 * Setup our admin class.
	 */
	private function instansiations() {

		if ( $this->isAdmin() ) {
			FeaturedImageAdmin::instance();
			new FrostyMediaLicense();
		} else {
			Shortcode::instance()->addShortcode();
		}
	}

	private function addActions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	/**
	 * Register shortcode styles.
	 */
	public function enqueueScripts() {
		wp_register_style( 'ms-featured-image', plugins_url( '/css/sites.css', dirname( __FILE__ ) ), array(), self::VERSION );
	}

	/**
	 * @param bool $doing_ajax
	 *
	 * @return bool
	 */
	private function isAdmin( $doing_ajax = true ) {
		return $doing_ajax ? is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) : is_admin();
	}

	/**
	 * Return the plugin slug.
	 *
	 * @return string
	 */
	public static function get_plugin_slug() {
		return self::instance()->plugin_slug;
	}

	/**
	 * Return the plugin file path.
	 *
	 * @return string
	 */
	public static function get_base_file() {
		return self::instance()->base_file;
	}

	/**
	 * Return the plugin settings slug.
	 *
	 * @return string.
	 */
	public static function get_settings_page_hook() {
		return 'settings_page_' . self::instance()->plugin_slug;
	}

	/**
	 * @param string $file
	 * @param null|array $atts
	 *
	 * @return string
	 */
	public static function views( $file = '', $atts = null ) {
		include( plugin_dir_path( self::get_base_file() ) . "views/$file" );
	}

}
