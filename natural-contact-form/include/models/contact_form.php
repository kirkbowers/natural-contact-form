<?php
namespace com\kirkbowers\naturalcontactform;

class ContactForm extends \org\mvcoffee\Model {
  // This is a long table name, and with WordPress's table prefix it will be longer,
  // but it's < 50 characters.  Unless WP has an over 14 character prefix, we should be
  // safely under the 64 character limit.
  static protected function table_basename() {
    return Plugin::PREFIX . 'contact_forms';
  }

  static protected function version_option_name() {
    return  Plugin::PREFIX . 'contact_forms_version';
  }
  
  const VERSION = '0.1.2';

  static protected $fields = array(
    'title'                => 'tinytext NOT NULL',
    'slug'                 => 'tinytext NOT NULL',
    'name_fields'          => "varchar(32)  NOT NULL DEFAULT 'name'",
    'label_location'       => "varchar(32)  NOT NULL DEFAULT 'placeholder'",
    'required_indicator'   => "varchar(32)",
    'name_label'           => "varchar(128) DEFAULT 'Name'",
    'first_name_label'     => "varchar(128) DEFAULT 'First Name'",
    'last_name_label'      => "varchar(128) DEFAULT 'Last Name'",
    'email_label'          => "varchar(128) DEFAULT 'Email'",
    'display_message'      => "tinyint NOT NULL DEFAULT 1",
    'message_label'        => "varchar(128) DEFAULT 'Message'",
    'submit_label'         => "varchar(128) DEFAULT 'Submit'",
    'sender_email'         => "varchar(256)",
    'subject'              => "varchar(512)",
    'success_redirect'     => "varchar(512)",
    'success_message'      => "varchar(512)",
    'page_guard_test_mode' => "tinyint NOT NULL DEFAULT 0",
  );

}

ContactForm::types('display_message'     , 'boolean');
ContactForm::types('page_guard_test_mode', 'boolean');

ContactForm::displays('title'             , 'Contact Form Title');
ContactForm::displays('slug'              , 'Contact Form id');

ContactForm::validates('title', 'presence');
ContactForm::validates('title', 'uniqueness');

ContactForm::validates('slug', 'presence');
ContactForm::validates('slug', 'uniqueness');
