<?php
/**
* Plugin Name: Admin Menu Cleaner
* Plugin URI: https://wordpress.org/plugins/wp-admin-menu-wizard
* Description: With this plugin you can hide the admin menu items you do not use by a flick of a toggle button.
* Version: 1.1.3
* Requires at least: 3.0
* Requires PHP: 5.6
* Author: Alfa Developers
* Author URI: https://alfadevelopers.ro/en/plugins/admin-menu-cleaner
* Text Domain: wp-admin-menu-wizard
* Domain Path: /languages
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
* This program is free software; you can redistribute it and/or modify it under the terms of the GNU
* General Public License version 2, as published by the Free Software Foundation. You may NOT assume
* that you can use any other version of the GPL.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
* even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

// Exit on direct access atempt.
if(!defined('ABSPATH')) {
	exit;
}

/**
 * Add settings link for this plugin on "plugins" page
**/
function ad_plugin_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=wp_admin_menu_wizard' ) .
		'">' . __('Settings','wp-admin-menu-wizard') . '</a>';
	return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'ad_plugin_settings_link');

/**
 * Load translation filesize
**/
function ad_load_translate() {
  load_plugin_textdomain( 'wp-admin-menu-wizard', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('init', 'ad_load_translate');

/**
 * Include and use admin notice class
**/
require_once plugin_dir_path( __FILE__ ) . 'includes/notice-class.php';
add_action('admin_notices', [new AdmwAdminNotice(), 'displayNotice']);


/**
 * Enqueue scripts and styles
 **/
function ad_admin_scripts() {
	wp_enqueue_style('admin-styles', plugin_dir_url( __FILE__ ).'/css/admin-menu-cleaner.css');
	wp_enqueue_script('admin-scripts', plugin_dir_url( __FILE__ ).'js/admin-menu-cleaner.js');
}
add_action('admin_enqueue_scripts', 'ad_admin_scripts');

/**
 * Define default values for menus
**/
function  ad_default_Options(){
	$options =  array();
	foreach($GLOBALS['menu'] as $menu){
		$options[str_replace('.', 'tgtadtgt',$menu[2])] = 0;
	}
	return $options;
}

/**
 * Helper function to get options.
**/
function ad_get_options(){
	return get_option('ad_menu_settings_option', ad_default_options());
}

/**
 * Function to control the menu clear toggle
 **/
function ad_admin_menu_clear(){
	if(isset($_GET['clearmenu'])){
	$display = $_GET['clearmenu'];
		if($display == 'on'){
			$adMenuOn = array('adMenuOn' => 1);
			update_option('ad_menu_settings_toggle', $adMenuOn);
			AdmwAdminNotice::displaySuccess(__('Menu cleared.', 'wp-admin-menu-wizard'));
		}elseif($display == 'off'){
			$adMenuOn = array('adMenuOn' => 0);
			update_option('ad_menu_settings_toggle', $adMenuOn);
			AdmwAdminNotice::displaySuccess(__('Menu restored.', 'wp-admin-menu-wizard'));
		}
	}
}
add_action('admin_menu', 'ad_admin_menu_clear');

/**
 * Function to hide the menus that user checked
**/
function ad_hide_menu_items() {
	if (isset($_GET['page']) && $_GET['page'] == 'wp_admin_menu_wizard'){
		return;
	}else{
		$option = get_option('ad_menu_settings_toggle');
		if($option['adMenuOn'] ==1){
			$options = ad_get_options();
			foreach ($options as $key => $value){
				if($value ==1){
					remove_menu_page( str_replace('tgtadtgt', '.',$key) );
				}
			}
		}
	}
}
add_action('admin_init', 'ad_hide_menu_items');

/**
 * Add menu entries for module.
*/
function ad_add_admin_menu(){
	$option = get_option('ad_menu_settings_toggle');
	$checker ="";
	if($option["adMenuOn"] ==1){
		$checker = 'checked="checked"';
	}
	$html = '<div class="ad-mc">Menu cleaner <br><label class="switch"><input class="switch-ch" type="checkbox"value="1"'. $checker.'><span class="slider ad"></span></label></div>';
	add_menu_page( 'Admin menu cleaner', $html, 'manage_options', 'wp_admin_menu_wizard','','',150 );
	add_submenu_page('wp_admin_menu_wizard', 'Admin menu cleaner settings', 'Settings', 'manage_options', 'wp_admin_menu_wizard', 'ad_options_page' );
}
add_action( 'admin_menu', 'ad_add_admin_menu' );

/**
 * Function to generate the module settings page and save maneu configuration.
**/
function ad_options_page(){
	$notice = new AdmwAdminNotice();
	$notice->displayNotice();

	$options = ad_get_options();
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$optionUpdate = array();
		foreach($_POST as $field => $value) {
			$optionUpdate[$field] = $value;
		}
		if(update_option('ad_menu_settings_option', $optionUpdate)){
			echo '<div class="notice notice-success is-dismissible"><p>'.__( 'Settings saved.', 'wp-admin-menu-wizard' ).'</p></div>';
		}
	}

	$option = ad_get_options();?>
	<div class="wrap">
		<h1><?= esc_html(get_admin_page_title()); ?></h1>
		<p><strong><?php _e('Check the menu items you want out of your way and save. When the "Menu cleaner" toggle is on, all the checked items will be out of your way.', 'wp-admin-menu-wizard'); ?></strong></p>
		<p><?php _e('For better visualization of your admin menu, when you are in this settings page, all the menu items are visible on the admin menu, even if the toggle is on. Once you leave this page, the toggle works again.', 'wp-admin-menu-wizard'); ?></p>
		<form action="" method="post">

		<?php foreach($GLOBALS['menu'] as $menu):
		if($menu[2] != 'wp_admin_menu_wizard'){ ?>

		<input type="checkbox" name="<?php  echo str_replace('.', 'tgtadtgt',$menu[2]); ?>" id="<?php echo $menu[2]; ?>" value="1"<?php if(isset($option[str_replace('.', 'tgtadtgt',$menu[2])]) && $option[str_replace('.', 'tgtadtgt',$menu[2])] ==1){echo 'checked="checked"';}?> />&nbsp;
		<?php if($menu[0] ==""){
				echo $menu[2];
			}else{
				echo $menu[0];
			}?>
			<br/>
		<?php 		}
		endforeach;
		do_settings_sections( 'pluginPage' );
		submit_button(); ?>
		</form>
	</div>
	<style>#wpfooter{background: #ffffff;}</style>
	<?php

//put Plugin links on footer
add_filter('admin_footer_text', 'afdv_plugin_links');
}

function afdv_plugin_links () {

echo '<p><a href="https://alfadevelopers.ro/en/plugins" target="_blank">Alfa Developers Plugins</a> | <a href="https://alfadevelopers.ro/en/plugins/support" target="_blank">Get Support</a></p>';

}
