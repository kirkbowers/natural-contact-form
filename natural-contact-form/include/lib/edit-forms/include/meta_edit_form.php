<?php
namespace com\kirkbowers\editforms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class provides an easy way to create an input form of meta values on an
 * "Edit Page" screen in the WordPress dashboard.
 */
class MetaEditForm extends BaseEditForm {
  public $post_id;
  
  public function __construct($name, $post_id) {
     $this->name = $name;
     $this->post_id = $post_id;
  }

  public function render($fields) {
    $this->open_section = false;
    
    echo_form_post_marker($this->name);
    $this->render_all_fields($fields);
  }

  protected function get_value_for_field($field) {
    return get_post_meta($this->post_id, $field['name'], true);
  }
  
  public function handle_post($fields) {
    if (form_was_posted($this->name)) {
      foreach ($fields as $field) {
        if (in_array($field['type'], $this->fields_with_values)) {
          $value = $_POST[$field['name']];
          if ($value) {
            update_post_meta($this->post_id, $field['name'], $value);
          } else {
            delete_post_meta($this->post_id, $field['name']);
          }
        }
      } 
      
      // Return true that the form was posted
      return true;
    }  else {
      return false;
    }
  }
}