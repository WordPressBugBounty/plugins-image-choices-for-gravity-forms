<?php

GFForms::include_addon_framework();

class GFImgChoiceAddon extends GFAddOn {

	protected $_version = GFIMP_ADDON_VERSION;
	protected $_min_gravityforms_version = '2.8';
	protected $_slug = 'image-choices-for-gravity-forms';
	protected $_path = 'image-choices-for-gravity-forms/gf-img-choices.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Image Picker For Gravity Forms';
	protected $_short_title = 'Image Picker';
	protected $_defaultTheme = "basic";
	protected $_defaultColor = "#0077FF";
	protected $_defaultLargeColumn = "6";
	protected $_defaultMediumColumn = "4";
	protected $_defaultSmallColumn = "2";
	protected $_supported_field_types = ['radio', 'checkbox'];
	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFImgChoiceAddon
	 */
	public static function get_instance() {
		if (self::$_instance == null) {
			self::$_instance = new GFImgChoiceAddon();
		}

		return self::$_instance;
	}


	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();

		add_filter('gform_tooltips', array($this, 'gfic_add_tooltips'));
		add_action('gform_enqueue_scripts', array($this, 'add_frontend_enqueue_styles'), 10, 2);
		add_filter('gform_field_choice_markup_pre_render', array($this, 'gfic_label_image_field'), 10, 4);
		add_filter('gform_field_css_class', array($this, 'gfic_custom_class'), 10, 3);

		add_filter('gform_field_settings_tabs', array($this, 'gfic_fields_settings_tab'), 10, 2);
		add_action('gform_field_settings_tab_content_img_choice_tab', array($this, 'gfic_fields_settings_tab_content'), 10, 2);
	}

	public function get_menu_icon() {
		return file_get_contents($this->get_base_path() . '/assets/images/image_picker.svg');
	}

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gfimp_admin_script',
				'src'     => $this->get_base_url() . '/assets/js/image-picker-admin.js',
				'version' => $this->_version,
				'deps'    => array('jquery', 'wp-color-picker'),
				'enqueue'  => array(
					array('admin_page' => array('form_editor', 'plugin_settings', 'form_settings')),
					array($this, 'maybe_enqueue_main_scripts_styles')
				)
			)
		);

		return array_merge(parent::scripts(), $scripts);
	}

	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gfimp_admin_style',
				'src'     => $this->get_base_url() . '/assets/css/gfimp_admin_style.css',
				'version' => $this->_version,
				'enqueue' => array(
					array('admin_page' => array('form_editor', 'plugin_settings', 'form_settings')),
					array($this, 'maybe_enqueue_main_scripts_styles')
				)
			),
			array(
				'handle'  => 'gfimp_front_style',
				'src'     => $this->get_base_url() . '/assets/css/gfimp_front_style.css',
				'version' => $this->_version,
				'enqueue' => array(
					array('field_types' => array('radio', 'checkbox')),
					array($this, 'maybe_enqueue_main_scripts_styles')
				)
			)
		);

		return array_merge(parent::styles(), $styles);
	}


	public function init_admin() {
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

		parent::init_admin();
	}

	public function admin_enqueue_scripts() {
		if ($this->is_form_editor()) {
			wp_enqueue_media(); // For Media Library
		}
	}

	public function maybe_enqueue_main_scripts_styles($form) {
		return (!empty($form) && $this->form_contains_image_picker_fields($form));
	}

	public function form_contains_image_picker_fields($form) {
		$has_image_picker = false;
		foreach ($form['fields'] as $field) {
			if ($this->field_has_image_picker_enabled($field)) {
				$has_image_picker = true;
				break;
			}
		}
		return $has_image_picker;
	}

	public function gfic_fields_settings_tab($tabs, $form) {
		$tabs[] = array(
			// Define the unique ID for your tab.
			'id'             => 'img_choice_tab',
			// Define the title to be displayed on the toggle button your tab.
			'title'          => 'Image Picker',
			// Define an array of classes to be added to the toggle button for your tab.
			'toggle_classes' => array('gfic_toggle_1', 'gfic_toggle_2'),
			// Define an array of classes to be added to the body of your tab.
			'body_classes'   => array('gfic_toggle_class'),
		);

		return $tabs;
	}

	public function gfic_fields_settings_tab_content($form) {
		$columns = $this->get_columns();
		$theme_setting_value = $this->get_active_value_for_fields($form, 'gfimp_theme', $this->get_themes(), $this->_defaultTheme);
		$large_column_setting_value = $this->get_active_value_for_fields($form, 'gfimp_column_large', $columns, $this->_defaultLargeColumn);
		$medium_column_setting_value = $this->get_active_value_for_fields($form, 'gfimp_column_medium', $columns, $this->_defaultMediumColumn);
		$small_column_setting_value = $this->get_active_value_for_fields($form, 'gfimp_column_small', $columns, $this->_defaultSmallColumn);

?>
		<li class="img_choice_field_setting field_setting">
			<ul>
				<li class="imgchoice_check" style="margin-bottom: 15px">
					<input type="checkbox" id="gfic_enable_imgchoice" onclick="SetFieldProperty('initImageGField', this.checked);" />
					<label for="gfic_enable_imgchoice" class="inline">
						<?php esc_html_e("Enable Image Picker Options", "image-choices-for-gravity-forms"); ?>
						<?php gform_tooltip("enable_image_choices"); ?>
					</label>
				</li>
				<ul class="gfimp_options">
					<li class="gfimp_theme_setting">
						<label for="gfimp_theme" class="section_label">
							<?php esc_html_e("Choose Theme", "image-choices-for-gravity-forms"); ?>
							<?php gform_tooltip("img_column"); ?>
						</label>
						<select name="gfimp_theme" id="gfimp_theme" onchange="SetFieldProperty('gfimp_theme', this.value);">
							<option value="form_setting">
								<?php /* translators: %s: theme settings value */ echo sprintf(esc_html__("Use Form Setting (%s)", "image-choices-for-gravity-forms"), esc_html($theme_setting_value)); ?></option>
							<?php
							$themes = $this->get_themes();
							foreach ($themes as $theme_key => $theme_label) {
								echo '<option value="' . esc_attr($theme_key) . '">' . esc_html($theme_label) . '</option>';
							}
							?>
						</select>
					</li>
					<li class="imgchoice_column" style="margin-bottom: 15px">
						<label for="gfic_imgcolumn_label" class="section_label">
							<?php esc_html_e("Choose Large device column", "image-choices-for-gravity-forms"); ?>
							<?php gform_tooltip("img_column"); ?>
						</label>
						<select name="pcafe_imgp_column" id="pcafe_imgp_column" onChange="SetFieldProperty('pcafeImgpColumn', this.value);">
							<option value="form_setting">
								<?php /* translators: %s: theme settings value */ echo sprintf(esc_html__("Use Form Setting (%s)", "image-choices-for-gravity-forms"), esc_html($large_column_setting_value)); ?></option>
							<?php
							foreach ($columns as $column_key => $column_label) {
								echo '<option value="' . esc_attr($column_key) . '">' . esc_html($column_label) . '</option>';
							}
							?>
						</select>
					</li>
					<li class="gfimp_column_medium_setting" style="margin-bottom: 15px">
						<label for="gfimp_column_medium" class="section_label">
							<?php esc_html_e("Choose medium device column", "image-choices-for-gravity-forms"); ?>
							<?php gform_tooltip("img_column"); ?>
						</label>
						<select name="gfimp_column_medium" id="gfimp_column_medium" onChange="SetFieldProperty('gfimp_column_medium', this.value);">
							<option value="form_setting">
								<?php /* translators: %s: theme settings value */ echo sprintf(esc_html__("Use Form Setting (%s)", "image-choices-for-gravity-forms"), esc_html($medium_column_setting_value)); ?></option>
							<?php
							foreach ($columns as $column_key => $column_label) {
								echo '<option value="' . esc_attr($column_key) . '">' . esc_html($column_label) . '</option>';
							}
							?>
						</select>
					</li>
					<li class="gfimp_column_small_setting" style="margin-bottom: 15px">
						<label for="gfimp_column_small" class="section_label">
							<?php esc_html_e("Choose small device column", "image-choices-for-gravity-forms"); ?>
							<?php gform_tooltip("img_column"); ?>
						</label>
						<select name="gfimp_column_small" id="gfimp_column_small" onChange="SetFieldProperty('gfimp_column_small', this.value);">
							<option value="form_setting">
								<?php /* translators: %s: theme settings value */ echo sprintf(esc_html__("Use Form Setting (%s)", "image-choices-for-gravity-forms"), esc_html($small_column_setting_value)); ?></option>
							<?php
							foreach ($columns as $column_key => $column_label) {
								echo '<option value="' . esc_attr($column_key) . '">' . esc_html($column_label) . '</option>';
							}
							?>
						</select>
					</li>
				</ul>
			</ul>
		</li>

		<?php
	}

	public function gfic_label_image_field($choice_markup, $choice, $field, $value) {
		if (! $this->field_has_image_picker_enabled($field)) {
			return $choice_markup;
		}

		$image_url = (isset($choice['imageUrl'])) ? $choice['imageUrl'] : '';

		if (is_admin()) {
			$image_markup = '<span class="pcafe_imgp_wrap"><img src="' . esc_url($image_url) . '" /></span>';
		} else {
			if (!empty($image_url)) {
				$image_markup = '<span class="pcafe_imgp_wrap"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($choice['text']) . '" class="image_picker_image" /></span>';
			} else {
				$image_markup = '<span class="pcafe_imgp_wrap"></span>';
			}
		}

		$choice_markup = preg_replace('#<label\b([^>]*)>(.*?)</label\b[^>]*>#s', implode("", [
			'<label ${1} >',
			$image_markup,
			'<span class="pcafe_imgp_text">${2}</span>',
			'</label>',
		]), $choice_markup);

		return $choice_markup;
	}

	public function gfic_custom_class($classes, $field, $form) {
		if (! $this->field_has_image_picker_enabled($field)) {
			return $classes;
		}

		$classes .= GFCommon::is_form_editor() ? ' pcafe_imgp_admin' : ' init_pcafe_imgp';

		if (is_admin()) {
			return $classes;
		}

		$legacy_theme = $field->pcafeNewStyle === true ? 'card' : $this->_defaultTheme;

		$theme_name = $this->get_active_settings_value($form, $field, 'gfimp_theme', 'gfimp_theme', $legacy_theme);
		$large_column = $this->get_active_settings_value($form, $field, 'gfimp_column_large', 'pcafeImgpColumn', $this->_defaultLargeColumn, true);
		$medium_column = $this->get_active_settings_value($form, $field, 'gfimp_column_medium', 'gfimp_column_medium', $this->_defaultMediumColumn, true);
		$small_column = $this->get_active_settings_value($form, $field, 'gfimp_column_small', 'gfimp_column_small', $this->_defaultSmallColumn, true);

		if ($theme_name != '') {
			$classes .= ' pcafe_theme_' . $theme_name;
		}

		if ($large_column != '' && $large_column != 'default') {
			$classes .= ' pcafe_imgp_col_lg_' . $large_column;
		}

		if ($medium_column != '') {
			$classes .= ' pcafe_imgp_col_md_' . $medium_column;
		}

		if ($small_column != '') {
			$classes .= ' pcafe_imgp_col_sm_' . $small_column;
		}

		return $classes;
	}

	public function add_frontend_enqueue_styles($form, $is_ajax) {
		if (is_admin() || wp_doing_ajax()) {
			return;
		}


		$form_id = rgar($form, 'id');
		$form_settings = $this->get_form_settings($form);

		ob_start();
		$global_color = $this->get_plugin_setting_value('pcafe_imgp_color', $this->_defaultColor);

		if (!empty($global_color)) {
		?>
			.gform_wrapper .gfield.init_pcafe_imgp[class*="pcafe_theme_"] {
			--gfimp-global-color: <?php echo esc_attr($global_color); ?>;
			}
		<?php
		}

		$global_overrides_css = ob_get_clean();
		$global_overrides_css_ref = 'gfimp_global_overrides_css__' . $form_id;

		if (!wp_style_is($global_overrides_css_ref) && !empty($global_overrides_css)) {
			wp_register_style($global_overrides_css_ref, false);
			wp_enqueue_style($global_overrides_css_ref);
			wp_add_inline_style($global_overrides_css_ref, $global_overrides_css);
		}

		ob_start();
		// Form settings
		$form_color = $this->get_form_setting_value('pcafe_imgp_color', $form_settings);

		if (!empty($form_color) && $form_color != 'global_setting') {
		?>
			#gform_<?php echo esc_attr($form_id); ?> .gform_fields .init_pcafe_imgp[class*="pcafe_theme_"] {
			--gfimp-global-color: <?php echo esc_attr($form_color); ?>;
			}
<?php
		}

		$form_overrides_css = ob_get_clean();
		$form_overrides_css_ref = 'gfimp_form_overrides_css__' . $form_id;

		if (!wp_style_is($form_overrides_css_ref) && !empty($form_overrides_css)) {
			wp_register_style($form_overrides_css_ref, false);
			wp_enqueue_style($form_overrides_css_ref);
			wp_add_inline_style($form_overrides_css_ref, $form_overrides_css);
		}
	}

	public function gfic_add_tooltips() {
		$tooltips['enable_image_choices'] = esc_html__("Check this box to enable and show image choices options.", "image-choices-for-gravity-forms");
		$tooltips['pcafe_imgp_new_design'] = esc_html__("Check this box to enable new design.", "image-choices-for-gravity-forms");
		$tooltips['img_column'] = esc_html__("Choose column for showing on frontend form.", "image-choices-for-gravity-forms");

		return $tooltips;
	}

	public function field_has_image_picker_enabled($field) {
		return !empty($field) && in_array($field->type, $this->_supported_field_types) && property_exists($field, 'initImageGField') && $field->initImageGField === true;
	}


	public function form_settings_fields($form) {
		$settings = [];

		$settings[] = $this->theme_setting_section(true);
		$settings[] = $this->layout_setting_section(true);
		$settings[] = $this->color_setting_section(true);

		return $settings;
	}

	public function plugin_settings_fields() {

		$settings = [];

		$settings[] = $this->theme_setting_section();
		$settings[] = $this->layout_setting_section();
		$settings[] = $this->color_setting_section();

		return $settings;
	}

	public function theme_setting_section($is_form_settings = false) {
		$theme = $this->get_themes();
		$selected_theme = $this->get_plugin_setting('gfimp_theme') ?? 'basic';

		if ($is_form_settings) {
			$global_theme = [
				'label' => sprintf(
					/* translators: %s: Global alignment value */
					esc_html__('Use Global Setting - (%s)', 'image-choices-for-gravity-forms'),
					esc_html($theme[$selected_theme])
				),
				'value' => 'global_setting',
			];
			$themes = array_merge(
				[$global_theme],
				$this->setting_options_convert_into_array($theme)
			);
		} else {
			$themes = $this->setting_options_convert_into_array($theme);
		}

		return array(
			'title'  => esc_html__('Theme Options', 'image-choices-for-gravity-forms'),
			'class'  => 'gform-settings-panel--half',
			'type' => 'section',
			'fields' => array(
				array(
					'name'      => 'gfimp_theme',
					'label'     => esc_html__('Themes', 'image-choices-for-gravity-forms'),
					'tooltip'   => esc_html__('Choose theme for image picker', 'image-choices-for-gravity-forms'),
					'type'      => 'select',
					'choices'   => $themes
				)
			)
		);
	}

	public function layout_setting_section($is_form_settings = false) {
		$layout = $this->get_columns();
		$large_selected_column = $this->get_plugin_setting('gfimp_column_large') ?? $this->_defaultLargeColumn;
		$medium_selected_column = $this->get_plugin_setting('gfimp_column_medium') ?? $this->_defaultMediumColumn;
		$small_selected_column = $this->get_plugin_setting('gfimp_column_small') ?? $this->_defaultSmallColumn;

		if ($is_form_settings) {
			$global_large_column = [
				'label' => sprintf(
					/* translators: %s: Global alignment value */
					esc_html__('Use Global Setting - (%s)', 'image-choices-for-gravity-forms'),
					esc_html($layout[$large_selected_column])
				),
				'value' => 'global_setting',
			];

			$global_medium_column = [
				'label' => sprintf(
					/* translators: %s: Global alignment value */
					esc_html__('Use Global Setting - (%s)', 'image-choices-for-gravity-forms'),
					esc_html($layout[$medium_selected_column])
				),
				'value' => 'global_setting',
			];

			$global_small_column = [
				'label' => sprintf(
					/* translators: %s: Global alignment value */
					esc_html__('Use Global Setting - (%s)', 'image-choices-for-gravity-forms'),
					esc_html($layout[$small_selected_column])
				),
				'value' => 'global_setting',
			];


			$large_column = array_merge(
				[$global_large_column],
				$this->setting_options_convert_into_array($layout)
			);

			$medium_column = array_merge(
				[$global_medium_column],
				$this->setting_options_convert_into_array($layout)
			);

			$small_column = array_merge(
				[$global_small_column],
				$this->setting_options_convert_into_array($layout)
			);
		} else {
			$large_column = $this->setting_options_convert_into_array($layout);
			$medium_column = $this->setting_options_convert_into_array($layout);
			$small_column = $this->setting_options_convert_into_array($layout);
		}

		return array(
			'title'  => esc_html__('Layout Options', 'image-choices-for-gravity-forms'),
			'class'  => 'gform-settings-panel--half',
			'type' => 'section',
			'fields' => array(
				array(
					'name'      => 'gfimp_column_large',
					'label'     => esc_html__('Column - Large Device', 'image-choices-for-gravity-forms'),
					'tooltip'   => esc_html__('Choose choose column for large device', 'image-choices-for-gravity-forms'),
					'type'      => 'select',
					'choices'   => $large_column
				),
				array(
					'name'      => 'gfimp_column_medium',
					'label'     => esc_html__('Column - Medium Device', 'image-choices-for-gravity-forms'),
					'tooltip'   => esc_html__('Choose choose column for large device', 'image-choices-for-gravity-forms'),
					'type'      => 'select',
					'choices'   => $medium_column
				),
				array(
					'name'      => 'gfimp_column_small',
					'label'     => esc_html__('Column - Small Device', 'image-choices-for-gravity-forms'),
					'tooltip'   => esc_html__('Choose choose column for large device', 'image-choices-for-gravity-forms'),
					'type'      => 'select',
					'choices'   => $small_column
				),
			)
		);
	}

	public function color_setting_section($is_form_settings = false) {

		return array(
			'title'  => esc_html__('Color Options', 'image-choices-for-gravity-forms'),
			'class'  => 'gform-settings-panel--half',
			'type' => 'section',
			'fields' => array(
				array(
					'name'      => 'pcafe_imgp_color',
					'label'     => esc_html__('Image Picker Color', 'image-choices-for-gravity-forms'),
					'tooltip'   => esc_html__('Choose your color', 'image-choices-for-gravity-forms'),
					'type'      => 'text',
					'class'     => 'medium',
					'default_value' => '#0077FF',
				)
			)
		);
	}

	public function get_themes() {
		return [
			'basic'		=> esc_html__('Basic', 'image-choices-for-gravity-forms'),
			'simple'	=> esc_html__('Simple', 'image-choices-for-gravity-forms'),
			'card'      => esc_html__('Card', 'image-choices-for-gravity-forms'),
		];
	}

	public function get_columns() {
		return [
			'default' => esc_html__("Theme Default", "image-choices-for-gravity-forms"),
			'1'  => '1',
			'2'  => '2',
			'3'  => '3',
			'4'  => '4',
			'5'  => '5',
			'6'  => '6',
			'7'  => '7',
			'8'  => '8',
			'9'  => '9',
			'10' => '10',
			'11' => '11',
			'12' => '12',
		];
	}
	protected function setting_options_convert_into_array($choices) {
		$options = array();
		foreach ($choices as $choice_value => $choice_label) {
			$options[] = array(
				'value' => $choice_value,
				'label' => $choice_label,
			);
		}
		return $options;
	}

	public function get_plugin_setting_value($setting_key, $default_value = null, $plugin_settings = null) {
		if (empty($setting_key)) {
			return null;
		}

		if (empty($plugin_settings)) {
			$value = $this->get_plugin_setting($setting_key);
		} else {
			$value = (isset($plugin_settings[$setting_key])) ? $plugin_settings[$setting_key] : null;
		}

		if (is_null($value)) {
			$value = $default_value;
		}

		return $value;
	}

	public function get_form_setting_value($setting_key, $form_settings) {
		if (empty($setting_key)) {
			return null;
		}

		$default_value = "global_setting";
		$value = (isset($form_settings[$setting_key]) && !empty($form_settings[$setting_key])) ? $form_settings[$setting_key] : $default_value;

		return $value;
	}

	public function get_active_value_for_fields($form, $setting_key, $options, $default_value) {
		$global_value = $this->get_plugin_setting_value($setting_key, $default_value);
		$form_value = $this->get_form_setting_value($setting_key, $this->get_form_settings($form));

		$g_value = $options[$global_value];

		if ($form_value == 'global_setting') {
			/* translators: %s: global fixed width settings value */
			$value = sprintf(esc_html__("Global: %s", "image-choices-for-gravity-forms"), $g_value);
		} else {
			$value = $options[$form_value];
		}

		return $value;
	}

	public function get_active_settings_value($form, $field, $setting_key, $field_key, $default_value = '', $isInputField = false) {
		$form_settings = $this->get_form_settings($form);

		$global_value = $this->get_plugin_setting_value($setting_key, $default_value, null);

		if ($isInputField && $global_value === 'default') {
			$global_value = $default_value;
		}

		$form_value = $this->get_form_setting_value($setting_key, $form_settings);

		$field_value = $this->get_field_setting_value($field_key, $field);

		if ($field_value === 'form_setting') {
			return ($form_value === 'global_setting') ? $global_value : $form_value;
		}

		return $field_value;
	}

	public function get_field_setting_value($field_key, $field, $isInputField = false) {
		if (empty($field_key)) {
			return null;
		}

		$default_value = $isInputField ? '' : 'form_setting';

		if (is_object($field)) {
			$value = property_exists($field, $field_key) ? $field->{$field_key} : $default_value;
		} else {
			$value = (isset($field[$field_key]) && !empty(isset($field[$field_key]))) ? $field[$field_key] : $default_value;
		}

		return $value;
	}
}
