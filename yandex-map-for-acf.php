<?php
/*
Plugin Name: Yandex Map for ACF
Description: Adds Yandex Map field type to Advanced Custom Fields
Version: 0.0.1
Author: sk
Text Domain: yandex-map-for-acf
*/

defined('ABSPATH') || exit;

// Check if ACF is active
if (!class_exists('ACF')) {
	add_action('admin_notices', function() {
		echo '<div class="error"><p>' .
		     __('Yandex Map for ACF requires Advanced Custom Fields to be installed and active.', 'yandex-map-for-acf') .
		     '</p></div>';
	});
	return;
}

// Define constants
define('YANDEX_MAP_FOR_ACF_VERSION', '1.0.0');
define('YANDEX_MAP_FOR_ACF_PATH', plugin_dir_path(__FILE__));
define('YANDEX_MAP_FOR_ACF_URL', plugin_dir_url(__FILE__));

// Include field class
require_once YANDEX_MAP_FOR_ACF_PATH . 'includes/class-yandex-map-field.php';

// Initialize plugin
add_action('acf/include_field_types', function() {
	new Yandex_Map_Field();
});

// Add settings link
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
	$settings_link = '<a href="' . admin_url('options-general.php?page=yandex-maps-settings') . '">' . __('Settings') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
});

// Settings page
require_once YANDEX_MAP_FOR_ACF_PATH . 'includes/class-yandex-map-settings.php';
new Yandex_Map_Settings();