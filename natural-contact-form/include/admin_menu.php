<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds all the menu choices for this plugin in the right nav of the WordPress 
 * dashboard.  Also registers the pages that are not accessible via the menu but are
 * linked to within the plugin's backend.
 */
function add_menu_pages() {
  add_menu_page(
    'Natural Contact Form', 
    'Natural Contact Form', 
    'manage_options', 
    Plugin::ALL_FORMS_SLUG, 
    Plugin::namespaced('display_page_all_forms'));
  add_submenu_page(
    Plugin::ALL_FORMS_SLUG, 
    __("All Contact Forms", 'natural-contact-page'), 
    __("All Forms", 'natural-contact-page'), 
    'manage_options', 
    Plugin::ALL_FORMS_SLUG, 
    Plugin::namespaced('display_page_all_forms'));
  add_submenu_page(
    Plugin::ALL_FORMS_SLUG, 
    __("Add New Contact Forms", 'natural-contact-page'), 
    __("Add New Form", 'natural-contact-page'), 
    'manage_options', 
    Plugin::NEW_FORM_SLUG, 
    Plugin::namespaced('display_page_new_form'));
  add_submenu_page(
    null, 
    __("Edit Contact Form", 'natural-contact-page'), 
    __("Edit Form", 'natural-contact-page'), 
    'manage_options', 
    Plugin::EDIT_FORM_SLUG, 
    Plugin::namespaced('display_page_edit_form'));
  add_submenu_page(
    null, 
    __("Delete Contact Form", 'natural-contact-page'), 
    __("Delete Form", 'natural-contact-page'), 
    'manage_options', 
    Plugin::DELETE_FORM_SLUG, 
    Plugin::namespaced('display_page_delete_form'));

  add_submenu_page(
    null, 
    __("Set MailChimp API Key", 'natural-contact-page'), 
    __("MailChimp API Key Form", 'natural-contact-page'), 
    'manage_options', 
    Plugin::MAILCHIMP_API_KEY_FORM_SLUG, 
    Plugin::namespaced('display_page_mailchimp_api_key_form'));
  add_submenu_page(
    null, 
    __("Set MailChimp Settings", 'natural-contact-page'), 
    __("MailChimp Settings Form", 'natural-contact-page'), 
    'manage_options', 
    Plugin::MAILCHIMP_SETTINGS_FORM_SLUG, 
    Plugin::namespaced('display_page_mailchimp_settings_form'));
}

add_action('admin_menu', Plugin::namespaced('add_menu_pages'));

