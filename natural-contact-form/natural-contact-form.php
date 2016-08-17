<?php
/**
 * Plugin Name: Natural Contact Form
 * Plugin URI: http://kirkbowers.com/wordpress/naturalcontactform
 * Description: Provides a simple contact form with minimal customization, honey pot spam  protection, and a natural format for the email message that makes replying to easy.
 * Version: 0.1.0
 * Author: Kirk Bowers
 * Author URI: http://kirkbowers.com
 * License: GPLv2
 * Text Domain: natural-contact-form
 * Domain Path: /languages
 */

// The reverse URL namespacing convention is not common in PHP that I've seen, but it
// is the norm in Java and iOS land.  I think it's a good convention, pretty much 
// guaranteeing namespace uniqueness, so I adopt it here.

if ( defined( 'ABSPATH' ) && ! class_exists( '\com\kirkbowers\naturalcontactform\Plugin' ) )
{
  $dir = dirname( __FILE__ );
  // This is a somewhat clumsy way to get the path relative to the plugin dir, as needed
  // by load_plugin_textdomain
  $dir = str_replace(WP_PLUGIN_DIR . '/', '', $dir);

  load_plugin_textdomain('natural-contact-form', false, $dir . '/languages' );

  $plugin_dir_path = plugin_dir_path( __FILE__ );
	require  $plugin_dir_path . 'include/plugin.php';
	\com\kirkbowers\naturalcontactform\Plugin::start(__FILE__);

  register_activation_hook( __FILE__, 
    \com\kirkbowers\naturalcontactform\Plugin::namespaced('install') );
  register_uninstall_hook( __FILE__, 
    \com\kirkbowers\naturalcontactform\Plugin::namespaced('uninstall') );
}
