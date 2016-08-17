<?php
namespace com\kirkbowers\naturalcontactform;

function install() {
  ContactForm::install();

  // This function can be called after the plugin has been installed, deactivated, then
  // reactivated.  We don't want to add the one default contact form if it already
  // exists.
  if (ContactForm::count() < 1) {
    ContactForm::create(
      array_merge(
        get_contact_form_defaults(),
        array(
          'title'            => 'Default',
          'slug'             => 'default',
        )
      )
    );
  }
}

function get_contact_form_defaults() {
  return array(
    'sender_email'     => 'donotreply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])),
    'subject'          => 'Contact from ' . get_bloginfo('name'),
    'success_message'  => 'Your message has been sent!',
  );
}

function uninstall() {
  ContactForm::uninstall();
}

// add_action('plugins_loaded', Plugin::namespaced('ContactForm::upgrade'));
add_action('admin_init', Plugin::namespaced('ContactForm::upgrade'));
