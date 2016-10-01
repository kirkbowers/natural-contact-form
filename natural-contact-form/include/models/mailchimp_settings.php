<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MailChimpSettings is a model within a model.  It provides a Rails-inspired ORM of the
 * array stored in the ContactForm email_list_settings field when MailChimp is used as
 * the email provider.
 */
class MailChimpSettings extends \org\mvcoffee\ArrayField {
  static protected $fields = array(
    'apikey',
    'list',
    'name_merge_field',
    'first_name_merge_field',
    'last_name_merge_field'
  );
  
  public function check_apikey($value) {
    try {
      $MailChimp = new \DrewM\MailChimp\MailChimp($value);
      $result = $MailChimp->get("/");
      if (isset($result['account_id'])) {
        return true;
      } else {
        return false;
      }
    } catch (\Exception $e) {
      return false;
    }
  }
}

MailChimpSettings::displays('apikey', 'API Key');

MailChimpSettings::validates('apikey', 'presence');
MailChimpSettings::validates('apikey', 'check_apikey', 
  array(
    'message' => '%s is an invalid API Key, or MailChimp is unreachable'
  )
);
