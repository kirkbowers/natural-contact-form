<?php
namespace org\mvcoffee;

class ArrayField extends Validatable {
  public $values;

  public function __construct($infields = array()) {
    $this->values = $infields;
  }
  
  public function set($infields) {
    foreach (static::$fields as $field_name) {
      if (isset($infields[$field_name])) {
        $this->values[$field_name] = $infields[$field_name];
      }
    }
  }

  public function get($field_name) {
    if (isset($this->values[$field_name])) {
      return $this->values[$field_name];
    } else {
      return null;
    }
  }
}
