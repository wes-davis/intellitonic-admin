<?php
/**
 * Auto Update Email Manager Module
 *
 * @package Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager;

use Intellitonic\Admin\Feature_Modules\Abstract_Module;
use Intellitonic\Admin\Feature_Modules\Traits\Email_Controls;
use Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager\View\Auto_Update_Email_Manager_View;
use Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager\Settings;
use Intellitonic\Admin\Core\Feature_Registry;

/**
 * Controls which automatic update notification emails WordPress sends
 */
class Auto_Update_Email_Manager extends Abstract_Module {
    use Email_Controls;

    /**
     * Settings instance
     *
     * @var Settings
     */
    protected $settings;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'auto_update_email_manager',
            __('Auto Update Email Manager', 'intellitonic-admin'),
            __('Controls which automatic update notification emails WordPress sends', 'intellitonic-admin')
        );

        // Set up menu configuration
        $this->setup_menu(
            __('Email Settings', 'intellitonic-admin'),
            __('Email Settings', 'intellitonic-admin'),
            'email-settings'
        );
    }

    /**
     * Initialize the module
     * Only called when enabled and dependencies are met
     *
     * @return void
     */
    public function init(): void {
        // Initialize settings with this module instance
        $this->settings = new Settings($this);
        $this->view = new Auto_Update_Email_Manager_View($this);
    }

    /**
     * Register hooks that should always run for feature discovery
     * These run regardless of feature state
     *
     * @return void
     */
    protected function register_discovery_hooks(): void {
        parent::register_discovery_hooks();

        // Register feature toggle
        register_setting(
            'intellitonic_admin_settings',
            'intellitonic_feature_' . $this->get_id() . '_enabled',
            [
                'type' => 'boolean',
                'description' => __('Enable Auto Update Email Manager', 'intellitonic-admin'),
                'default' => false,
                'sanitize_callback' => 'rest_sanitize_boolean'
            ]
        );
    }

    /**
     * Register hooks that should only run when feature is enabled
     * These only run in enabled state
     *
     * @return void
     */
    protected function register_activation_hooks(): void {
        parent::register_activation_hooks();

        // Register email control hooks
        add_filter('auto_plugin_update_send_email', [$this, 'control_plugin_emails'], 10, 2);
        add_filter('auto_theme_update_send_email', [$this, 'control_theme_emails'], 10, 2);
    }

    /**
     * Register feature hooks
     *
     * @param Feature_Registry $registry The feature registry instance
     * @return void
     */
    protected function register_hooks(Feature_Registry $registry): void {
        // Register feature toggle
        register_setting(
            'intellitonic_admin_settings',
            'intellitonic_feature_' . $this->get_id() . '_enabled',
            [
                'type' => 'boolean',
                'description' => __('Enable Auto Update Email Manager', 'intellitonic-admin'),
                'default' => false,
                'sanitize_callback' => 'rest_sanitize_boolean'
            ]
        );

        // Only register settings and menu when enabled
        if ($this->is_enabled()) {
            $this->register_settings($registry);
            add_action('intellitonic_register_feature_menus', [$this, 'register_menu']);
        }
    }

    /**
     * Register feature settings
     *
     * @param Feature_Registry $registry The feature registry instance
     * @return void
     */
    protected function register_settings(Feature_Registry $registry): void {
        // Register settings
        register_setting(
            'intellitonic_admin_settings',
            'intellitonic_feature_' . $this->get_id() . '_settings',
            [
                'type' => 'array',
                'description' => __('Auto Update Email Manager Settings', 'intellitonic-admin'),
                'sanitize_callback' => [$this, 'validate_settings'],
                'default' => [
                    'disable_core_success_emails' => true,
                    'disable_core_failure_emails' => true,
                    'disable_core_critical_emails' => false,
                    'disable_plugin_success_emails' => true,
                    'disable_plugin_failure_emails' => true,
                    'disable_plugin_mixed_emails' => true,
                    'disable_theme_success_emails' => true,
                    'disable_theme_failure_emails' => true,
                    'disable_theme_mixed_emails' => true
                ]
            ]
        );

        // Register settings sections and fields
        add_action('admin_init', [$this, 'register_settings_fields']);
    }

    /**
     * Handle feature being toggled
     *
     * @param array $old_value Old option value
     * @param array $new_value New option value
     * @return void
     */
    public function handle_feature_toggle($old_value, $new_value): void {
        $was_enabled = isset($old_value['auto_update_email_manager']) && $old_value['auto_update_email_manager'];
        $is_enabled = isset($new_value['auto_update_email_manager']) && $new_value['auto_update_email_manager'];

        if ($was_enabled === $is_enabled) {
            return;
        }

        if ($is_enabled) {
            $this->register_activation_hooks();
        }
    }

    /**
     * Validate settings
     *
     * @param mixed $input Settings to validate
     * @return array Validated settings array
     */
    public function validate_settings($input): array {
        // If input is null or not an array, return empty array as default
        if (!is_array($input)) {
            return [];
        }

        $output = [];
        $valid_keys = [
            'disable_core_success_emails',
            'disable_core_failure_emails',
            'disable_core_critical_emails',
            'disable_plugin_success_emails',
            'disable_plugin_failure_emails',
            'disable_plugin_mixed_emails',
            'disable_theme_success_emails',
            'disable_theme_failure_emails',
            'disable_theme_mixed_emails'
        ];

        foreach ($valid_keys as $key) {
            $output[$key] = isset($input[$key]) ? (bool) $input[$key] : false;
        }

        return $output;
    }

    /**
     * Control core update emails
     *
     * @param bool   $send        Whether to send the email.
     * @param string $type        The type of email to send.
     * @param object $core_update The update offer that was attempted.
     * @param mixed  $result      The update result.
     * @return bool Whether to send the email
     */
    public function control_core_emails(bool $send, string $type, object $core_update, mixed $result): bool {
        $settings = get_option('intellitonic_feature_auto_update_email_manager_settings', []);

        switch ($type) {
            case 'success':
                return empty($settings['disable_core_success_emails']);
            case 'fail':
                return empty($settings['disable_core_failure_emails']);
            case 'critical':
                return empty($settings['disable_core_critical_emails']);
            default:
                return $send;
        }
    }

    /**
     * Control plugin update emails
     *
     * @param bool  $send           Whether to send the email.
     * @param array $update_results The results of all attempted updates.
     * @return bool Whether to send the email
     */
    public function control_plugin_emails(bool $send, array $update_results): bool {
        $settings = get_option('intellitonic_feature_auto_update_email_manager_settings', []);

        $successful_updates = wp_list_filter($update_results, ['result' => true]);
        $failed_updates = wp_list_filter($update_results, ['result' => false]);

        if (empty($failed_updates)) {
            return empty($settings['disable_plugin_success_emails']);
        } elseif (empty($successful_updates)) {
            return empty($settings['disable_plugin_failure_emails']);
        } else {
            return empty($settings['disable_plugin_mixed_emails']);
        }
    }

    /**
     * Control theme update emails
     *
     * @param bool  $send           Whether to send the email.
     * @param array $update_results The results of all attempted updates.
     * @return bool Whether to send the email
     */
    public function control_theme_emails(bool $send, array $update_results): bool {
        $settings = get_option('intellitonic_feature_auto_update_email_manager_settings', []);

        $successful_updates = wp_list_filter($update_results, ['result' => true]);
        $failed_updates = wp_list_filter($update_results, ['result' => false]);

        if (empty($failed_updates)) {
            return empty($settings['disable_theme_success_emails']);
        } elseif (empty($successful_updates)) {
            return empty($settings['disable_theme_failure_emails']);
        } else {
            return empty($settings['disable_theme_mixed_emails']);
        }
    }
}

// Self-instantiate the module
if (!defined('DOING_AJAX') && !defined('DOING_CRON')) {
    new Auto_Update_Email_Manager();
}
