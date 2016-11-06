<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ContactForm is the Rails-inspired ORM representation of the the Contact Form database
 * table.  Most of the magic is in the meta-programming methods at the bottom that 
 * handling typing and validating the individual fields.
 */
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
  
  const VERSION = '1.1.0';

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
    'display_phone'        => "tinyint NOT NULL DEFAULT 0",
    'phone_label'          => "varchar(128) DEFAULT 'Phone'",
    'display_message'      => "tinyint NOT NULL DEFAULT 1",
    'message_label'        => "varchar(128) DEFAULT 'Message'",
    'submit_label'         => "varchar(128) DEFAULT 'Submit'",
    'sender_email'         => "varchar(256)",
    'subject'              => "varchar(512)",
    'success_redirect'     => "varchar(512)",
    'success_message'      => "varchar(512)",
    'page_guard_test_mode' => "tinyint NOT NULL DEFAULT 0",
    
    'space_below_text_fields'  => "varchar(32)",
    'space_below_message'      => "varchar(32)",
    'text_fields_width'        => "varchar(32)",
    'message_textarea_width'   => "varchar(32)",
    'message_textarea_height'  => "varchar(32)",
    'error_message_color'      => "varchar(10)",
    'error_label_color'        => "varchar(10)",
    'error_text_field_color'   => "varchar(10)",
    'extra_css'                => "longtext",
    
    'email_list_provider'      => "varchar(32) DEFAULT 'none'",
    'email_list_settings'      => "longtext",
  );

}

ContactForm::types('display_phone'       , 'boolean');
ContactForm::types('display_message'     , 'boolean');
ContactForm::types('page_guard_test_mode', 'boolean');
ContactForm::types('email_list_settings' , 'array'  );

ContactForm::displays('title'             , 'Contact Form Title');
ContactForm::displays('slug'              , 'Contact Form id');

ContactForm::validates('title', 'presence');
ContactForm::validates('title', 'uniqueness');

ContactForm::validates('slug', 'presence');
ContactForm::validates('slug', 'uniqueness');
