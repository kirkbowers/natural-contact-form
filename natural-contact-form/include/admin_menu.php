<?php
namespace com\kirkbowers\naturalcontactform;

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
}

add_action('admin_menu', Plugin::namespaced('add_menu_pages'));

