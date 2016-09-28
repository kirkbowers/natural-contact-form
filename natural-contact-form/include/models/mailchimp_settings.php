<?php
namespace com\kirkbowers\naturalcontactform;

class MailChimpSettings extends \org\mvcoffee\ArrayField {
  static protected $fields = array(
    'apikey',
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
