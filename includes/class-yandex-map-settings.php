<?php
class Yandex_Map_Settings {
	private $options;

	public function __construct() {
		add_action('admin_menu', [$this, 'add_settings_page']);
		add_action('admin_init', [$this, 'register_settings']);
		add_action('admin_notices', [$this, 'show_api_key_notice']);
	}

	public function add_settings_page() {
		add_options_page(
			'Yandex Maps Settings',
			'Yandex Maps',
			'manage_options',
			'yandex-maps-settings',
			[$this, 'render_settings_page']
		);
	}

	public function register_settings() {
		register_setting('yandex_maps_options', 'yandex_maps_options', [$this, 'sanitize']);

		add_settings_section(
			'api_section',
			'API Settings',
			[$this, 'render_section_info'],
			'yandex-maps-settings'
		);

		add_settings_field(
			'api_key',
			'Yandex Maps API Key',
			[$this, 'render_api_key_field'],
			'yandex-maps-settings',
			'api_section'
		);
	}

	public function sanitize($input) {
		$sanitized = [];
		$sanitized['api_key'] = sanitize_text_field($input['api_key'] ?? '');
		return $sanitized;
	}

	public function render_settings_page() {
		$this->options = get_option('yandex_maps_options');
		?>
		<div class="wrap">
			<h1>Yandex Maps Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('yandex_maps_options');
				do_settings_sections('yandex-maps-settings');
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function render_section_info() {
		$current_lang = get_yandex_language_code();
		echo '<p>' . sprintf(
				__('Current map language: %s (based on WordPress language: %s)', 'yandex-map-for-acf'),
				strtoupper(str_replace('_', '-', $current_lang)),
				get_locale()
			) . '</p>';
		echo '<p>' . __('Enter your Yandex Maps API settings below:', 'yandex-map-for-acf') . '</p>';
	}

	public function render_api_key_field() {
		$value = $this->options['api_key'] ?? '';
		echo '<input type="text" id="api_key" name="yandex_maps_options[api_key]" value="' . esc_attr($value) . '" class="regular-text">';
		echo '<p class="description">Get your API key from <a href="https://developer.tech.yandex.ru/" target="_blank">Yandex Developer Console</a></p>';
	}

	public function show_api_key_notice() {
		$options = get_option('yandex_maps_options');
		if (empty($options['api_key'])) {
			echo '<div class="notice notice-warning"><p>';
			echo '<strong>Yandex Maps for ACF:</strong> ';
			echo 'Please <a href="' . admin_url('options-general.php?page=yandex-maps-settings') . '">enter your API key</a> for maps to work properly.';
			echo '</p></div>';
		}
	}


}