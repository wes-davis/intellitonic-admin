<?php
/**
 * Abstract View Base
 *
 * Base class for all views in the plugin, serving both core and feature components.
 * Enforces consistent rendering, asset management, and template handling.
 *
 * @package Intellitonic\Admin\Core\View
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Core\View;

/**
 * Base class for all views
 */
abstract class Abstract_View {
    /**
     * Render the view
     *
     * @param array $data Optional data to pass to view.
     * @return void
     */
    abstract public function render(array $data = []): void;

    /**
     * Register assets
     *
     * @return void
     */
    abstract public function register_assets(): void;

    /**
     * Enqueue assets
     *
     * @return void
     */
    abstract public function enqueue_assets(): void;

    /**
     * Get template path
     *
     * @param string $template Template name.
     * @return string Full template path.
     */
    abstract protected function get_template_path(string $template): string;

    /**
     * Get assets URL
     *
     * @param string $path Optional path to append.
     * @return string Full assets URL.
     */
    abstract protected function get_assets_url(string $path = ''): string;

    /**
     * Get view title
     *
     * @return string
     */
    abstract public function get_title(): string;

    /**
     * Get view description
     *
     * @return string
     */
    abstract public function get_description(): string;

    /**
     * Verify user capabilities
     *
     * @return void
     */
    protected function verify_capabilities(): void {
        if (!current_user_can('manage_options')) {
            wp_die(
                esc_html__('You do not have sufficient permissions to access this page.', 'intellitonic-admin'),
                403
            );
        }
    }

    /**
     * Load template with data
     *
     * @param string $template Template name.
     * @param array  $data     Data to pass to template.
     * @return void
     */
    protected function load_template(string $template, array $data = []): void {
        if (!empty($data)) {
            extract($data);
        }
        require $this->get_template_path($template);
    }

    /**
     * Register and enqueue a CSS file
     *
     * @param string $handle    Script handle.
     * @param string $path      Path to CSS file.
     * @param array  $deps      Dependencies.
     * @param string $version   Version string.
     * @param string $media     Media type.
     * @return void
     */
    protected function enqueue_style(
        string $handle,
        string $path,
        array $deps = [],
        ?string $version = null,
        string $media = 'all'
    ): void {
        wp_enqueue_style(
            $handle,
            $this->get_assets_url($path),
            $deps,
            $version ?? INTELLITONIC_ADMIN_VERSION,
            $media
        );
    }

    /**
     * Register and enqueue a JavaScript file
     *
     * @param string $handle    Script handle.
     * @param string $path      Path to JS file.
     * @param array  $deps      Dependencies.
     * @param string $version   Version string.
     * @param bool   $in_footer Whether to enqueue in footer.
     * @return void
     */
    protected function enqueue_script(
        string $handle,
        string $path,
        array $deps = [],
        ?string $version = null,
        bool $in_footer = true
    ): void {
        wp_enqueue_script(
            $handle,
            $this->get_assets_url($path),
            $deps,
            $version ?? INTELLITONIC_ADMIN_VERSION,
            $in_footer
        );
    }
}
