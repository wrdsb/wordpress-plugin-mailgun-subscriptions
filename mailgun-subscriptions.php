<?php
/*
* Plugin Name: WRDSB Mailgun Subscriptions and Notifications
* Plugin URI: https://github.com/wrdsb/wordpress-plugin-mailgun-subscriptions
* Description: Subscribe to Mailgun mailing lists and receive post notifications
* Author: WRDSB
* Author URI: https://github.com/wrdsb
* Version: 2.3.2
* Text Domain: wrdsb-mailgun-subscriptions
* Domain Path: /languages
* License: GPLv3 or later
* GitHub Plugin URI: wrdsb/wordpress-plugin-mailgun-subscriptions
* GitHub Branch: master
*/

if ( !function_exists('wrdsb_mailgun_subscriptions_load') ) {

	function wrdsb_mailgun_subscriptions_load() {
		add_action( 'init', 'wrdsb_mailgun_subscriptions_load_textdomain', 10, 0 );
		if ( !wrdsb_mailgun_subscriptions_version_check() ) {
			add_action( 'admin_notices', 'wrdsb_mailgun_subscriptions_version_notice' );
			return;
		}
		require_once('Mailgun_Subscriptions/Plugin.php');
		\Mailgun_Subscriptions\Plugin::init(__FILE__);
		require_once('Confirmation_Page.php');
		new \Mailgun_Subscriptions\Confirmation_Page;
	}

	function wrdsb_mailgun_subscriptions_load_textdomain() {
		$domain = 'wrdsb-mailgun-subscriptions';
		// The "plugin_locale" filter is also used in load_plugin_textdomain()
		$locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain($domain, WP_LANG_DIR.'/wrdsb-mailgun-subscriptions/'.$domain.'-'.$locale.'.mo');
		load_plugin_textdomain($domain, FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
	}

	function wrdsb_mailgun_subscriptions_version_check() {
		if ( version_compare(PHP_VERSION, '5.3.2', '>=') ) {
			return TRUE;
		}
		return FALSE;
	}

	function wrdsb_mailgun_subscriptions_version_notice() {
		$message = sprintf(__('Mailgun Subscriptions and Notifications requires PHP version %s or higher. You are using version %s.', 'wrdsb-mailgun-subscriptions'), '5.3.2', PHP_VERSION);
		printf( '<div class="error"><p>%s</p></div>', $message );
	}

	add_action( 'plugins_loaded', 'wrdsb_mailgun_subscriptions_load' );
}
