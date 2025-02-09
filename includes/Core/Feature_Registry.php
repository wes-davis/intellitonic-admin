<?php
/**
 * Feature Registry
 *
 * @package Intellitonic\Admin\Core
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Core;

use Intellitonic\Admin\Feature_Modules\Abstract_Module;

/**
 * Handles feature discovery and state management
 */
class Feature_Registry
{
	/**
	 * Registered features
	 *
	 * @var Abstract_Module[]
	 */
	private $features = [];

	/**
	 * Feature state cache
	 *
	 * @var array
	 */
	private $state_cache = [];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Listen for feature registrations
		add_action('intellitonic_feature_registered', [$this, 'add_feature']);

		// Discover features immediately
		$this->discover_features();

		// Check dependencies after all features are registered
		add_action('admin_init', [$this, 'check_dependencies']);
	}

	/**
	 * Discover features by triggering registration
	 *
	 * @internal
	 * @return void
	 */
	public function discover_features(): void
	{
		/**
		 * Trigger feature registration
		 *
		 * Features should hook into this action to register themselves
		 */
		do_action('intellitonic_register_features');
	}

	/**
	 * Add a discovered feature
	 *
	 * @internal
	 * @param Abstract_Module $feature Feature instance.
	 * @return void
	 */
	public function add_feature(Abstract_Module $feature): void
	{
		$this->features[$feature->get_id()] = $feature;
		$this->state_cache[$feature->get_id()] = null;
	}

	/**
	 * Get all registered features
	 *
	 * @return Abstract_Module[]
	 */
	public function get_features(): array
	{
		return $this->features;
	}

	/**
	 * Get a specific feature by ID
	 *
	 * @param string $id Feature ID.
	 * @return Abstract_Module|null
	 */
	public function get_feature(string $id): ?Abstract_Module
	{
		return $this->features[$id] ?? null;
	}

	/**
	 * Check feature dependencies and disable features with unmet dependencies
	 *
	 * @internal
	 * @return void
	 */
	public function check_dependencies(): void
	{
		foreach ($this->features as $feature) {
			if ($feature->is_enabled() && !$feature->can_be_enabled()) {
				$this->disable_feature($feature->get_id());
				add_settings_error(
					'intellitonic_admin_settings',
					'feature_dependency_error',
					sprintf(
						/* translators: %s: feature name */
						__('Feature "%s" has been disabled due to unmet dependencies.', 'intellitonic-admin'),
						$feature->get_name()
					)
				);
			}
		}
	}

	/**
	 * Enable a feature
	 *
	 * @param string $id Feature ID.
	 * @return bool
	 */
	public function enable_feature(string $id): bool
	{
		if (!isset($this->features[$id])) {
			return false;
		}

		$feature = $this->features[$id];

		if (!$feature->can_be_enabled()) {
			return false;
		}

		// Run pre-enable actions
		do_action('intellitonic_pre_enable_feature', $feature);
		do_action("intellitonic_pre_enable_feature_{$id}", $feature);

		$result = update_option('intellitonic_feature_' . $id . '_enabled', true);

		if ($result) {
			// Clear state cache
			$this->state_cache[$id] = null;

			// Run post-enable actions
			do_action('intellitonic_post_enable_feature', $feature);
			do_action("intellitonic_post_enable_feature_{$id}", $feature);

			// Add transient caching for performance
			wp_cache_set('intellitonic_feature_' . $id, true, '', HOUR_IN_SECONDS);
		}

		return $result;
	}

	/**
	 * Disable a feature
	 *
	 * @param string $id Feature ID.
	 * @return bool
	 */
	public function disable_feature(string $id): bool
	{
		if (!isset($this->features[$id])) {
			return false;
		}

		$feature = $this->features[$id];

		// Run pre-disable actions
		do_action('intellitonic_pre_disable_feature', $feature);
		do_action("intellitonic_pre_disable_feature_{$id}", $feature);

		$result = update_option('intellitonic_feature_' . $id . '_enabled', false);

		if ($result) {
			// Clear state cache
			$this->state_cache[$id] = null;

			// Run post-disable actions
			do_action('intellitonic_post_disable_feature', $feature);
			do_action("intellitonic_post_disable_feature_{$id}", $feature);

			// Check for dependent features that need to be disabled
			foreach ($this->features as $dependent_feature) {
				if (in_array($id, $dependent_feature->get_dependencies(), true)) {
					$this->disable_feature($dependent_feature->get_id());
				}
			}

			// Add transient cache invalidation
			wp_cache_delete('intellitonic_feature_' . $id);
		}

		return $result;
	}

	/**
	 * Get feature state from cache or database
	 *
	 * @param string $id Feature ID.
	 * @return bool
	 */
	public function get_feature_state(string $id): bool
	{
		if (!isset($this->features[$id])) {
			return false;
		}

		if (!isset($this->state_cache[$id])) {
			$this->state_cache[$id] = $this->features[$id]->is_enabled();
		}

		return $this->state_cache[$id];
	}

	/**
	 * Clear feature state cache
	 *
	 * @param string|null $id Optional feature ID to clear specific cache.
	 * @return void
	 */
	public function clear_state_cache(?string $id = null): void
	{
		if ($id === null) {
			$this->state_cache = [];
		} else {
			$this->state_cache[$id] = null;
		}
	}

	/**
	 * Add method for bulk feature operations
	 *
	 * @param array $feature_states Array of feature IDs and their enabled states.
	 * @return array Array of results for each feature operation.
	 */
	public function bulk_update_features(array $feature_states): array
	{
		$results = [];
		foreach ($feature_states as $id => $enabled) {
			$results[$id] = $enabled ?
				$this->enable_feature($id) :
				$this->disable_feature($id);
		}
		return $results;
	}
}
