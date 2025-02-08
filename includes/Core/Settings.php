<?php

/**
 * Settings class
 *
 * @package Intellitonic\Admin\Core
 */

namespace Intellitonic\Admin\Core;

use Intellitonic\Admin\Features\Abstract_Feature;

/**
 * Handles WordPress Settings API integration
 */
class Settings
{
	/**
	 * Settings group name
	 */
	const SETTINGS_GROUP = 'intellitonic_admin_settings';

	/**
	 * Feature manager instance
	 *
	 * @var Feature_Manager
	 */
	private $feature_manager;

	/**
	 * Constructor
	 *
	 * @param Feature_Manager $feature_manager Feature manager instance.
	 */
	public function __construct(Feature_Manager $feature_manager)
	{
		$this->feature_manager = $feature_manager;
	}

	/**
	 * Initialize settings
	 *
	 * @return void
	 */
	public function init(): void
	{
		add_action('admin_init', [$this, 'register_settings']);
	}

	/**
	 * Register settings
	 *
	 * @return void
	 */
	public function register_settings(): void
	{
		register_setting(
			self::SETTINGS_GROUP,
			'intellitonic_admin_settings',
			[
				'type' => 'array',
				'description' => __('Intellitonic Admin Settings', 'intellitonic-admin'),
				'sanitize_callback' => [$this, 'sanitize_settings'],
				'default' => [],
			]
		);

		add_settings_section(
			'intellitonic_features_section',
			__('Feature Management', 'intellitonic-admin'),
			[$this, 'render_features_section'],
			self::SETTINGS_GROUP
		);

		foreach ($this->feature_manager->get_features() as $feature) {
			add_settings_field(
				'intellitonic_feature_' . $feature->get_id() . '_enabled',
				$feature->get_name(),
				[$this, 'render_feature_field'],
				self::SETTINGS_GROUP,
				'intellitonic_features_section',
				[
					'feature' => $feature,
					'label_for' => 'intellitonic_feature_' . $feature->get_id() . '_enabled',
				]
			);

			// Register feature-specific settings if they exist
			$settings = $feature->get_settings();
			if (!empty($settings)) {
				$section_id = 'intellitonic_feature_' . $feature->get_id() . '_settings';

				add_settings_section(
					$section_id,
					sprintf(
						/* translators: %s: feature name */
						__('%s Settings', 'intellitonic-admin'),
						$feature->get_name()
					),
					function () use ($feature) {
						$this->render_feature_settings_section($feature);
					},
					self::SETTINGS_GROUP
				);

				foreach ($settings as $setting) {
					add_settings_field(
						$setting['id'],
						$setting['label'],
						[$this, 'render_feature_setting_field'],
						self::SETTINGS_GROUP,
						$section_id,
						array_merge($setting, ['feature' => $feature])
					);
				}
			}
		}
	}

	/**
	 * Render features section
	 *
	 * @return void
	 */
	public function render_features_section(): void
	{
		echo '<div class="intellitonic-bulk-actions">';
		echo '<select name="bulk_action">';
		echo '<option value="">' . esc_html__('Bulk Actions', 'intellitonic-admin') . '</option>';
		echo '<option value="enable">' . esc_html__('Enable', 'intellitonic-admin') . '</option>';
		echo '<option value="disable">' . esc_html__('Disable', 'intellitonic-admin') . '</option>';
		echo '</select>';
		wp_nonce_field('intellitonic_bulk_action');
		submit_button(__('Apply', 'intellitonic-admin'), 'secondary', 'do_bulk_action', false);
		echo '</div>';

		echo '<p>' . esc_html__('Enable or disable Intellitonic Admin features. Some features may depend on others to function properly.', 'intellitonic-admin') . '</p>';
	}

	/**
	 * Render feature settings section
	 *
	 * @param Abstract_Feature $feature Feature instance.
	 * @return void
	 */
	public function render_feature_settings_section(Abstract_Feature $feature): void
	{
		if (!$feature->is_enabled()) {
			echo '<div class="notice notice-warning inline"><p>';
			printf(
				/* translators: %s: feature name */
				esc_html__('The %s feature is currently disabled. Enable it to configure these settings.', 'intellitonic-admin'),
				esc_html($feature->get_name())
			);
			echo '</p></div>';
		}
	}

	/**
	 * Render feature field
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_feature_field(array $args): void
	{
		$feature = $args['feature'];
		$option_name = 'intellitonic_feature_' . $feature->get_id() . '_enabled';
		$enabled = $this->feature_manager->get_feature_state($feature->get_id());
		$can_be_enabled = $feature->can_be_enabled();
		$dependencies = $feature->get_dependencies();

		echo '<div class="intellitonic-feature-toggle">';

		printf(
			'<label for="%1$s">
				<input type="checkbox" name="feature_ids[]" value="%2$s" class="intellitonic-feature-toggle__bulk" />
				<input type="checkbox" id="%1$s" name="%1$s" value="1" %3$s %4$s class="intellitonic-feature-toggle__input" />
				<span class="intellitonic-feature-toggle__description">%5$s</span>
			</label>',
			esc_attr($args['label_for']),
			esc_attr($feature->get_id()),
			checked($enabled, true, false),
			disabled(!$can_be_enabled, true, false),
			wp_kses_post($feature->get_description())
		);

		if (!empty($dependencies)) {
			echo '<div class="intellitonic-feature-toggle__dependencies">';
			echo '<p class="description">';
			esc_html_e('Dependencies:', 'intellitonic-admin');
			echo '</p>';
			echo '<ul>';
			foreach ($dependencies as $dependency_id) {
				$dependency = $this->feature_manager->get_feature($dependency_id);
				if ($dependency) {
					printf(
						'<li>%s%s</li>',
						esc_html($dependency->get_name()),
						$dependency->is_enabled() ? ' ✓' : ' ✗'
					);
				}
			}
			echo '</ul>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render feature setting field
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_feature_setting_field(array $args): void
	{
		$feature = $args['feature'];
		$setting_id = $args['id'];
		$type = $args['type'] ?? 'text';
		$value = get_option($setting_id, $args['default'] ?? '');
		$disabled = !$feature->is_enabled();

		switch ($type) {
			case 'checkbox':
				printf(
					'<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s %3$s />',
					esc_attr($setting_id),
					checked($value, true, false),
					disabled($disabled, true, false)
				);
				break;

			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%1$s" rows="5" cols="50" %2$s>%3$s</textarea>',
					esc_attr($setting_id),
					disabled($disabled, true, false),
					esc_textarea($value)
				);
				break;

			case 'select':
				echo '<select id="' . esc_attr($setting_id) . '" name="' . esc_attr($setting_id) . '"' . disabled($disabled, true, false) . '>';
				foreach ($args['options'] as $option_value => $option_label) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr($option_value),
						selected($value, $option_value, false),
						esc_html($option_label)
					);
				}
				echo '</select>';
				break;

			default:
				printf(
					'<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" class="regular-text" %4$s />',
					esc_attr($type),
					esc_attr($setting_id),
					esc_attr($value),
					disabled($disabled, true, false)
				);
				break;
		}

		if (!empty($args['description'])) {
			echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
		}
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input Settings input.
	 * @return array
	 */
	public function sanitize_settings(array $input): array
	{
		$output = [];
		$features = $this->feature_manager->get_features();

		foreach ($input as $key => $value) {
			if (strpos($key, 'intellitonic_feature_') === 0 && strpos($key, '_enabled') !== false) {
				$feature_id = str_replace(['intellitonic_feature_', '_enabled'], '', $key);
				$feature = $features[$feature_id] ?? null;

				if ($feature) {
					if ($value && !$feature->can_be_enabled()) {
						add_settings_error(
							'intellitonic_admin_settings',
							'feature_dependency_error',
							sprintf(
								/* translators: %s: feature name */
								__('Cannot enable "%s" because its dependencies are not met.', 'intellitonic-admin'),
								$feature->get_name()
							)
						);
						continue;
					}
					$output[$key] = (bool) $value;
				}
			} else {
				// Handle feature-specific settings
				foreach ($features as $feature) {
					$settings = $feature->get_settings();
					foreach ($settings as $setting) {
						if ($setting['id'] === $key) {
							$output[$key] = $feature->validate_settings([$key => $value])[$key];
							break 2;
						}
					}
				}
			}
		}

		return $output;
	}

	/**
	 * Handle bulk actions
	 *
	 * @return void
	 */
	public function handle_bulk_actions(): void
	{
		if (!isset($_POST['bulk_action']) || !isset($_POST['feature_ids'])) {
			return;
		}

		check_admin_referer('intellitonic_bulk_action');

		$action = sanitize_key($_POST['bulk_action']);
		$feature_ids = array_map('sanitize_key', $_POST['feature_ids']);

		switch ($action) {
			case 'enable':
				foreach ($feature_ids as $id) {
					$this->feature_manager->enable_feature($id);
				}
				break;
			case 'disable':
				foreach ($feature_ids as $id) {
					$this->feature_manager->disable_feature($id);
				}
				break;
		}
	}
}
