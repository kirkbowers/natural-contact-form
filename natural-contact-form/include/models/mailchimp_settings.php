<?php
namespace com\kirkbowers\naturalcontactform;

class MailChimpSettings extends \org\mvcoffee\ArrayField {
  static protected $fields = array(
    'apikey',
  );
}

MailChimpSettings::displays('apikey', 'API Key');

MailChimpSettings::validates('apikey', 'presence');
