<?php
class Yandex_Map_Field extends acf_field {

	function __construct() {
		$this->name = 'yandex_map';
		$this->label = __('Yandex Map', 'yandex-map-for-acf');
		$this->category = 'jquery';
		$this->defaults = [
			'height' => '400',
			'zoom' => '12',
			'api_key' => '',
			'scroll_zoom' => true,
			'drag' => true,
		];

		parent::__construct();

		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
	}

	function enqueue_scripts() {
		$options = get_option('yandex_maps_options', []);
		$api_key = !empty($options['api_key']) ? $options['api_key'] : '';

		if ($api_key) {
			wp_enqueue_script(
				'yandex-maps-api',
				'https://api-maps.yandex.ru/2.1/?lang=en_US&apikey=' . $api_key,
				[],
				null
			);
		}

		// Enqueue JS
		wp_enqueue_script(
			'yandex-map-field',
			YANDEX_MAP_FOR_ACF_URL . 'assets/js/yandex-map-field.js',
			['yandex-maps-api'],
			filemtime(YANDEX_MAP_FOR_ACF_PATH . 'assets/js/yandex-map-field.js')
		);

		// Enqueue CSS
		wp_enqueue_style(
			'yandex-map-field',
			YANDEX_MAP_FOR_ACF_URL . 'assets/css/yandex-map-field.css',
			[],
			filemtime(YANDEX_MAP_FOR_ACF_PATH . 'assets/css/yandex-map-field.css')
		);

		wp_localize_script('yandex-map-field', 'yandex_map_vars', [
			'geocode_error' => __('Could not geocode the address', 'yandex-map-for-acf'),
		]);
	}

	function render_field_settings($field) {
		acf_render_field_setting($field, [

			'label' => __('Height', 'yandex-map-for-acf'),
			'instructions' => __('Map height in pixels', 'yandex-map-for-acf'),
			'type' => 'number',
			'name' => 'height',
			'append' => 'px',
		]);

		acf_render_field_setting($field, [
			'label' => __('Default Zoom', 'yandex-map-for-acf'),
			'instructions' => __('Initial zoom level (1-19)', 'yandex-map-for-acf'),
			'type' => 'number',
			'name' => 'zoom',
			'min' => 1,
			'max' => 19,
		]);

		acf_render_field_setting($field, [
			'label' => __('Scroll Zoom', 'yandex-map-for-acf'),
			'instructions' => __('Allow zooming with mouse wheel', 'yandex-map-for-acf'),
			'type' => 'true_false',
			'name' => 'scroll_zoom',
			'ui' => 1,
		]);

		acf_render_field_setting($field, [
			'label' => __('Draggable', 'yandex-map-for-acf'),
			'instructions' => __('Allow dragging the map', 'yandex-map-for-acf'),
			'type' => 'true_false',
			'name' => 'drag',
			'ui' => 1,
		]);
	}

	function render_field($field) {
		$value = wp_parse_args($field['value'] ?? [], [
			'address' => '',
			'lat' => '55.751244',
			'lng' => '37.618423',
			'zoom' => $field['zoom'] ?? 12,
		]);

		$attrs = [
			'id' => $field['id'],
			'class' => $field['class'] . ' yandex-map-field',
			'data-map-id' => 'yandex-map-' . $field['key'],
			'data-lat' => $value['lat'],
			'data-lng' => $value['lng'],
			'data-zoom' => $value['zoom'],
			'data-scroll' => $field['scroll_zoom'] ? 'true' : 'false',
			'data-drag' => $field['drag'] ? 'true' : 'false',
		];
		?>
      <div <?php echo acf_esc_attrs($attrs); ?> style="height: <?php echo esc_attr($field['height']); ?>px;">
        <div class="yandex-map-search">
          <input type="text" class="yandex-map-address" value="<?php echo esc_attr($value['address']); ?>"
                 placeholder="<?php esc_attr_e('Search location...', 'yandex-map-for-acf'); ?>">
          <button type="button" class="button yandex-map-search-button">
			  <?php _e('Search', 'yandex-map-for-acf'); ?>
          </button>
        </div>

        <div class="yandex-map-container" id="yandex-map-<?php echo esc_attr($field['key']); ?>"></div>

        <div class="yandex-map-coordinates">
          <input type="text" name="<?php echo esc_attr($field['name']); ?>[address]"
                 value="<?php echo esc_attr($value['address']); ?>" class="yandex-map-address-field" readonly>
          <input type="hidden" name="<?php echo esc_attr($field['name']); ?>[lat]" value="<?php echo esc_attr($value['lat']); ?>">
          <input type="hidden" name="<?php echo esc_attr($field['name']); ?>[lng]" value="<?php echo esc_attr($value['lng']); ?>">
          <input type="hidden" name="<?php echo esc_attr($field['name']); ?>[zoom]" value="<?php echo esc_attr($value['zoom']); ?>">
        </div>
      </div>
		<?php
	}

	function load_value($value, $post_id, $field) {
		return is_array($value) ? $value : [];
	}

	function update_value($value, $post_id, $field) {
		return is_array($value) ? $value : [];
	}

	function format_value($value, $post_id, $field) {
		return wp_parse_args($value ?? [], [
			'address' => '',
			'lat' => '',
			'lng' => '',
			'zoom' => $field['zoom'] ?? 12,
		]);
	}
}