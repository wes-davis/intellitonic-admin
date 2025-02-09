<?php
/**
 * Abstract Feature Settings
 *
 * @package Intellitonic\Admin\Feature_Modules
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Feature_Modules;

/**
 * Base class for all feature settings
 */
abstract class Abstract_Feature_Settings {
	/**
	 * Parent feature module
	 *
	 * @var Abstract_Module
	 */
	protected $feature;

	/**
	 * Constructor
	 *
	 * @param Abstract_Module $feature Parent feature module.
	 */
	public function __construct(Abstract_Module $feature) {
		$this->feature = $feature;
	}

	/**
	 * Register settings with WordPress
	 *
	 * @return void
	 */
	abstract public function register(): void;

	/**
	 * Get all settings
	 *
	 * @return array
	 */
	abstract public function get_all(): array;

	/**
	 * Get a specific setting
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value if setting not found.
	 * @return mixed
	 */
	abstract public function get(string $key, $default = null);

	/**
	 * Update a specific setting
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool Whether the setting was updated successfully.
	 */
	abstract public function update(string $key, $value): bool;

	/**
	 * Get the option name for this feature's settings
	 *
	 * @return string
	 */
	protected function get_option_name(): string {
		return 'intellitonic_' . $this->feature->get_id() . '_settings';
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input Settings input.
	 * @return array Sanitized settings.
	 */
	abstract protected function sanitize_settings(array $input): array;
}
