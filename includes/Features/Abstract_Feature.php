<?php

/**
 * Abstract Feature class
 *
 * @package Intellitonic\Admin\Features
 */

namespace Intellitonic\Admin\Features;

/**
 * Base class for all features
 */
abstract class Abstract_Feature
{
	/**
	 * Feature ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Feature name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Feature description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Feature dependencies
	 *
	 * @var string[]
	 */
	protected $dependencies = [];

	/**
	 * Constructor
	 *
	 * @param string $id          Feature ID.
	 * @param string $name        Feature name.
	 * @param string $description Feature description.
	 */
	public function __construct(string $id, string $name, string $description)
	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
	}

	/**
	 * Register the feature
	 *
	 * This method is called by the feature manager to register the feature.
	 * It handles the registration of hooks and initialization of the feature.
	 *
	 * @return void
	 */
	final public function register(): void
	{
		// Register core hooks
		add_action('init', [$this, 'maybe_initialize'], 5);

		// Allow features to register their own hooks
		$this->register_hooks();
	}

	/**
	 * Register feature-specific hooks
	 *
	 * Override this method in child classes to register any additional hooks needed.
	 * This is called during feature registration, before initialization.
	 *
	 * @return void
	 */
	protected function register_hooks(): void
	{
		// Default implementation does nothing
	}

	/**
	 * Initialize the feature if enabled
	 *
	 * @internal
	 * @return void
	 */
	final public function maybe_initialize(): void
	{
		if ($this->is_enabled() && $this->can_be_enabled()) {
			$this->init();
		}
	}

	/**
	 * Get feature ID
	 *
	 * @return string
	 */
	public function get_id(): string
	{
		return $this->id;
	}

	/**
	 * Get feature name
	 *
	 * @return string
	 */
	public function get_name(): string
	{
		return $this->name;
	}

	/**
	 * Get feature description
	 *
	 * @return string
	 */
	public function get_description(): string
	{
		return $this->description;
	}

	/**
	 * Get feature dependencies
	 *
	 * @return string[]
	 */
	public function get_dependencies(): array
	{
		return $this->dependencies;
	}

	/**
	 * Set feature dependencies
	 *
	 * @param string[] $dependencies Feature IDs this feature depends on.
	 * @return void
	 */
	protected function set_dependencies(array $dependencies): void
	{
		$this->dependencies = $dependencies;
	}

	/**
	 * Check if feature is enabled
	 *
	 * @return bool
	 */
	public function is_enabled(): bool
	{
		return (bool) get_option('intellitonic_feature_' . $this->id . '_enabled', false);
	}

	/**
	 * Check if feature can be enabled
	 *
	 * @return bool
	 */
	public function can_be_enabled(): bool
	{
		foreach ($this->dependencies as $dependency) {
			$option = get_option('intellitonic_feature_' . $dependency . '_enabled', false);
			if (!$option) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Initialize the feature
	 *
	 * This method should be implemented by child classes to set up their functionality.
	 * It is only called when the feature is enabled and all dependencies are met.
	 *
	 * @return void
	 */
	abstract public function init(): void;

	/**
	 * Get feature settings
	 *
	 * Override this method in child classes to provide feature-specific settings.
	 * Each setting should be an array with 'id', 'label', 'type', and optional 'description' and 'default'.
	 *
	 * @return array
	 */
	public function get_settings(): array
	{
		return [];
	}

	/**
	 * Validate feature settings
	 *
	 * Override this method in child classes to validate feature-specific settings.
	 * This method should sanitize and validate all settings values.
	 *
	 * @param array $settings Settings to validate.
	 * @return array
	 */
	public function validate_settings(array $settings): array
	{
		return $settings;
	}
}
