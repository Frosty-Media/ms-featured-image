<?php

declare(strict_types=1);

namespace FrostyMedia\MSFeaturedImage\Admin;

use FrostyMedia\MSFeaturedImage\FeaturedImage;
use FrostyMedia\MSFeaturedImage\WpHooksInterface;
use function absint;
use function esc_url;
use function is_numeric;
use function sprintf;

/**
 * Class SettingsApi
 * @package FrostyMedia\MSFeaturedImage\Admin
 */
class SettingsAPI implements WpHooksInterface
{

    public const NONCE_KEY = '_msfi_nonce';

    /** @var string $settings_page_hook */
    protected string $settings_page_hook;

    /**
     * settings sections array
     * @var array $settings_sections
     */
    private array $settings_sections = [];

    /**
     * Settings fields array
     * @var array $settings_fields
     */
    private array $settings_fields = [];

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
    }

    /**
     * @param string $page_slug
     */
    public function setSettingsPageHook(string $page_slug): void
    {
        $this->settings_page_hook = sprintf('settings_page_%s', $page_slug);
    }

    /**
     * @return string
     */
    public function getSettingsPageHook(): string
    {
        return $this->settings_page_hook;
    }

    /**
     * Enqueue scripts and styles
     * @param string $hook
     */
    public function adminEnqueueScripts(string $hook): void
    {
        if ($this->getSettingsPageHook() !== $hook) {
            return;
        }

        wp_enqueue_media();
    }

    /**
     * Set settings sections
     * @param array $sections setting sections array
     */
    public function setSections(array $sections): void
    {
        $this->settings_sections = $sections;
    }

    /**
     * Add a single section
     * @param array $section
     */
    public function addSection(array $section): void
    {
        $this->settings_sections[] = $section;
    }

    /**
     * Set settings fields
     * @param array $fields settings fields array
     */
    public function setFields(array $fields): void
    {
        $this->settings_fields = $fields;
    }

    /**
     * @param string $section
     * @param string $field
     */
    public function addField(string $section, string $field): void
    {
        $defaults = [
                'name' => '',
                'label' => '',
                'desc' => '',
                'type' => 'text',
        ];

        $this->settings_fields[$section][] = wp_parse_args($field, $defaults);
    }

    /**
     * Initialize and registers the settings sections and fields to WordPress
     * Usually this should be called at `admin_init` hook.
     * This function gets the initiated settings sections and fields. Then
     * registers them to WordPress and ready for use.
     */
    public function adminInit(): void
    {
        // register settings sections
        foreach ($this->settings_sections as $section) {
            if (get_option($section['id']) === false) {
                add_option($section['id'], []);
            }

            add_settings_section($section['id'], $section['title'], '__return_false', $section['id']);
        }

        // register settings fields
        foreach ($this->settings_fields as $section => $field) {
            foreach ($field as $option) {
                $type = $option['type'] ?? 'text';
                $args = [
                        'id' => $option['name'],
                        'desc' => $option['desc'] ?? '',
                        'name' => $option['label'],
                        'section' => $section,
                        'size' => $option['size'] ?? null,
                        'options' => $option['options'] ?? '',
                        'std' => $option['default'] ?? '',
                        'sanitize_callback' => $option['sanitize_callback'] ?? '',
                ];

                add_settings_field(
                        $section . '[' . $option['name'] . ']',
                        $option['label'],
                        [$this, 'callback' . ucfirst($type),],
                        $section,
                        $section,
                        $args
                );
            }
        }

        // creates our settings in the options table
        foreach ($this->settings_sections as $section) {
            register_setting($section['id'], $section['id'], [$this, 'sanitizeOptions']);
        }
    }

    /**
     * Displays a text field for a settings field
     * @param array $args settings field args
     */
    public function callbackText(array $args): void
    {
        $value = $this->getOption($args['id'], $args['section'], $args['std']);
        $size = $args['size'] ?? 'regular';

        $html = '';

        if ($args['desc']) {
            $html .= sprintf('<span class="description">%s</span><br>', $args['desc']);
        }

        $html .= sprintf(
                '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s][url]" value="%4$s">',
                $size,
                $args['section'],
                $args['id'],
                $value['url'] ?? ''
        );
        $html .= sprintf(
                '<input type="hidden" name="%1$s[%2$s][id]" value="%3$s">',
                $args['section'],
                $args['id'],
                $value['id'] ?? ''
        );

        echo $html;
    }

    /**
     * Displays a file upload field for a settings field
     * @param array $args settings field args
     */
    public function callbackFile(array $args): void
    {
        $value = $this->getOption($args['id'], $args['section'], $args['std']);
        $id = $args['section'] . '[' . $args['id'] . ']';
        $desc = $args['desc'] ?? null;

        if (!is_null($desc)) {
            $args['desc'] = null;
        }

        $html = '<div class="alignone clear">';
        $html .= $this->getTheImage($value['id'] ?? null);
        $html .= '<div class="alignleft" style="margin-left: 25px">';

        ob_start();
        $this->callbackText($args);
        $html .= ob_get_clean();

        $html .= '<input type="button" class="button wpsf-browse" id="' . $id . '_button" value="Browse">';
        $html .= '<input type="button" class="button wpsf-clear" id="' . $id . '_button_clear" value="Clear">';

        if ($desc) {
            $html .= sprintf('<br><span class="description">%s</span>', $desc);
        }

        $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }

    /**
     * Sanitize callback for Settings API
     * @param array $options
     * @return array
     */
    public function sanitizeOptions(array $options): array
    {
        foreach ($options as $option_slug => $option_value) {
            foreach ($option_value as $name => $value) {
                if ($name === 'url') {
                    $options[$option_slug][$name] = esc_url($value);
                    continue;
                }
                $options[$option_slug][$name] = absint($value);
            }
        }

        return $options;
    }

    /**
     * Get the value of a settings field
     * @param string $option settings field name
     * @param string $section the section name this field belongs to
     * @param string $default default text if it's not found
     * @return string
     */
    public function getOption(string $option, string $section, string $default = ''): mixed
    {
        $options = get_site_option($section);

        return $options[$option] ?? $default;
    }

    /**
     * Show the section settings forms
     * This function displays every section in a different form
     */
    public function showForms(): void
    { ?>
        <h2><?php
            esc_html_e('Multisite Featured Image Settings', 'ms-featured-image'); ?></h2>
        <div class="metabox-holder">
            <div class="postbox">
                <?php
                $action = add_query_arg(
                        [
                                'page' => FeaturedImage::PLUGIN_SLUG,
                                'action' => FeaturedImage::PLUGIN_SLUG,
                                self::NONCE_KEY => wp_create_nonce(FeaturedImage::PLUGIN_SLUG),
                        ],
                        network_admin_url('settings.php')
                );
                foreach ($this->settings_sections as $form) { ?>
                    <div id="<?php
                    echo $form['id']; ?>" class="group inside">
                        <form method="post" action="<?php
                        echo esc_url($action); ?>">
                            <?php
                            settings_fields($form['id']); ?>
                            <?php
                            do_settings_sections($form['id']); ?>
                            <div style="padding-left: 10px">
                                <?php
                                submit_button(null, 'primary', FeaturedImage::SUBMIT); ?>
                            </div>
                        </form>
                    </div>
                    <?php
                } ?>
            </div>
        </div>
        <?php
    }

    /**
     * @param mixed $image_id
     * @return string
     */
    private function getTheImage(mixed $image_id): string
    {
        if (is_numeric($image_id) && $image_id > 0) {
            $html = '<div class="alignleft">' . wp_get_attachment_image($image_id, [50, 50]) . '</div>';
        } else {
            $html = '<div class="alignleft"><img src="https://placeholdit.com/50/dddddd/999999?text=FM" alt="Placeholder"></div>';
        }

        return $html;
    }
}
