<?php

/**
 * Features management page template
 *
 * @package Intellitonic\Admin
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

	<form method="post" action="options.php">
		<?php
	settings_fields('intellitonic_admin_settings');
	do_settings_sections('intellitonic_admin_settings');
	submit_button(__('Save Changes', 'intellitonic-admin'));
	?>
	</form>

	<div class="intellitonic-admin-features-info">
		<h3><?php esc_html_e('About Features', 'intellitonic-admin'); ?></h3>
		<p>
			<?php
	esc_html_e(
				'Enable or disable specific features based on your needs. Changes will take effect immediately after saving.',
				'intellitonic-admin'
	);
	?>
		</p>
		<p>
			<?php
	esc_html_e(
				'Each feature is designed to work independently, so you can enable only the functionality you need.',
				'intellitonic-admin'
	);
	?>
		</p>
	</div>
</div>
