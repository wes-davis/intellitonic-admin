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
use Intellitonic\Admin\Core\Feature_Manager;
use Intellitonic\Admin\Core\Menu;
use Intellitonic\Admin\Core\Settings;

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

// Autoloader
require_once INTELLITONIC_ADMIN_PATH . 'vendor/autoload.php';

/**
 * Initialize the plugin
 *
 * Sets up the dependency injection container and initializes core components.
 *
 * @return void
 */
function init(): void {
	// Initialize core components with proper dependency injection
	$feature_manager = new Feature_Manager();
	$settings = new Settings($feature_manager);
	$menu = new Menu($feature_manager);
	$admin = new Admin($feature_manager, $settings, $menu);

	// Initialize the admin interface
	$admin->init();

	// Allow other components to hook into our plugin
	do_action('intellitonic_admin_init', $feature_manager, $settings, $menu, $admin);
}

// Initialize the plugin after all plugins are loaded
add_action('plugins_loaded', __NAMESPACE__ . '\init');
