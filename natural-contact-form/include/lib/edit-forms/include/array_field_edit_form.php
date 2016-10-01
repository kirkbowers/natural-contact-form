<?php
namespace com\kirkbowers\editforms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class provides an easy way to build an input form for an array field within 
 * a larger model.  It's for editing a model within a model.  Array fields are 
 * eventually serialized to be stored in the database.  This class needs a reference to
 * the ORM model of the array field in question, plus a reference to the ORM model of 
 * the containing model, the actual database table in which this serialized array is to 
 * be stored.
 */
class ArrayFieldEditForm extends BaseEditForm {
  public $redirect_on_save = null;
  public $successful_save = false;

  public $model = null;
  public $model_type = null;
  public $containing_model = null;
  public $containing_model_type = null;
  public $field = null;
  public $key = "";

  public function __construct($name, $model_type, $containing_model_type, $field, $key = "") {
    $this->name = $name;
    $this->model_type = $model_type;
    $this->containing_model_type = $containing_model_type;
    $this->field = $field;
    $this->key = $key;
    
    $this->model = new $model_type();
  }
  
  public function set_containing_model($containing_model) {
    $this->containing_model = $containing_model;

    $value = $this->containing_model->get($this->field);
    if (!$value) {
      $value = array();
    }
    
    if ($this->key && isset($value[$this->key])) {
      $this->model->set($value[$this->key]);
    } else {
      $this->model->set($value);
    }
    
    return $value;
  }
  
  protected function get_errors_for_field($field) {
    if ($this->model && isset($this->model->errors_for_field[$field])) {
      return $this->model->errors_for_field[$field];
    } else {
      return false;
    }
  }
   
  public function handle_post() {
    if (form_was_posted($this->name)) {
      if (isset($_POST['id'])) {
        error_log("Containing model id = " . $_POST['id']);
        $function = array($this->containing_model_type, 'find');
        $value = $this->set_containing_model( $function($_POST['id']) );
      } else {
        $value = $this->set_containing_model( new $this->containing_model_type() );
      }
      
      $this->model->set($_POST);
  
      if ($this->model->validate()) {
        if ($this->key) {
          $value[$this->key] = $this->model->values;
        } else {
          $value = $this->model->values;
        }
        
        error_log("New array value = " . var_export($value, true));
        $this->containing_model->set(array($this->field => $value));
        $this->containing_model->save();
        
        $this->successful_save = true;
        if ($this->redirect_on_save) {
          $funcname = $this->redirect_on_save;
          wp_redirect($funcname($this->containing_model));
          exit;
        } 
      } else {
      
      }
    }      
  }
  
  
  public function render($fields, $tabs = false) {
    // $this->handle_post();
  
    $this->open_section = false;
    
    if (! $this->model->is_valid()) {
      foreach ($this->model->errors as $error) {
    ?>
      <div class="notice notice-error"><?php echo $error ?></div>
    <?php
      }
    ?>
  </ul>
    <?php
    }
    
    ?>
<form name="form" method="post"> 

  <?php echo_form_post_marker($this->name) ?>

  <?php
    if ($this->containing_model->id()) {
  ?>
  <input type="hidden" name="id" value="<?php echo $this->containing_model->id() ?>">
  <?php
    }
    
    $this->render_all_fields($fields, $tabs);

    ?>
  <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'edit-forms') ?>"  /></p>
</form>
    <?php
  }
  
  
  protected function get_label_for_field($field) {
    return $this->model->display_for_field($field['name']);
  }
  
  protected function get_value_for_field($field) {
    return $this->model->get($field['name']);
  }
  
}
