<?php

/**
 * Admin class
 *
 * @package Intellitonic\Admin\Core
 * @since 1.0.0
 */

namespace Intellitonic\Admin\Core;

use Intellitonic\Admin\Core\Feature_Registry;
use Intellitonic\Admin\Core\Feature_Settings;
use Intellitonic\Admin\Core\Menu;

/**
 * Main admin interface class
 */
class Admin
{
	/**
	 * Feature registry instance
	 *
	 * @var Feature_Registry
	 */
	private $feature_registry;

	/**
	 * Menu instance
	 *
	 * @var Menu
	 */
	private $menu;

	/**
	 * Feature Settings instance
	 *
	 * @var Feature_Settings
	 */
	private $settings;

	/**
	 * Constructor
	 *
	 * @param Feature_Registry  $feature_registry Feature registry instance.
	 * @param Feature_Settings  $settings         Settings instance.
	 * @param Menu             $menu             Menu instance.
	 */
	public function __construct(
		Feature_Registry $feature_registry,
		Feature_Settings $settings,
		Menu $menu
	) {
		$this->feature_registry = $feature_registry;
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
			INTELLITONIC_ADMIN_URL . 'includes/Admin/css/admin.css',
			[],
			INTELLITONIC_ADMIN_VERSION
		);

		wp_enqueue_script(
			'intellitonic-admin',
			INTELLITONIC_ADMIN_URL . 'includes/Admin/js/admin.js',
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
