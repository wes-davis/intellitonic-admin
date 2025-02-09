<?php
/**
 * Email Controls Trait
 *
 * Provides common functionality for managing WordPress email notifications.
 * Implements filters for core, plugin, and theme update emails.
 *
 * @package Intellitonic\Admin\Feature_Modules\Traits
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Feature_Modules\Traits;

/**
 * Email control functionality for features
 *
 * @since 1.0.0
 */
trait Email_Controls {
    /**
     * Set up email control hooks
     *
     * Registers all necessary filters for controlling update notification emails:
     * - auto_core_update_send_email: Core updates (priority: 10)
     * - auto_plugin_update_send_email: Plugin updates (priority: 10)
     * - auto_theme_update_send_email: Theme updates (priority: 10)
     *
     * @since 1.0.0
     * @return void
     */
    protected function setup_email_hooks(): void {
        // Core update emails
        add_filter(
            'auto_core_update_send_email',
            [$this, 'control_core_emails'],
            10,
            4
        );

        // Plugin update emails
        add_filter(
            'auto_plugin_update_send_email',
            [$this, 'control_plugin_emails'],
            10,
            2
        );

        // Theme update emails
        add_filter(
            'auto_theme_update_send_email',
            [$this, 'control_theme_emails'],
            10,
            2
        );
    }

    /**
     * Remove email control hooks
     *
     * Cleanup method to remove all email control filters when feature is disabled.
     *
     * @since 1.0.0
     * @return void
     */
    protected function remove_email_hooks(): void {
        remove_filter('auto_core_update_send_email', [$this, 'control_core_emails']);
        remove_filter('auto_plugin_update_send_email', [$this, 'control_plugin_emails']);
        remove_filter('auto_theme_update_send_email', [$this, 'control_theme_emails']);
    }

    /**
     * Control core update emails
     *
     * @since 1.0.0
     * @param bool   $send        Whether to send the email.
     * @param string $type        The type of email to send.
     * @param object $core_update The update offer that was attempted.
     * @param mixed  $result      The update result.
     * @return bool Whether to send the email.
     */
    abstract public function control_core_emails(bool $send, string $type, object $core_update, mixed $result): bool;

    /**
     * Control plugin update emails
     *
     * @since 1.0.0
     * @param bool  $send           Whether to send the email.
     * @param array $update_results The results of all attempted updates.
     * @return bool Whether to send the email.
     */
    abstract public function control_plugin_emails(bool $send, array $update_results): bool;

    /**
     * Control theme update emails
     *
     * @since 1.0.0
     * @param bool  $send           Whether to send the email.
     * @param array $update_results The results of all attempted updates.
     * @return bool Whether to send the email.
     */
    abstract public function control_theme_emails(bool $send, array $update_results): bool;
}
