<?php
/**
 * Admin View
 *
 * @package Intellitonic\Admin\Core\View
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Core\View;

use Intellitonic\Admin\Core\Feature_Registry;

/**
 * Handles the display of the main admin landing page
 */
class Admin_View extends Abstract_View {
    /**
     * Feature registry instance
     *
     * @var Feature_Registry
     */
    private $feature_registry;

    /**
     * Constructor
     *
     * @param Feature_Registry $feature_registry Feature registry instance.
     */
    public function __construct(Feature_Registry $feature_registry) {
        $this->feature_registry = $feature_registry;
    }

    /**
     * Register admin assets
     *
     * @return void
     */
    public function register_assets(): void {
        wp_register_style(
            'intellitonic-admin-dashboard',
            $this->get_assets_url('css/admin-dashboard.css'),
            [],
            INTELLITONIC_ADMIN_VERSION
        );

        wp_register_script(
            'intellitonic-admin-dashboard',
            $this->get_assets_url('js/admin-dashboard.js'),
            ['jquery'],
            INTELLITONIC_ADMIN_VERSION,
            true
        );
    }

    /**
     * Enqueue admin assets
     *
     * @return void
     */
    public function enqueue_assets(): void {
        $this->register_assets();
        wp_enqueue_style('intellitonic-admin-dashboard');
        wp_enqueue_script('intellitonic-admin-dashboard');

        wp_localize_script('intellitonic-admin-dashboard', 'intellitonicAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('intellitonic_admin_nonce'),
        ]);
    }

    /**
     * Get assets URL
     *
     * @param string $path Optional path to append.
     * @return string Full assets URL.
     */
    protected function get_assets_url(string $path = ''): string {
        return INTELLITONIC_ADMIN_URL . 'assets/admin/' . $path;
    }

    /**
     * Get view title
     *
     * @return string
     */
    public function get_title(): string {
        return __('Intellitonic Admin Dashboard', 'intellitonic-admin');
    }

    /**
     * Get view description
     *
     * @return string
     */
    public function get_description(): string {
        return __('Manage and configure your WordPress admin features from one central location.', 'intellitonic-admin');
    }

    /**
     * Render the view
     *
     * @param array $data Optional data to pass to the view.
     * @return void
     */
    public function render(array $data = []): void {
        $this->verify_capabilities();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="intellitonic-admin-dashboard">
                <div class="intellitonic-admin-welcome">
                    <h2><?php esc_html_e('Welcome to Intellitonic Admin', 'intellitonic-admin'); ?></h2>
                    <p class="about-description">
                        <?php esc_html_e('Manage and configure your WordPress admin features from one central location.', 'intellitonic-admin'); ?>
                    </p>
                </div>

                <div class="intellitonic-admin-features">
                    <h3><?php esc_html_e('Available Features', 'intellitonic-admin'); ?></h3>
                    <div class="feature-grid">
                        <?php foreach ($this->feature_registry->get_features() as $feature): ?>
                            <div class="feature-card">
                                <h4><?php echo esc_html($feature->get_name()); ?></h4>
                                <p><?php echo esc_html($feature->get_description()); ?></p>
                                <div class="feature-status">
                                    <?php if ($feature->is_enabled()): ?>
                                        <span class="status-enabled"><?php esc_html_e('Enabled', 'intellitonic-admin'); ?></span>
                                    <?php else: ?>
                                        <span class="status-disabled"><?php esc_html_e('Disabled', 'intellitonic-admin'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="intellitonic-admin-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=intellitonic-admin-settings')); ?>" class="button button-primary">
                        <?php esc_html_e('Manage Features', 'intellitonic-admin'); ?>
                    </a>
                </div>
            </div>
        </div>

        <style>
            .intellitonic-admin-dashboard {
                max-width: 1200px;
                margin: 20px 0;
            }
            .intellitonic-admin-welcome {
                background: #fff;
                padding: 20px;
                margin-bottom: 20px;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .feature-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .feature-card {
                background: #fff;
                padding: 20px;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .feature-status {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #eee;
            }
            .status-enabled {
                color: #46b450;
                font-weight: 600;
            }
            .status-disabled {
                color: #dc3232;
                font-weight: 600;
            }
            .intellitonic-admin-actions {
                margin-top: 30px;
            }
        </style>
        <?php
    }

    /**
     * Get template path
     *
     * @param string $template Template name.
     * @return string Full template path.
     */
    protected function get_template_path(string $template): string {
        return plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'templates/' . $template;
    }
}
