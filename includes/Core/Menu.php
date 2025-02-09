<?php

/**
 * Menu class
 *
 * @package Intellitonic\Admin\Core
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Core;

use Intellitonic\Admin\Core\View\Admin_View;
use Intellitonic\Admin\Core\View\Feature_Registry_View;
use Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager\View\Auto_Update_Email_Manager_View;

/**
 * Handles admin menu registration
 */
class Menu
{
	/**
	 * Parent menu slug
	 */
	const PARENT_MENU_SLUG = 'intellitonic-admin';

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
	public function __construct(Feature_Registry $feature_registry)
	{
		$this->feature_registry = $feature_registry;
	}

	/**
	 * Initialize the menu
	 *
	 * @return void
	 */
	public function init(): void
	{
		// Only register menus for administrators
		if (!current_user_can('manage_options')) {
			return;
		}

		add_action('admin_menu', [$this, 'register_menus']);
	}

	/**
	 * Register admin menus
	 *
	 * @return void
	 */
	public function register_menus(): void
	{
		// Double-check capabilities as this is a mu-plugin
		if (!current_user_can('manage_options')) {
			return;
		}

		// Add main Intellitonic menu
		add_menu_page(
			__('Intellitonic', 'intellitonic-admin'),
			__('Intellitonic', 'intellitonic-admin'),
			'manage_options',
			self::PARENT_MENU_SLUG,
			[$this, 'render_main_page'],
			'dashicons-admin-generic',
			30
		);

		// Add main menu item as first submenu
		add_submenu_page(
			self::PARENT_MENU_SLUG,
			__('Dashboard', 'intellitonic-admin'),
			__('Dashboard', 'intellitonic-admin'),
			'manage_options',
			self::PARENT_MENU_SLUG,
			[$this, 'render_main_page']
		);

		// Add Settings submenu
		add_submenu_page(
			self::PARENT_MENU_SLUG,
			__('Intellitonic Admin Settings', 'intellitonic-admin'),
			__('Settings', 'intellitonic-admin'),
			'manage_options',
			self::PARENT_MENU_SLUG . '-settings',
			[$this, 'render_features_page']
		);

		// Let enabled features register their menu items
		do_action('intellitonic_register_feature_menus', self::PARENT_MENU_SLUG);
	}

	/**
	 * Render the main admin page
	 */
	public function render_main_page(): void {
		$view = new Admin_View($this->feature_registry);
		$view->render();
	}

	/**
	 * Render the features page
	 */
	public function render_features_page(): void {
		$view = new Feature_Registry_View($this->feature_registry);
		$view->render();
	}

	/**
	 * Render the email settings page
	 */
	public function render_email_settings_page(): void {
		/** @var \Intellitonic\Admin\Feature_Modules\Auto_Update_Email_Manager\Auto_Update_Email_Manager $feature */
		$feature = $this->feature_registry->get_feature('auto_update_email_manager');
		if ($feature) {
			$view = new Auto_Update_Email_Manager_View($feature);
			$view->render();
		}
	}
}
