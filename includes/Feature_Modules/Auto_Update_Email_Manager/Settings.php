<?php
/**
 * Auto Update Email Manager Settings
 *
 * @package Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager;

use Intellitonic\Admin\Feature_Modules\Abstract_Feature_Settings;

/**
 * Settings implementation for Auto Update Email Manager
 */
class Settings extends Abstract_Feature_Settings {
    /**
     * Default settings
     *
     * @var array
     */
    private const DEFAULT_SETTINGS = [
        'disable_core_success_emails' => 1,
        'disable_core_failure_emails' => 1,
        'disable_core_critical_emails' => 0,
        'disable_plugin_success_emails' => 1,
        'disable_plugin_failure_emails' => 1,
        'disable_plugin_mixed_emails' => 1,
        'disable_theme_success_emails' => 1,
        'disable_theme_failure_emails' => 1,
        'disable_theme_mixed_emails' => 1
    ];

    /**
     * Get all settings
     *
     * @return array
     */
    public function get_all(): array {
        $options = get_option($this->get_option_name());
        return is_array($options) ? $options : self::DEFAULT_SETTINGS;
    }

    /**
     * Get a specific setting
     *
     * @param string $key     Setting key.
     * @param mixed  $default Default value if setting not found.
     * @return mixed
     */
    public function get(string $key, $default = null) {
        $options = $this->get_all();
        return $options[$key] ?? $default;
    }

    /**
     * Update a specific setting
     *
     * @param string $key   Setting key.
     * @param mixed  $value Setting value.
     * @return bool Whether the setting was updated successfully.
     */
    public function update(string $key, $value): bool {
        $options = $this->get_all();
        $options[$key] = $value;
        return update_option($this->get_option_name(), $options);
    }

    /**
     * Register settings
     *
     * @return void
     */
    public function register(): void {
        register_setting(
            'intellitonic_auem_settings',
            $this->get_option_name(),
            [
                'type' => 'array',
                'description' => __('Auto Update Email Manager Settings', 'intellitonic-admin'),
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => self::DEFAULT_SETTINGS,
            ]
        );

        $this->add_settings_sections();
    }

    /**
     * Sanitize settings
     *
     * @param array $input Settings input.
     * @return array Sanitized settings.
     */
    protected function sanitize_settings(array $input): array {
        $output = [];
        foreach (self::DEFAULT_SETTINGS as $key => $default) {
            $output[$key] = isset($input[$key]) ? (bool) $input[$key] : $default;
        }
        return $output;
    }

    /**
     * Add settings sections and fields
     */
    private function add_settings_sections(): void {
        // Core updates section
        add_settings_section(
            'core_email_section',
            __('Core Update Emails', 'intellitonic-admin'),
            [$this, 'render_core_section_info'],
            'intellitonic-update-email-manager'
        );

        add_settings_field(
            'disable_core_success_emails',
            __('Success Emails', 'intellitonic-admin'),
            [$this, 'render_checkbox_field'],
            'intellitonic-update-email-manager',
            'core_email_section',
            ['field' => 'disable_core_success_emails']
        );

        add_settings_field(
            'disable_core_failure_emails',
            __('Failure Emails', 'intellitonic-admin'),
            [$this, 'render_checkbox_field'],
            'intellitonic-update-email-manager',
            'core_email_section',
            ['field' => 'disable_core_failure_emails']
        );

        add_settings_field(
            'disable_core_critical_emails',
            __('Critical Update Emails', 'intellitonic-admin'),
            [$this, 'render_checkbox_field'],
            'intellitonic-update-email-manager',
            'core_email_section',
            ['field' => 'disable_core_critical_emails']
        );

        // Plugin updates section
        add_settings_section(
            'plugin_email_section',
            __('Plugin Update Emails', 'intellitonic-admin'),
            [$this, 'render_plugin_section_info'],
            'intellitonic-update-email-manager'
        );

        // Theme updates section
        add_settings_section(
            'theme_email_section',
            __('Theme Update Emails', 'intellitonic-admin'),
            [$this, 'render_theme_section_info'],
            'intellitonic-update-email-manager'
        );
    }

    /**
     * Render section descriptions
     */
    public function render_core_section_info(): void {
        echo '<p>' . esc_html__('Control emails for WordPress core automatic updates.', 'intellitonic-admin') . '</p>';
    }

    public function render_plugin_section_info(): void {
        echo '<p>' . esc_html__('Control emails for plugin automatic updates.', 'intellitonic-admin') . '</p>';
    }

    public function render_theme_section_info(): void {
        echo '<p>' . esc_html__('Control emails for theme automatic updates.', 'intellitonic-admin') . '</p>';
    }

    /**
     * Render a checkbox field
     *
     * @param array $args Field arguments.
     */
    public function render_checkbox_field(array $args): void {
        $field = $args['field'];
        $value = $this->get($field, 1);
        ?>
        <input type="checkbox"
               id="<?php echo esc_attr($field); ?>"
               name="<?php echo esc_attr($this->get_option_name() . '[' . $field . ']'); ?>"
               value="1"
               <?php checked(1, $value); ?>>
        <label for="<?php echo esc_attr($field); ?>">
            <?php esc_html_e('Disable these emails', 'intellitonic-admin'); ?>
        </label>
        <?php
    }
}
