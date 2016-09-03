<?php
namespace com\kirkbowers\editforms;

class MVCoffeeModelEditForm extends BaseEditForm {
  public $redirect_on_save = null;

  private $model_type;
  public $model = null;

  public function __construct($name, $model_type) {
    $this->name = $name;
    $this->model_type = $model_type;
  }
  
   
  public function handle_post() {
    if (form_was_posted($this->name)) {
      if (isset($_POST['id'])) {
        $function = array($this->model_type, 'find');
        $this->model = $function($_POST['id']);
      } else {
        $this->model = new $this->model_type();
      }

      $this->model->set($_POST);
  
      if ($this->model->validate()) {    
        $this->model->save();
        
        if ($this->redirect_on_save) {
          $funcname = $this->redirect_on_save;
          wp_redirect($funcname($this->model));
          exit;
        }
      } else {
      
      }
    }      
  }
  
  
  public function render($fields) {
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
    if ($this->model->id()) {
  ?>
  <input type="hidden" name="id" value="<?php echo $this->model->id() ?>">
  <?php
    }
    
    $this->render_all_fields($fields);

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
