<?php

/**
 * Admin class
 *
 * @package Intellitonic\Admin\Core
 */

namespace Intellitonic\Admin\Core;

/**
 * Main admin interface class
 */
class Admin
{
	/**
	 * Feature manager instance
	 *
	 * @var Feature_Manager
	 */
	private $feature_manager;

	/**
	 * Menu instance
	 *
	 * @var Menu
	 */
	private $menu;

	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Constructor
	 *
	 * @param Feature_Manager $feature_manager Feature manager instance.
	 * @param Settings        $settings        Settings instance.
	 * @param Menu            $menu            Menu instance.
	 */
	public function __construct(
		Feature_Manager $feature_manager,
		Settings $settings,
		Menu $menu
	) {
		$this->feature_manager = $feature_manager;
		$this->settings = $settings;
		$this->menu = $menu;
	}

	/**
	 * Initialize the admin interface
	 *
	 * @return void
	 */
	public function init(): void
	{
		add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

		$this->settings->init();
		$this->menu->init();
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void
	{
		$screen = get_current_screen();
		if (!$screen || strpos($screen->id, 'intellitonic-admin') === false) {
			return;
		}

		wp_enqueue_style(
			'intellitonic-admin',
			INTELLITONIC_ADMIN_URL . 'includes/admin/css/admin.css',
			[],
			INTELLITONIC_ADMIN_VERSION
		);

		wp_enqueue_script(
			'intellitonic-admin',
			INTELLITONIC_ADMIN_URL . 'includes/admin/js/admin.js',
			['jquery'],
			INTELLITONIC_ADMIN_VERSION,
			true
		);

		wp_localize_script(
			'intellitonic-admin',
			'intellitonicAdmin',
			[
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('intellitonic_admin_nonce'),
			]
		);
	}
}
