<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This file creates helper functions for building URL routes to the various pages
 * in the plugin's backend.  These helpers are inspired by Rails' *_path and *_url
 * functions.
 */
function all_forms_url() {
  return admin_url() . "admin.php?page=" . Plugin::ALL_FORMS_SLUG;
}

function new_form_url() {
  return admin_url() . "admin.php?page=" . Plugin::NEW_FORM_SLUG;
}

function edit_form_url($contact_form) {
  if ($contact_form instanceof ContactForm) {
    $id = $contact_form->id();
  } else {
    $id = $contact_form;
  }

  return admin_url() . "admin.php?page=" . Plugin::EDIT_FORM_SLUG  . "&id=" . $id;
}

function delete_form_url($contact_form) {
  if ($contact_form instanceof ContactForm) {
    $id = $contact_form->id();
  } else {
    $id = $contact_form;
  }

  return admin_url() . "admin.php?page=" . Plugin::DELETE_FORM_SLUG  . "&id=" . $id;
}

function mailchimp_api_key_form_url($contact_form) {
  if ($contact_form instanceof ContactForm) {
    $id = $contact_form->id();
  } else {
    $id = $contact_form;
  }

  return admin_url() . "admin.php?page=" . Plugin::MAILCHIMP_API_KEY_FORM_SLUG  . "&id=" . $id;
}

function mailchimp_settings_form_url($contact_form) {
  if ($contact_form instanceof ContactForm) {
    $id = $contact_form->id();
  } else {
    $id = $contact_form;
  }

  return admin_url() . "admin.php?page=" . Plugin::MAILCHIMP_SETTINGS_FORM_SLUG  . "&id=" . $id;
}
