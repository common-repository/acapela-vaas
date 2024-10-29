<?php

/*
* Plugin Name: Acapela VaaS
* Description: Inserts Acapela VaaS Javascript code in the head tag of your Wordpress template.
* Version: 1.0.0
* Author: Wavenet SPRL
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

defined('ABSPATH') or die('No external scripts');

if (!class_exists('Acapela'))
{
	class Acapela
	{
		private $plugin_slug = 'acapela';
		private $options_section_slug = 'acapela_section';
		private $vaas_js_hash = 'sha256-dqHUvFomKJT23n2t9CuxI2rQRK5AUsXHxLAWOmLOgqc=';
		
		function __construct()
		{
			add_filter('script_loader_tag', array($this, 'alter_javascript'), 10, 3);
			add_action('wp_enqueue_scripts', array($this, 'insert_javascript'));
			add_action('admin_menu', array($this, 'settings_page'));
			add_action('admin_init', array($this, 'setup_sections'));
			add_action('admin_init', array($this, 'setup_fields'));
		}
		
		public function insert_javascript()
		{
			wp_register_style('acapela_css', 'https://webreader-beta.acapela-group.com/vaas/latest/vaas.css');
			wp_register_script('acapela_js', 'https://webreader-beta.acapela-group.com/vaas/latest/vaas.js');
			wp_enqueue_style('acapela_css');
			wp_enqueue_script('acapela_js');
		}
		
		public function alter_javascript($tag, $handle, $src)
		{
			if ('acapela_js' == $handle)
			{
				$attributes = '';
				$attributes .= ' data-key="' . get_option('key_field') . '"';
				$attributes .= ' data-login="' . get_option('login_field') . '"';
				$attributes .= ' data-app="' . get_option('app_field') . '"';
				$attributes .= ' data-pwd="' . get_option('password_field') . '"';
				$attributes .= ' data-default-voice="' . get_option('default_voice_field') . '"';
				$attributes .= ' data-selector="' . get_option('selector_field') . '"';
				$attributes .= ' data-exclude-selector="' . get_option('exclude_selector_field') . '"';
				$attributes .= ' integrity="' . $this->vaas_js_hash . '" crossorigin="anonymous"';
				
				return str_replace(' src', $attributes . ' src', $tag);
			}
			
			return $tag;
		}
		
		public function settings_page()
		{
			$page_title = 'Acapela VaaS Settings';
			$menu_title = 'Acapela VaaS';
			$capability = 'manage_options';
			$slug = $this->plugin_slug;
			$callback = array($this, 'settings_page_content');

			add_submenu_page('options-general.php', $page_title, $menu_title, $capability, $slug, $callback);
		}
		
		public function settings_page_content()
		{
			?>
			<div class="wrap">
				<h2>Acapela VaaS</h2>
				<form method="post" action="options.php">
					<?php
						settings_fields($this->plugin_slug);
						do_settings_sections($this->plugin_slug);
						submit_button();
					?>
				</form>
			</div>
			<?php
		}
		
		public function setup_sections()
		{
			add_settings_section($this->options_section_slug, 'Settings', false, $this->plugin_slug);
		}
		
		public function setup_fields()
		{
			$fields = array(
				array(
					'uid' => 'key_field',
					'label' => 'Key',
					'section' => $this->options_section_slug,
					'type' => 'text',
					'options' => false
				),
				array(
					'uid' => 'login_field',
					'label' => 'Login',
					'section' => $this->options_section_slug,
					'type' => 'text',
					'options' => false
				),
				array(
					'uid' => 'app_field',
					'label' => 'App',
					'section' => $this->options_section_slug,
					'type' => 'text',
					'options' => false
				),
				array(
					'uid' => 'password_field',
					'label' => 'Password',
					'section' => $this->options_section_slug,
					'type' => 'text',
					'options' => false
				),
				array(
					'uid' => 'default_voice_field',
					'label' => 'Default voice',
					'section' => $this->options_section_slug,
					'type' => 'text',
					'options' => false,
					'helper' => 'Select the voice which will be used for text to speech.'
				),
				array(
					'uid' => 'selector_field',
					'label' => 'Selector',
					'section' => $this->options_section_slug,
					'type' => 'text',
					'options' => false,
					'helper' => 'Select all css selectors which identify sections where you want to active text to speech functionality.'
				),
				array(
					'uid' => 'exclude_selector_field',
					'label' => 'Exclude selector',
					'section' => $this->options_section_slug,
					'type' => 'text',
					'options' => false
				)
			);
			
			foreach ($fields as $field)
			{
				add_settings_field($field['uid'], $field['label'], array($this, 'field_callback'), $this->plugin_slug, $field['section'], $field);
				register_setting($this->plugin_slug, $field['uid']);
			}
		}
		
		public function field_callback($arguments)
		{
			echo '<input name="' . $arguments['uid'] . '" id="' . $arguments['uid'] . '" type="' . $arguments['type'] . '" value="' . get_option($arguments['uid']) . '" class="regular-text"/>';
			if ($helper = $arguments['helper'])
			{
				echo '<p class="description">' . $helper . '</p>';
			}
		}
	}
	
	$acapela = new Acapela();
}
