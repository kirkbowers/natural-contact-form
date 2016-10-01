<?php
/**
 * Plugin Name: Edit Forms
 * Description: This is a developer plugin that provides the building blocks for creating edit forms in various places in the admin.  At the moment it is incomplete, completely ad hoc for use within the Natural Contact Form plugin, only containing functionality needed specifically for that plugin.  It also supplies an edit form that defends on the mvcoffee-wp Model.
 * Version: 0.1.0
 * Author: Kirk Bowers
 * Author URI: http://kirkbowers.com
 * License: GPLv2
 * Text Domain: edit-forms
 * Domain Path: /languages
 */

// The reverse URL namespacing convention is not common in PHP that I've seen, but it
// is the norm in Java and iOS land.  I think it's a good convention, pretty much 
// guaranteeing namespace uniqueness, so I adopt it here.

if ( defined( 'ABSPATH' ) && ! class_exists( '\com\kirkbowers\editforms\BaseEditForm' ) )
{
  $dir = dirname( __FILE__ );
  // This is a somewhat clumsy way to get the path relative to the plugin dir, as needed
  // by load_plugin_textdomain
  $dir = str_replace(WP_PLUGIN_DIR . '/', '', $dir);

  load_plugin_textdomain('edit-forms', false, $dir . '/languages' );

  $files = array(
    'html_form_post',
    'base_edit_form',
    'meta_edit_form',
    'mvcoffee_model_edit_form',
    'array_field_edit_form'
  );

  $path = plugin_dir_path( __FILE__ );

  foreach ($files as $file) {
    require_once $path . 'include/' . $file . '.php';
  }
}
