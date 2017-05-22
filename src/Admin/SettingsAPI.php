<?php

namespace FrostyMedia\MSFeaturedImage\Admin;

use FrostyMedia\MSFeaturedImage\Common;
use FrostyMedia\MSFeaturedImage\FeaturedImage;

/**
 * Class SettingsAPI
 *
 * @package FrostyMedia\MSFeaturedImage\Includes\Admin
 */
class SettingsAPI {

    /**
     * settings sections array
     *
     * @var array
     */
    private $settings_sections = [];

    /**
     * Settings fields array
     *
     * @var array
     */
    private $settings_fields = [];

    /**
     * SettingsAPI constructor.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
    }

    /**
     * Enqueue scripts and styles
     */
    public function admin_enqueue_scripts( $hook ) {
        if ( FeaturedImage::get_settings_page_hook() !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_media();
    }

    /**
     * Set settings sections
     *
     * @param array $sections setting sections array
     */
    public function set_sections( $sections ) {
        $this->settings_sections = $sections;
    }

    /**
     * Add a single section
     *
     * @param array $section
     */
    public function add_section( $section ) {
        $this->settings_sections[] = $section;
    }

    /**
     * Set settings fields
     *
     * @param array $fields settings fields array
     */
    public function set_fields( $fields ) {
        $this->settings_fields = $fields;
    }

    /**
     * @param string $section
     * @param string $field
     */
    public function add_field( $section, $field ) {

        $defaults = [
            'name' => '',
            'label' => '',
            'desc' => '',
            'type' => 'text',
        ];

        $arg                                 = wp_parse_args( $field, $defaults );
        $this->settings_fields[ $section ][] = $arg;
    }

    /**
     * Initialize and registers the settings sections and fileds to WordPress
     *
     * Usually this should be called at `admin_init` hook.
     *
     * This function gets the initiated settings sections and fields. Then
     * registers them to WordPress and ready for use.
     */
    public function admin_init() {
        //register settings sections
        foreach ( $this->settings_sections as $section ) {
            if ( false == get_option( $section['id'] ) ) {
                add_option( $section['id'] );
            }

            if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
                $section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
                $callback        = create_function( '', 'echo "' . str_replace( '"', '\"', $section['desc'] ) . '";' );
            } else {
                $callback = '__return_false';
            }

            add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
        }

        //register settings fields
        foreach ( $this->settings_fields as $section => $field ) {
            foreach ( $field as $option ) {

                $type = isset( $option['type'] ) ? $option['type'] : 'text';

                $args = [
                    'id' => $option['name'],
                    'desc' => isset( $option['desc'] ) ? $option['desc'] : '',
                    'name' => $option['label'],
                    'section' => $section,
                    'size' => isset( $option['size'] ) ? $option['size'] : null,
                    'options' => isset( $option['options'] ) ? $option['options'] : '',
                    'std' => isset( $option['default'] ) ? $option['default'] : '',
                    'sanitize_callback' => isset( $option['sanitize_callback'] ) ?
                        $option['sanitize_callback'] : '',
                ];
                add_settings_field( $section . '[' . $option['name'] . ']', $option['label'], [
                    $this,
                    'callback_' . $type,
                ], $section, $section, $args );
            }
        }

        // creates our settings in the options table
        foreach ( $this->settings_sections as $section ) {
            register_setting( $section['id'], $section['id'], [ $this, 'sanitize_options' ] );
        }
    }

    /**
     * Displays a text field for a settings field
     *
     * @param array $args settings field args
     */
    public function callback_text( $args ) {

        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

        $html = '';

        if ( $args['desc'] ) {
            $html .= sprintf( '<span class="description">%s</span><br>', $args['desc'] );
        }

        $html .= sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );

        echo $html;
    }

    /**
     * Displays a file upload field for a settings field
     *
     * @param array $args settings field args
     */
    public function callback_file( $args ) {

        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $id    = $args['section'] . '[' . $args['id'] . ']';
        $desc  = isset( $args['desc'] ) ? $args['desc'] : null;

        if ( ! is_null( $desc ) ) {
            $args['desc'] = null;
        }

        $html = '<div class="alignone clear">';
        $html .= $this->get_the_image( $value );
        $html .= '<div class="alignleft" style="margin-left: 25px">';

        ob_start();
        $this->callback_text( $args );
        $html .= ob_get_clean();

        $html .= '<input type="button" class="button wpsf-browse" id="' . $id . '_button" value="Browse" />';
        $html .= '<input type="button" class="button wpsf-clear" id="' . $id . '_button_clear" value="Clear" />';

        if ( $desc ) {
            $html .= sprintf( '<br><span class="description">%s</span>', $desc );
        }

        $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function get_the_image( $value ) {

        $image_id = ! empty( $value ) ? Common::urlToAttachmentID( $value ) : '';

        if ( ! empty( $image_id ) ) {
            $html = '<div class="alignleft">' . wp_get_attachment_image( $image_id, [
                    50,
                    50,
                ] ) . '</div>';
        } else {
            $html = '<div class="alignleft"><img src="//placehold.it/50?text=FM"></div>';
        }

        return $html;
    }

    /**
     * Sanitize callback for Settings API
     */
    function sanitize_options( $options ) {

        foreach ( $options as $option_slug => $option_value ) {

            $sanitize_callback = $this->get_sanitize_callback( $option_slug );

            // If callback is set, call it
            if ( $sanitize_callback ) {
                $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
                continue;
            }

            // Treat everything that's not an array as a string
            if ( ! is_array( $option_value ) ) {
                $options[ $option_slug ] = sanitize_text_field( $option_value );
                continue;
            }
        }

        return $options;
    }

    /**
     * Get sanitation callback for given option slug
     *
     * @param string $slug option slug
     *
     * @return mixed string or bool false
     */
    private function get_sanitize_callback( $slug = '' ) {

        if ( empty( $slug ) ) {
            return false;
        }

        // Iterate over registered fields and see if we can find proper callback
        foreach ( $this->settings_fields as $section => $options ) {

            foreach ( $options as $option ) {

                if ( $option['name'] != $slug ) {
                    continue;
                }

                // Return the callback name
                return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ?
                    $option['sanitize_callback'] : false;
            }
        }

        return false;
    }

    /**
     * Get the value of a settings field
     *
     * @param string $option settings field name
     * @param string $section the section name this field belongs to
     * @param string $default default text if it's not found
     *
     * @return string
     */
    public function get_option( $option, $section, $default = '' ) {

        $options = get_site_option( $section );

        if ( isset( $options[ $option ] ) ) {
            return $options[ $option ];
        }

        return $default;
    }

    /**
     * Show navigation as tab'd menu.
     *
     * Shows all the settings section labels as tab
     */
    public function show_navigation() {

        $html = '<h2 class="nav-tab-wrapper">';

        foreach ( $this->settings_sections as $tab ) {
            $html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
        }

        $html .= '</h2>';

        echo $html;
    }

    /**
     * Show the section settings forms
     *
     * This function displays every sections in a different form
     */
    public function show_forms() { ?>
        <div class="metabox-holder">
            <div class="postbox">
                <?php foreach ( $this->settings_sections as $form ) { ?>
                    <div id="<?php echo $form['id']; ?>" class="group">
                        <form method="post" action="<?php echo esc_url( add_query_arg( [
                            'page' => FeaturedImage::PLUGIN_SLUG,
                            'action' => 'ms-feat-img',
                            '_msfi_nonce' => wp_create_nonce( 'ms-feat-img' ),
                        ], network_admin_url( 'settings.php' ) ) ); ?>">

                            <?php do_action( 'wsa_form_top_' . $form['id'], $form ); ?>
                            <?php settings_fields( $form['id'] ); ?>
                            <?php do_settings_sections( $form['id'] ); ?>
                            <?php do_action( 'wsa_form_bottom_' . $form['id'], $form ); ?>

                            <div style="padding-left: 10px">
                                <?php submit_button( '', '', FeaturedImage::PLUGIN_SLUG . '_submit' ); ?>
                            </div>
                        </form>

                        <?php
                        if ( defined( 'WP_LOCAL_DEV' ) && ( WP_LOCAL_DEV || WP_DEBUG ) ) {
                            echo '<pre>' . print_r( get_option( $form['id'] ), true ) . '</pre>';
                        } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        $this->script();
    }

    /**
     * JavaScript code.
     */
    private function script() {
        ?>
        <script>
          jQuery(document).ready(function ($, undefined) {
            //Initiate Color Picker
            $('.wp-color-picker-field').wpColorPicker();
            // Switches option sections
            $('.group').hide();
            var activetab = '';
            if (typeof(localStorage) !== undefined) {
              activetab = localStorage.getItem("activetab");
            }
            if (activetab != '' && $(activetab).length) {
              $(activetab).fadeIn();
            } else {
              $('.group:first').fadeIn();
            }
            $('.group .collapsed').each(function () {
              $(this).find('input:checked').parent().parent().parent().nextAll().each(
                function () {
                  if ($(this).hasClass('last')) {
                    $(this).removeClass('hidden');
                    return false;
                  }
                  $(this).filter('.hidden').removeClass('hidden');
                });
            });

            if (activetab != '' && $(activetab + '-tab').length) {
              $(activetab + '-tab').addClass('nav-tab-active');
            }
            else {
              $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
            }
            $('.nav-tab-wrapper a').click(function (evt) {
              $('.nav-tab-wrapper a').removeClass('nav-tab-active');
              $(this).addClass('nav-tab-active').blur();
              var clicked_group = $(this).attr('href');
              if (typeof(localStorage) !== undefined) {
                localStorage.setItem("activetab", $(this).attr('href'));
              }
              $('.group').hide();
              $(clicked_group).fadeIn();
              evt.preventDefault();
            });
          });
        </script>
        <?php
    }
}
