<?php
/**
 * Plugin Name: mvcoffee-wp
 * Plugin URI: http://mvcoffee.org
 * Description: This is a developer plugin that provides a Rails-like server side Model layer that is compatible with the client-side MVC framework MVCoffee.  At the moment it is incomplete, completely ad hoc for use within the Natural Contact Form plugin, only containing functionality needed specifically for that plugin.  I anticipate it eventually also providing the mvcoffee.js client for client-side model validation.
 * Version: 0.1.0
 * Author: Kirk Bowers
 * Author URI: http://kirkbowers.com
 * License: GPLv2
 * Text Domain: mvcoffee-wp
 * Domain Path: /languages
 */

// The reverse URL namespacing convention is not common in PHP that I've seen, but it
// is the norm in Java and iOS land.  I think it's a good convention, pretty much 
// guaranteeing namespace uniqueness, so I adopt it here.

if ( defined( 'ABSPATH' ) && ! class_exists( '\org\mvcoffee\Model' ) )
{
  $dir = dirname( __FILE__ );
  // This is a somewhat clumsy way to get the path relative to the plugin dir, as needed
  // by load_plugin_textdomain
  $dir = str_replace(WP_PLUGIN_DIR . '/', '', $dir);

  load_plugin_textdomain('mvcoffee-wp', false, $dir . '/languages' );

  $files = array(
    'validatable',
    'model',
    'array_field',
  );

  $path = plugin_dir_path( __FILE__ );

  foreach ($files as $file) {
    require_once $path . 'include/' . $file . '.php';
  }
}
