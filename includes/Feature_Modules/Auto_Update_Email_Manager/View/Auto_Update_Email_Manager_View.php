<?php
/**
 * Auto Update Email Manager View
 *
 * @package Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager\View
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager\View;

use Intellitonic\Admin\Core\View\Abstract_View;
use Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager\Auto_Update_Email_Manager;

/**
 * View implementation for Auto Update Email Manager
 */
class Auto_Update_Email_Manager_View extends Abstract_View {
    /**
     * Module instance
     *
     * @var Auto_Update_Email_Manager
     */
    protected $module;

    /**
     * Constructor
     *
     * @param Auto_Update_Email_Manager $module Module instance.
     */
    public function __construct(Auto_Update_Email_Manager $module) {
        $this->module = $module;
    }

    /**
     * Register view assets
     *
     * @return void
     */
    public function register_assets(): void {
        wp_register_style(
            'intellitonic-auem',
            $this->get_assets_url('css/auto-update-email-manager.css'),
            [],
            INTELLITONIC_ADMIN_VERSION
        );

        wp_register_script(
            'intellitonic-auem',
            $this->get_assets_url('js/auto-update-email-manager.js'),
            ['jquery'],
            INTELLITONIC_ADMIN_VERSION,
            true
        );
    }

    /**
     * Enqueue view assets
     *
     * @return void
     */
    public function enqueue_assets(): void {
        $this->register_assets();
        wp_enqueue_style('intellitonic-auem');
        wp_enqueue_script('intellitonic-auem');

        wp_localize_script('intellitonic-auem', 'intellitonicAUEM', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('intellitonic_auem_nonce'),
            'settings' => $this->module->get_settings(),
        ]);
    }

    /**
     * Get assets URL
     *
     * @param string $path Optional path to append.
     * @return string Full assets URL.
     */
    protected function get_assets_url(string $path = ''): string {
        return INTELLITONIC_ADMIN_URL . 'assets/feature-modules/auto-update-email-manager/' . $path;
    }

    /**
     * Get view title
     *
     * @return string
     */
    public function get_title(): string {
        return $this->module->get_name();
    }

    /**
     * Get view description
     *
     * @return string
     */
    public function get_description(): string {
        return $this->module->get_description();
    }

    /**
     * Render the settings page
     *
     * @param array $data Optional data to pass to view.
     * @return void
     */
    public function render(array $data = []): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('intellitonic_admin_settings');
                do_settings_sections('intellitonic_admin_settings');
                submit_button(__('Save Settings', 'intellitonic-admin'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render a checkbox field
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_checkbox_field(array $args): void {
        $option_name = 'intellitonic_feature_auto_update_email_manager_settings';
        $label_for = $args['label_for'];
        $description = $args['description'];

        $options = get_option($option_name, []);
        $value = isset($options[$label_for]) ? $options[$label_for] : false;

        ?>
        <label>
            <input type="checkbox"
                   id="<?php echo esc_attr($label_for); ?>"
                   name="<?php echo esc_attr($option_name); ?>[<?php echo esc_attr($label_for); ?>]"
                   value="1"
                   <?php checked($value, true); ?>
            />
            <?php echo esc_html($description); ?>
        </label>
        <?php
    }

    /**
     * Get template path
     *
     * @param string $template Template name.
     * @return string Full template path.
     */
    protected function get_template_path(string $template): string {
        return plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'templates/auto-update-email-manager/' . $template;
    }
}
