<?php

/**
 * Menu class
 *
 * @package Intellitonic\Admin\Core
 */

namespace Intellitonic\Admin\Core;

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

		add_menu_page(
			__('Intellitonic Admin', 'intellitonic-admin'),
			__('Intellitonic', 'intellitonic-admin'),
			'manage_options',
			self::PARENT_MENU_SLUG,
			[$this, 'render_main_page'],
			'dashicons-admin-generic',
			30
		);

		add_submenu_page(
			self::PARENT_MENU_SLUG,
			__('Features', 'intellitonic-admin'),
			__('Features', 'intellitonic-admin'),
			'manage_options',
			self::PARENT_MENU_SLUG . '-features',
			[$this, 'render_features_page']
		);
	}

	/**
	 * Render the main admin page
	 *
	 * @return void
	 */
	public function render_main_page(): void
	{
		// Verify capabilities before rendering
		if (!current_user_can('manage_options')) {
			wp_die(
				esc_html__('You do not have sufficient permissions to access this page.', 'intellitonic-admin'),
				403
			);
		}

		require_once INTELLITONIC_ADMIN_PATH . 'includes/admin/views/main-page.php';
	}

	/**
	 * Render the features page
	 *
	 * @return void
	 */
	public function render_features_page(): void
	{
		// Verify capabilities before rendering
		if (!current_user_can('manage_options')) {
			wp_die(
				esc_html__('You do not have sufficient permissions to access this page.', 'intellitonic-admin'),
				403
			);
		}

		require_once INTELLITONIC_ADMIN_PATH . 'includes/admin/views/features-page.php';
	}
}
