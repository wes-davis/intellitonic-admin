<?php

/**
 * Main admin page template
 *
 * @package Intellitonic\Admin
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

	<div class="intellitonic-admin-welcome">
		<h2><?php esc_html_e('Welcome to Intellitonic Admin', 'intellitonic-admin'); ?></h2>
		<p><?php esc_html_e('Manage your Intellitonic features and settings from this central dashboard.', 'intellitonic-admin'); ?></p>
	</div>

	<div class="intellitonic-admin-quick-links">
		<h3><?php esc_html_e('Quick Links', 'intellitonic-admin'); ?></h3>
		<ul>
			<li>
				<a href="<?php echo esc_url(admin_url('admin.php?page=intellitonic-admin-features')); ?>">
					<?php esc_html_e('Manage Features', 'intellitonic-admin'); ?>
				</a>
			</li>
			<li>
				<a href="https://intellitonic.com/support" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e('Get Support', 'intellitonic-admin'); ?>
				</a>
			</li>
			<li>
				<a href="https://intellitonic.com/docs" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e('Documentation', 'intellitonic-admin'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
