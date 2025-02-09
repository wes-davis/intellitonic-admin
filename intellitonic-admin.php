<?php
/**
 * Plugin Name: Intellitonic Admin
 * Plugin URI: https://intellitonic.com
 * Description: A modular admin plugin with toggleable features for Intellitonic.
 * Version: 1.0.0
 * Author: Intellitonic
 * Author URI: https://intellitonic.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: intellitonic-admin
 * Domain Path: /languages
 *
 * @package Intellitonic\Admin
 */

namespace Intellitonic\Admin;

use Intellitonic\Admin\Core\Admin;
use Intellitonic\Admin\Core\Feature_Registry;
use Intellitonic\Admin\Core\Feature_Settings;
use Intellitonic\Admin\Core\Menu;

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Plugin version
define('INTELLITONIC_ADMIN_VERSION', '1.0.0');

// Plugin directory path
define('INTELLITONIC_ADMIN_PATH', plugin_dir_path(__FILE__));

// Plugin directory URL
define('INTELLITONIC_ADMIN_URL', plugin_dir_url(__FILE__));

/**
 * Register activation hook
 */
register_activation_hook(__FILE__, function() {
	// Activation tasks
});

/**
 * Register deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
	// Deactivation tasks
});

/**
 * Load feature modules
 *
 * @return void
 */
function load_feature_modules(): void {
	$feature_modules_dir = __DIR__ . '/includes/Feature_Modules/';
	$feature_dirs = array_filter(glob($feature_modules_dir . '*'), 'is_dir');

	foreach ($feature_dirs as $dir) {
		$module_file = $dir . '/' . basename($dir) . '.php';
		if (file_exists($module_file)) {
			require_once $module_file;
		}
	}
}

/**
 * Initialize the plugin core components and features.
 *
 * Core components are initialized at priority 5 to ensure they're ready
 * before features start registering themselves at default priority (10).
 *
 * @throws \RuntimeException If core components fail to initialize.
 * @return void
 */
function init(): void {
	try {
		// Load feature modules first
		load_feature_modules();

		// Initialize core components with proper type declarations
		$feature_registry = new Feature_Registry();
		if (!$feature_registry instanceof Feature_Registry) {
			throw new \RuntimeException('Failed to initialize Feature Registry');
		}

		$feature_settings = new Feature_Settings($feature_registry);
		if (!$feature_settings instanceof Feature_Settings) {
			throw new \RuntimeException('Failed to initialize Feature Settings');
		}

		$menu = new Menu($feature_registry);
		if (!$menu instanceof Menu) {
			throw new \RuntimeException('Failed to initialize Menu');
		}

		$admin = new Admin($feature_registry, $feature_settings, $menu);
		if (!$admin instanceof Admin) {
			throw new \RuntimeException('Failed to initialize Admin');
		}

		// Initialize the admin interface
		$admin->init();

		// Allow other components to hook into our plugin
		do_action('intellitonic_admin_init', $feature_registry, $feature_settings, $menu, $admin);

	} catch (\Exception $e) {
		// Log error but don't display notice
		error_log('Intellitonic Admin initialization failed: ' . $e->getMessage());
		return;
	}
}

// Initialize core components early
add_action('plugins_loaded', __NAMESPACE__ . '\init', 5);

/**
 * PSR-4 Autoloader
 *
 * Automatically loads feature modules and other classes following PSR-4 standard.
 * Feature modules will self-instantiate and register with the Feature Registry
 * through WordPress hooks.
 */
spl_autoload_register(function ($class) {
	// Base namespace and directory for PSR-4
	$namespace = 'Intellitonic\\Admin\\';
	$base_dir = __DIR__ . '/includes/';

	// Check if class uses our namespace
	$len = strlen($namespace);
	if (strncmp($namespace, $class, $len) !== 0) {
		return;
	}

	// Get relative class path
	$relative_class = substr($class, $len);
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	if (file_exists($file)) {
		require $file;
	}
});
