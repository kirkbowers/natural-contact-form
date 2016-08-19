<?php
namespace com\kirkbowers\naturalcontactform;

class Plugin {
  const VERSION = '0.2.0';

  private static $libs = array(
    'mvcoffee-wp',
    'edit-forms'
  );

  private static $files = array(
    'models/contact_form',
  
    'pages/all_forms',
    'pages/new_form',
    'pages/edit_form',
    
    'admin_menu',
    'routes',
    'shortcode',
    'page_guard',
    'enqueue_scripts',
    'install',
  );

  const ALL_FORMS_SLUG = 'natural_contact_form_all_forms';
  const NEW_FORM_SLUG  = 'natural_contact_form_new';
  const EDIT_FORM_SLUG = 'natural_contact_form_edit';

  // ModelEditForm objects need to be remembered throughout the entire request, even
  // though accessed within a couple of callback functions.  They gets stashed here.
  public static $edit_contact_form_form;
  public static $new_contact_form_form;
  public static $page_guard_form;

  public static $url;
  public static $path;

  /**
   * The PHP namespace mechanism is the preferred way to make the names of classes and
   * functions unique from those in other plugins.  However, there are still places that
   * name clashes may occur, like HTML form identifying hidden field names and nonces,
   * and the names of custom database tables.  If these are all prefixed with a reverse
   * URL, they are guarenteed to be unique.
   */
  const PREFIX = 'com_kirkbowers_naturalcontactform_';

  /**
   * All WordPress hooks take a "callable" for the function to be called.  All of this
   * plugin's functions to be called are namespaced.  Typing out the full namespace
   * longhand everytime I pass a callable to a hook would get tedious and error prone.
   * This function builds the namespaced version of the callable for you.  For a
   * standalone function, just call it on the function name:
   *
   *     function do_something() {
   *     }
   *
   *     add_action('some_hook', Plugin::namespaced('do_something'));
   *
   * For static class methods supplied as a callable array, only namespace the class name:
   *
   *     class MyClass {
   *       public static function do_something() {
   *       }
   *     }
   *
   *     add_action('some_hook', array( Plugin::namespaced('MyClass'), 'do_something' ));
   *
   */
  public static function namespaced($name) {
    return '\com\kirkbowers\naturalcontactform\\' . $name;
  }



  public static function start($inpath) {
    self::$path = plugin_dir_path($inpath);
    self::$url = plugins_url( '', $inpath );
  
    foreach (self::$libs as $lib) {
      require_once self::$path . 'include/lib/' . $lib . '/' . $lib . '.php';
    }
  
    foreach (self::$files as $file) {
      require_once self::$path . 'include/' . $file . '.php';
    }
  }
}
