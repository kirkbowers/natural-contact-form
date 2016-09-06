<?php
namespace com\kirkbowers\naturalcontactform;

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
