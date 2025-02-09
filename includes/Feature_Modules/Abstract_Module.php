<?php

/**
 * Abstract Feature Module
 *
 * @package Intellitonic\Admin\Feature_Modules
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Feature_Modules;

use Intellitonic\Admin\Core\View\Abstract_View;

/**
 * Base class for all feature modules
 */
abstract class Abstract_Module
{
	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Module description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Module dependencies
	 *
	 * @var string[]
	 */
	protected $dependencies = [];

	/**
	 * Settings instance
	 *
	 * @var mixed|null
	 */
	protected $settings = null;

	/**
	 * View instance
	 *
	 * @var Abstract_View|null
	 */
	protected $view = null;

	/**
	 * Menu registration callback
	 *
	 * @var callable|null
	 */
	protected $menu_callback = null;

	/**
	 * Menu title
	 *
	 * @var string|null
	 */
	protected $menu_title = null;

	/**
	 * Menu page title
	 *
	 * @var string|null
	 */
	protected $page_title = null;

	/**
	 * Menu slug suffix
	 *
	 * @var string|null
	 */
	protected $menu_slug = null;

	/**
	 * Constructor
	 *
	 * @param string $id          Module ID.
	 * @param string $name        Module name.
	 * @param string $description Module description.
	 */
	public function __construct(string $id, string $name, string $description)
	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;

		// Register with WordPress hooks system
		add_action('intellitonic_register_features', [$this, 'register']);
	}

	/**
	 * Register the module
	 *
	 * This method is called via WordPress hooks to register the module.
	 * It handles the registration of hooks and initialization of the module.
	 *
	 * @return void
	 */
	final public function register(): void
	{
		// Let WordPress know about this feature
		do_action('intellitonic_feature_registered', $this);

		// Register core hooks
		add_action('init', [$this, 'maybe_initialize'], 5);

		// Register discovery hooks (always run)
		$this->register_discovery_hooks();

		// Register activation hooks (only when enabled)
		if ($this->is_enabled() && $this->can_be_enabled()) {
			$this->register_activation_hooks();
		}
	}

	/**
	 * Register hooks that should always run for feature discovery
	 * These run regardless of feature state
	 *
	 * @return void
	 */
	protected function register_discovery_hooks(): void
	{
		// Default implementation for discovery
		// Child classes can override but should call parent
	}

	/**
	 * Register hooks that should only run when feature is enabled
	 * These only run in enabled state
	 *
	 * @return void
	 */
	protected function register_activation_hooks(): void
	{
		// Register menu if configured
		if ($this->has_menu()) {
			add_action('intellitonic_register_feature_menus', [$this, 'register_menu']);
		}

		// Initialize the module
		add_action('init', [$this, 'maybe_initialize'], 5);
	}

	/**
	 * Initialize the module if enabled
	 *
	 * @internal
	 * @return void
	 */
	final public function maybe_initialize(): void
	{
		if ($this->is_enabled() && $this->can_be_enabled()) {
			// Initialize core components first
			$this->init();

			// Then initialize settings and view if they exist
			if ($this->settings !== null) {
				$this->init_settings();
			}
			if ($this->view !== null) {
				$this->init_view();
			}
		}
	}

	/**
	 * Initialize feature settings
	 * Only called when feature is enabled and settings exist
	 *
	 * @return void
	 */
	protected function init_settings(): void
	{
		// Register settings with WordPress Settings API
		register_setting(
			'intellitonic_admin_settings',
			'intellitonic_feature_' . $this->id . '_settings',
			[
				'type' => 'array',
				'description' => sprintf(
					/* translators: %s: feature name */
					__('Settings for %s feature', 'intellitonic-admin'),
					$this->name
				),
				'sanitize_callback' => [$this, 'validate_settings'],
				'default' => [],
				'show_in_rest' => false
			]
		);

		// Only register settings hooks if settings object exists and has register method
		if (method_exists($this->settings, 'register')) {
			add_action('admin_init', [$this->settings, 'register']);
		}
	}

	/**
	 * Initialize feature view
	 * Only called when feature is enabled
	 *
	 * @return void
	 */
	protected function init_view(): void
	{
		if ($this->view) {
			// Register view hooks if needed
		}
	}

	/**
	 * Get module ID
	 *
	 * @return string
	 */
	public function get_id(): string
	{
		return $this->id;
	}

	/**
	 * Get module name
	 *
	 * @return string
	 */
	public function get_name(): string
	{
		return $this->name;
	}

	/**
	 * Get module description
	 *
	 * @return string
	 */
	public function get_description(): string
	{
		return $this->description;
	}

	/**
	 * Get module dependencies
	 *
	 * @return string[]
	 */
	public function get_dependencies(): array
	{
		return $this->dependencies;
	}

	/**
	 * Set module dependencies
	 *
	 * @param string[] $dependencies Module IDs this module depends on.
	 * @return void
	 */
	protected function set_dependencies(array $dependencies): void
	{
		$this->dependencies = $dependencies;
	}

	/**
	 * Check if module is enabled
	 *
	 * @return bool
	 */
	public function is_enabled(): bool
	{
		return (bool) get_option('intellitonic_feature_' . $this->id . '_enabled', false);
	}

	/**
	 * Check if module can be enabled
	 *
	 * @return bool
	 */
	public function can_be_enabled(): bool
	{
		// Check plugin dependencies first
		foreach ($this->get_plugin_dependencies() as $plugin) {
			if (!is_plugin_active($plugin)) {
				return false;
			}
		}

		// Then check feature dependencies
		foreach ($this->dependencies as $dependency) {
			$option = get_option('intellitonic_feature_' . $dependency . '_enabled', false);
			if (!$option) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get plugin dependencies
	 *
	 * Override this in child classes to specify required plugins
	 *
	 * @return array Array of plugin paths (e.g. 'woocommerce/woocommerce.php')
	 */
	protected function get_plugin_dependencies(): array
	{
		return [];
	}

	/**
	 * Initialize the module
	 *
	 * This method should be implemented by child classes to set up their functionality.
	 * It is only called when the module is enabled and all dependencies are met.
	 *
	 * @return void
	 */
	abstract public function init(): void;

	/**
	 * Get module settings with type safety
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings(): array
	{
		return [];  // Base implementation returns empty array
	}

	/**
	 * Validate settings - Base implementation
	 *
	 * @param mixed $input Settings to validate
	 * @return array Validated settings
	 */
	public function validate_settings($input): array
	{
		return is_array($input) ? $input : [];
	}

	/**
	 * Set up feature menu
	 *
	 * Call this in your feature's constructor to register a menu item
	 *
	 * @param string      $menu_title    The menu item text
	 * @param string      $page_title    The page title
	 * @param string      $menu_slug     The menu slug suffix
	 * @param callable    $menu_callback The render callback (defaults to the view's render method)
	 * @return void
	 */
	protected function setup_menu(
		string $menu_title,
		string $page_title,
		string $menu_slug,
		?callable $menu_callback = null
	): void {
		$this->menu_title = $menu_title;
		$this->page_title = $page_title;
		$this->menu_slug = $menu_slug;
		$this->menu_callback = $menu_callback;

		// Register settings section and fields if this is a settings page
		add_action('admin_init', function() {
			add_settings_section(
				'intellitonic_feature_' . $this->id . '_section',
				$this->name,
				function() {
					echo '<p>' . esc_html($this->description) . '</p>';
				},
				'intellitonic_admin_settings'
			);
		});
	}

	/**
	 * Check if feature has menu configuration
	 *
	 * @return bool
	 */
	protected function has_menu(): bool
	{
		return $this->menu_title !== null && $this->menu_slug !== null;
	}

	/**
	 * Register feature menu item
	 *
	 * @param string $parent_slug The parent menu slug
	 * @return void
	 */
	public function register_menu(string $parent_slug): void
	{
		if (!$this->has_menu()) {
			return;
		}

		$callback = $this->menu_callback ?? [$this->view, 'render'];
		$page_hook = add_submenu_page(
			$parent_slug,
			$this->page_title ?? $this->menu_title,
			$this->menu_title,
			'manage_options',
			$parent_slug . '-' . $this->menu_slug,
			$callback
		);

		// Register settings on this page
		if ($page_hook && $this->settings) {
			add_action('load-' . $page_hook, function() {
				do_action('intellitonic_feature_settings_page_' . $this->id);
			});
		}
	}
}
