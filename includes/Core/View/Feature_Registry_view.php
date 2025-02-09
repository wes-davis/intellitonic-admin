<?php
/**
 * Feature Registry View
 *
 * @package Intellitonic\Admin\Core\View
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Core\View;

use Intellitonic\Admin\Core\Feature_Registry;
use Intellitonic\Admin\Core\Feature_Settings;

/**
 * Handles the display of feature registry settings
 */
class Feature_Registry_View extends Abstract_View {
    /**
     * Feature registry instance
     *
     * @var Feature_Registry
     */
    private $feature_registry;

    /**
     * Feature settings instance
     *
     * @var Feature_Settings
     */
    private $feature_settings;

    /**
     * Constructor
     *
     * @param Feature_Registry $feature_registry Feature registry instance.
     */
    public function __construct(Feature_Registry $feature_registry) {
        $this->feature_registry = $feature_registry;
        $this->feature_settings = new Feature_Settings($feature_registry);
    }

    /**
     * Register feature registry assets
     *
     * @return void
     */
    public function register_assets(): void {
        wp_register_style(
            'intellitonic-feature-registry',
            $this->get_assets_url('css/feature-registry.css'),
            [],
            INTELLITONIC_ADMIN_VERSION
        );

        wp_register_script(
            'intellitonic-feature-registry',
            $this->get_assets_url('js/feature-registry.js'),
            ['jquery'],
            INTELLITONIC_ADMIN_VERSION,
            true
        );
    }

    /**
     * Enqueue feature registry assets
     *
     * @return void
     */
    public function enqueue_assets(): void {
        $this->register_assets();
        wp_enqueue_style('intellitonic-feature-registry');
        wp_enqueue_script('intellitonic-feature-registry');

        wp_localize_script('intellitonic-feature-registry', 'intellitonicFeatures', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('intellitonic_feature_nonce'),
            'features' => array_map(function($feature) {
                return [
                    'id' => $feature->get_id(),
                    'name' => $feature->get_name(),
                    'description' => $feature->get_description(),
                    'enabled' => $feature->is_enabled(),
                    'canBeEnabled' => $feature->can_be_enabled(),
                    'dependencies' => $feature->get_dependencies(),
                ];
            }, $this->feature_registry->get_features()),
        ]);
    }

    /**
     * Get assets URL
     *
     * @param string $path Optional path to append.
     * @return string Full assets URL.
     */
    protected function get_assets_url(string $path = ''): string {
        return INTELLITONIC_ADMIN_URL . 'assets/feature-registry/' . $path;
    }

    /**
     * Get view title
     *
     * @return string
     */
    public function get_title(): string {
        return __('Feature Settings', 'intellitonic-admin');
    }

    /**
     * Get view description
     *
     * @return string
     */
    public function get_description(): string {
        return __('Enable or disable Intellitonic Admin features and manage their settings.', 'intellitonic-admin');
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
            <?php settings_errors(); ?>

            <div class="intellitonic-feature-settings">
                <form method="post" action="options.php">
                    <?php
                    settings_fields(Feature_Settings::SETTINGS_GROUP);
                    ?>

                    <div class="feature-toggles">
                        <?php foreach ($this->feature_registry->get_features() as $feature): ?>
                            <div class="feature-toggle-card">
                                <div class="feature-toggle-header">
                                    <h3><?php echo esc_html($feature->get_name()); ?></h3>
                                    <label class="switch">
                                        <input type="checkbox"
                                               name="intellitonic_feature_<?php echo esc_attr($feature->get_id()); ?>_enabled"
                                               value="1"
                                               <?php checked(true, $feature->is_enabled()); ?>
                                               <?php disabled(false, $feature->can_be_enabled()); ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <p class="feature-description">
                                    <?php echo esc_html($feature->get_description()); ?>
                                </p>

                                <?php if (!empty($feature->get_dependencies())): ?>
                                    <div class="feature-dependencies">
                                        <p class="description">
                                            <?php esc_html_e('Required Features:', 'intellitonic-admin'); ?>
                                            <?php
                                            $deps = array_map(function($dep_id) {
                                                $dep = $this->feature_registry->get_feature($dep_id);
                                                return $dep ? $dep->get_name() : $dep_id;
                                            }, $feature->get_dependencies());
                                            echo esc_html(implode(', ', $deps));
                                            ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
        </div>

        <style>
            .intellitonic-feature-settings {
                max-width: 1200px;
                margin: 20px 0;
            }
            .feature-toggles {
                display: grid;
                gap: 20px;
                margin: 20px 0;
            }
            .feature-toggle-card {
                background: #fff;
                padding: 20px;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .feature-toggle-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            .feature-toggle-header h3 {
                margin: 0;
                padding: 0;
            }
            .feature-description {
                margin: 10px 0;
                color: #666;
            }
            .feature-dependencies {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #eee;
            }
            /* Toggle Switch Styles */
            .switch {
                position: relative;
                display: inline-block;
                width: 50px;
                height: 24px;
            }
            .switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 24px;
            }
            .slider:before {
                position: absolute;
                content: "";
                height: 16px;
                width: 16px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
            input:checked + .slider {
                background-color: #2271b1;
            }
            input:disabled + .slider {
                opacity: 0.5;
                cursor: not-allowed;
            }
            input:checked + .slider:before {
                transform: translateX(26px);
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
