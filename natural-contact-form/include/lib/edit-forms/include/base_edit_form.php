<?php
namespace com\kirkbowers\editforms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BaseEditForm is the foundation for everything in this sub-plugin.  It gives an array
 * based mechanism for specifying the fields to appear in an input form and renders those
 * fields into HTML in a general purpose, WordPress dashboard-styled manner.
 */
class BaseEditForm {
  
  //===============================================================================
  // 
  // Methods to be overridden
  
  protected function get_label_for_field($field) {
    // This is here in case the overriding class is for a type of thing in which fields
    // know their own labels.  It's rare.  The only known subclass that can do this is
    // ModelEditClass as Models have a `displays` method that sets the display label
    // for fields and otherwise calculates the display label by replacing underscores 
    // with spaces.
    // Normally, you should provided a "title" for each field, so this should return 
    // blank to let you know you did something wrong.
    return "";
  }
  
  protected function get_value_for_field($field) {
    // This absolutely needs to be overridden.  This is effectively an abstract method.
    return "";
  }
  
  protected function get_errors_for_field($field) {
    // This should be overridden if your subclass form does validation.
    return null;
  }
  
  private function get_defaulted_value_for_field($field) {
    $result = $this->get_value_for_field($field);
    if (null === $result) {
      if (isset($field['default'])) {
        $result = $field['default'];
      } else {
        $result = '';
      }
    }
    return $result;
  }
  
  
  
  protected $fields_with_values = array(
    'text',
    'textarea',
    'radio',
    'checkbox',
    'hidden',
  );
  
  protected $open_section = false;
  
  protected function section_id_attr($field) {
    $id = "";
    if (isset($field['name'])) {
      $id = $field['name'];
    } else {
      $id = 'tab-' . $this->counter;
    }

    return $id;
  }
  
  protected function render_all_fields($fields, $tabs = false) {
    $this->counter = 0;
    $this->section_fields = array();
  
    if ($tabs) {
      // First, build the fields for sections hash
      $current_section = '';
      foreach ($fields as $field) {
        if ($field['type'] == 'section') {
          $current_section = $field['name'];
    
          $this->section_fields[$current_section] = array();
        } else {
          $this->section_fields[$current_section][] = $field['name'];
        }       
      }
    
    ?>
        <h2 class="nfc-nav-tab-wrapper">
    <?php
    
    
      foreach ($fields as $field) {
        if ($field['type'] == 'section') {
          $id = $this->section_id_attr($field);
          
          $title = "";
          if (isset($field['title'])) {
            $title = $field['title'];
          } else {
            $title = 'Tab ' . $this->counter;
          }
          
          $error_class = '';
          if (isset($this->section_fields[$field['name']])) {
            foreach ($this->section_fields[$field['name']] as $contained_field) {
              if ($this->get_errors_for_field($contained_field)) {
                $error_class = 'ncf-error';
              }
            }
          }          
    ?>
           <a href="#<?php echo $id ?>" class="nav-tab <?php echo $error_class ?>" id="<?php echo $id ?>-tab"><?php echo $title ?></a>
     
    <?php    
        
          $this->counter += 1;
        } 
      }
    ?>
      </h2>
      <div class="ncf-panel">
    <?php
    }
    
    $this->counter = 0;
  
    if ($fields[0]['type'] != 'section') {
      $this->render_section();
    }
  
    foreach ($fields as $field) {
      $method_name = 'render_' . $field['type'];
      if (is_callable(array($this, $method_name))) {
        $this->$method_name($field);
      } else {
        $this->render_unknown($field);
      }
    }
    
    // If there is an open section, close it before we close the form.
    if ($this->open_section) {
      echo "    </table>\n";
      echo "  </div>\n";
    }
       
    if ($tabs) {
      echo "</div> <!-- class='ncf-panel' -->\n";
    }
          
    // This shouldn't make any difference, but let's be thorough
    $this->open_section = false;
  }

  public function render_unknown($field) {
    $type = $field['type'];
    
    $error_message = sprintf(escape_html__('ERROR!!  Unknown field type %s!'), $type);
    ?>
      <div class="options_form_field">
        <p><?php echo $error_message ?></p>
      </div>
    <?php
  }

  public function render_section($field = array()) {
    // If there is an open section, close it before we open this one.
    if ($this->open_section) {
      echo "    </table>\n";
      echo "  </div>\n";
    }
    
    // Now, indicate that we're opening our own section
    $this->open_section = true;
    
    $id = $this->section_id_attr($field);
    $this->counter += 1;
    
    ?>
    <div class="form_section group" id="<?php echo $id ?>">
    <?php 
      if (isset($field['title']) && preg_match('/\S/', $field['title'])) {
    ?>
      <h3><?php echo $field['title'] ?></h3>
    <?php
      }
    ?>
      <table class="form-table">
    <?php
  }
  
  public function get_label($field) {
    if (isset($field['title'])) {
      $label = $field['title'];
    } else {
      $label = $this->get_label_for_field($field);
    }

    return $label;  
  }
  
  public function render_label($field) {
    $label = $this->get_label($field);
  
    $error_class = '';
    if ($this->get_errors_for_field($field['name'])) {
      $error_class = 'class="ncf-error"';
    }
    
    ?>
      <th scope="row">
        <label for="<?php echo $field['name'] ?>" <?php echo $error_class ?>><?php echo $label ?></label>
      </th>    
    <?php
  }
  
  public function render_text($field) {
    ?>
    <tr>
      <?php $this->render_label($field); ?>
      <td>
        <input type="text" name="<?php echo $field['name'] ?>" class="regular-text" value="<?php echo esc_attr($this->get_defaulted_value_for_field($field)) ?>" >
    <?php
    if (isset($field['desc'])) {
    ?>
      <p><?php echo $field['desc'] ?></p>
    <?php
    }
    ?>
      </td>
    </tr>
    <?php
  }
  
  public function render_textarea($field) {
    ?>
    <tr>
      <?php $this->render_label($field); ?>
      <td>
        <textarea name="<?php echo $field['name'] ?>" class="large-text ncf-textarea"><?php echo esc_textarea($this->get_defaulted_value_for_field($field)) ?></textarea>
    <?php
    if (isset($field['desc'])) {
    ?>
      <p><?php echo $field['desc'] ?></p>
    <?php
    }
    ?>
      </td>
    </tr>
    <?php
  }
  
  public function render_radio($field) {
    ?>
    <tr>
      <?php $this->render_label($field); ?>
      <td>
        <fieldset><legend class="screen-reader-text"><span><?php echo $this->get_label($field) ?></span></legend>
    <?php
    $field_value = $this->get_defaulted_value_for_field($field);
    foreach ($field['values'] as $value) {
    ?>
        <label title="<?php echo $value['name'] ?>">
        <input type="radio" name="<?php echo $field['name'] ?>" value="<?php echo $value['name'] ?>" <?php if ($field_value == $value['name']) echo ' checked' ?>><?php echo $value['title'] ?></label><br>
    <?php
    }

    if (isset($field['desc'])) {
    ?>
      <p><?php echo $field['desc'] ?></p>
    <?php
    }
    ?>
        </fieldset>
      </td>
    </tr>
    <?php
  }

  public function render_checkbox($field) {
    ?>
    <tr>
      <?php $this->render_label($field); ?>
      <td>
    <?php
    $field_value = $this->get_defaulted_value_for_field($field);
    ?>
        <input type="hidden" name="<?php echo $field['name'] ?>" value="">

        <input type="checkbox" name="<?php echo $field['name'] ?>" <?php if ($field_value == 1) echo ' checked' ?>>
    <?php

    if (isset($field['desc'])) {
    ?>
      <p><?php echo $field['desc'] ?></p>
    <?php
    }
    ?>
      </td>
    </tr>
    <?php
  }

  public function render_hidden($field) {
    ?>
    <input type="hidden" name="<?php echo $field['name'] ?>" value="<?php echo $this->get_defaulted_value_for_field($field) ?>">
    <?php
  }
  
  
  public function render_placeholder($field) {
    ?>
    <tr id="<?php echo $field['name'] ?>">
      <?php $this->render_label($field); ?>
      <td class="placeholder">
      </td>
    </tr>
    <?php
  }

}
