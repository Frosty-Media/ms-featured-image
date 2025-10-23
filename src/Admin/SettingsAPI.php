<?php

namespace FrostyMedia\MSFeaturedImage\Admin;

use FrostyMedia\MSFeaturedImage\Common;
use FrostyMedia\MSFeaturedImage\FeaturedImage;
use FrostyMedia\MSFeaturedImage\WpHooksInterface;

/**
 * Class SettingsApi
 *
 * @package FrostyMedia\MSFeaturedImage\Admin
 */
class SettingsApi implements WpHooksInterface {

    const NONCE_KEY = '_msfi_nonce';

    /** @var string $settings_page_hook */
    protected $settings_page_hook;

    /**
     * settings sections array
     *
     * @var array $settings_sections
     */
    private $settings_sections = [];

    /**
     * Settings fields array
     *
     * @var array $settings_fields
     */
    private $settings_fields = [];

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        add_action( 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ] );
    }

    /**
     * @param string $page_slug
     */
    public function setSettingsPageHook( string $page_slug ) {
        $this->settings_page_hook = sprintf( 'settings_page_%s', $page_slug );
    }

    /**
     * @return string
     */
    public function getSettingsPageHook(): string {
        return $this->settings_page_hook;
    }

    /**
     * Enqueue scripts and styles
     *
     * @param string $hook
     */
    public function adminEnqueueScripts( $hook ) {
        if ( $this->getSettingsPageHook() !== $hook ) {
            return;
        }

        wp_enqueue_media();
    }

    /**
     * Set settings sections
     *
     * @param array $sections setting sections array
     */
    public function setSections( array $sections ) {
        $this->settings_sections = $sections;
    }

    /**
     * Add a single section
     *
     * @param array $section
     */
    public function addSection( array $section ) {
        $this->settings_sections[] = $section;
    }

    /**
     * Set settings fields
     *
     * @param array $fields settings fields array
     */
    public function setFields( array $fields ) {
        $this->settings_fields = $fields;
    }

    /**
     * @param string $section
     * @param string $field
     */
    public function addField( string $section, string $field ) {
        $defaults = [
            'name' => '',
            'label' => '',
            'desc' => '',
            'type' => 'text',
        ];

        $this->settings_fields[ $section ][] = wp_parse_args( $field, $defaults );
    }

    /**
     * Initialize and registers the settings sections and fileds to WordPress
     *
     * Usually this should be called at `admin_init` hook.
     *
     * This function gets the initiated settings sections and fields. Then
     * registers them to WordPress and ready for use.
     */
    public function adminInit() {
        // register settings sections
        foreach ( $this->settings_sections as $section ) {
            if ( get_option( $section['id'] ) === false ) {
                add_option( $section['id'], [] );
            }

            add_settings_section( $section['id'], $section['title'], '__return_false', $section['id'] );
        }

        // register settings fields
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

                add_settings_field(
                    $section . '[' . $option['name'] . ']',
                    $option['label'],
                    [ $this, 'callback' . ucfirst( $type ), ],
                    $section,
                    $section,
                    $args
                );
            }
        }

        // creates our settings in the options table
        foreach ( $this->settings_sections as $section ) {
            register_setting( $section['id'], $section['id'], [ $this, 'sanitizeOptions' ] );
        }
    }

    /**
     * Displays a text field for a settings field
     *
     * @param array $args settings field args
     */
    public function callbackText( $args ) {
        $value = esc_attr( $this->getOption( $args['id'], $args['section'], $args['std'] ) );
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
    public function callbackFile( $args ) {
        $value = esc_attr( $this->getOption( $args['id'], $args['section'], $args['std'] ) );
        $id    = $args['section'] . '[' . $args['id'] . ']';
        $desc  = isset( $args['desc'] ) ? $args['desc'] : null;

        if ( ! is_null( $desc ) ) {
            $args['desc'] = null;
        }

        $html = '<div class="alignone clear">';
        $html .= $this->getTheImage( $value );
        $html .= '<div class="alignleft" style="margin-left: 25px">';

        ob_start();
        $this->callbackText( $args );
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
     * Sanitize callback for Settings API
     *
     * @param array $options
     *
     * @return array
     */
    public function sanitizeOptions( $options ) {
        foreach ( $options as $option_slug => $option_value ) {

            $sanitize_callback = $this->getSanitizeCallback( $option_slug );

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
    private function getSanitizeCallback( $slug = '' ) {
        if ( empty( $slug ) ) {
            return false;
        }

        // Iterate over registered fields and see if we can find proper callback
        foreach ( $this->settings_fields as $section => $options ) {
            foreach ( $options as $option ) {
                if ( $option['name'] !== $slug ) {
                    continue;
                }

                // Return the callback name
                return isset( $option['sanitize_callback'] ) &&
                       is_callable( $option['sanitize_callback'] ) ?
                    $option['sanitize_callback'] :
                    false;
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
    public function getOption( string $option, string $section, $default = '' ): string {
        $options = get_site_option( $section );

        if ( isset( $options[ $option ] ) ) {
            return $options[ $option ];
        }

        return $default;
    }

    /**
     * Show the section settings forms
     *
     * This function displays every sections in a different form
     */
    public function showForms() { ?>
        <h2><?php esc_html_e( 'Multisite Featured Image Settings', 'ms-featured-image' ); ?></h2>
        <div class="metabox-holder">
            <div class="postbox">
                <?php
                $action = add_query_arg(
                    [
                        'page' => FeaturedImage::PLUGIN_SLUG,
                        'action' => FeaturedImage::PLUGIN_SLUG,
                        self::NONCE_KEY => wp_create_nonce( FeaturedImage::PLUGIN_SLUG ),
                    ],
                    network_admin_url( 'settings.php' )
                );
                foreach ( $this->settings_sections as $form ) { ?>
                    <div id="<?php echo $form['id']; ?>" class="group inside">
                        <form method="post" action="<?php echo esc_url( $action ); ?>">
                            <?php settings_fields( $form['id'] ); ?>
                            <?php do_settings_sections( $form['id'] ); ?>
                            <div style="padding-left: 10px">
                                <?php submit_button( null, 'primary', FeaturedImage::PLUGIN_SLUG . '_submit' ); ?>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function getTheImage( $value ): string {
        $image_id = ! empty( $value ) ? Common::urlToAttachmentID( $value ) : '';

        if ( ! empty( $image_id ) ) {
            $html = '<div class="alignleft">' .
                    wp_get_attachment_image(
                        $image_id,
                        [ 50, 50, ]
                    ) . '</div>';
        } else {
            $html = '<div class="alignleft"><img src="//place-hold.it/50?text=FM"></div>';
        }

        return $html;
    }
}
