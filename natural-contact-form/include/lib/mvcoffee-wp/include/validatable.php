<?php
namespace org\mvcoffee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validatable is the base class of any kind of Object Relational Model (ORM) that needs
 * to provide Rails ActiveRecord-style self-validation meta-programming methods.
 * 
 * It doesn't quite provide the true "Class Macro" style `validates` method that Rails
 * does, as PHP doesn't allow for code execution during class declaration.  However, it
 * provides a close approximation: Class Macros that are called immediately _after_ 
 * class declaration.  This way Rails (and MVCoffee) style validations can be provided.
 */
class Validatable {
  
  protected static $types_hash    = array();
  protected static $displays_hash    = array();
  protected static $validations_hash = array();
  
  public $errors = array();
  public $errors_for_field = array();

  public static function types($field, $type) {
    $classname = get_called_class();
  
    if (!isset(static::$types_hash[$classname])) {
      static::$types_hash[$classname] = array();
    }
    static::$types_hash[$classname][$field] = $type;
  }

  public static function type_for_field($field) {
    $classname = get_called_class();
    if (isset(static::$types_hash[$classname][$field])) {
      return static::$types_hash[$classname][$field];
    } 
    
    return null;
  }


  /**
   * When validations are performed, if a field is invalid, its name is turned into
   * a human readable string to be included in the generated error message.
   * By default, the first letter is capitalized, and all underscores are converted to
   * spaces.
   * For example, the database column "first_name" would be converted to "First name"
   * for the purpose of reporting errors.
   * If you want a different display name, you can use the Class Macro `displays` to 
   * set the display name explicitly, just like in Rails Active Record.  
   * Since PHP doesn't support true Class Macro methods the way Ruby does (called 
   * _during_ class declaration), it must be called on the class just after declaration:
   *
   *     class Person extends \org\mvcoffee\Model {
   *       static protected $fields = array(
   *         'first_name' => 'varchar(128)',
   *         ...
   *       );
   *     }
   *
   *     Person::displays('first_name', 'Given name');
   *
   */
  public static function displays($field, $display) {
    $classname = get_called_class();
  
    if (!isset(static::$displays_hash[$classname])) {
      static::$displays_hash[$classname] = array();
    }
    static::$displays_hash[$classname][$field] = $display;
  }

  public static function display_for_field($field) {
    $classname = get_called_class();
    if (isset(static::$displays_hash[$classname][$field])) {
      $name = static::$displays_hash[$classname][$field];
    } else {
      $name = ucwords(str_replace("_", " ", $field));
    }
    
    return __($name, 'mvcoffee-wp');
  }


  /**
   * `validates` mimics the Class Macro in Rails Active Record for adding validations
   * to a model.  As discussed in the comment above concerning the `displays` method,
   * it's not a true Class Macro in the Ruby sense, so must be called immediately after
   * class declaration:
   *
   *     class Person extends \org\mvcoffee\Model {
   *       static protected $fields = array(
   *         'first_name' => 'varchar(128) NOT NULL',
   *         ...
   *       );
   *     }
   *
   *     Person::validates('first_name', 'presence');
   *
   */
  public static function validates($field, $test, $options = null) {
    $classname = get_called_class();
  
    if (!isset(static::$validations_hash[$classname])) {
      static::$validations_hash[$classname] = array();
    }
    static::$validations_hash[$classname][] = array(
      'field'   => $field,
      'test'    => $test,
      'options' => $options
    );
  }
  
  public static function get_validations() {
    $classname = get_called_class();
    return static::$validations_hash[$classname];
  }
  
  public function validate() {
    $validations = static::get_validations();
    
    $this->errors = array();
    foreach ($validations as $validation) {
      $field = $validation['field'];
      $value = $this->get($field);
      $test = $validation['test'];
      $options = $validation['options'];
      
      if ($test == 'presence') {
        if ((null === $value) || !preg_match('/\S/', $value)) {
          $this->_add_error($field, '%s must not be blank', $options);
        }
      } else if ($test == 'uniqueness') {
        $invalid = false;
        
        if ($this->is_new_record()) {
          // If this is a new record, that is, it has never been saved to the database,
          // then any match existing in the database at all means it's already been 
          // taken, so a cheap count > 0 will do to test it.
          if (static::count(array( $field => $value )) > 0) {
            $invalid = true;
          }
        } else {
          // Otherwise, we can't know easily if any match in the database is just this
          // record already saved from before, or some other record that already had 
          // this value before this record has been saved.  We have to compare primary
          // keys.
          $matches = static::where(array( $field => $value ));
          
          foreach ($matches as $match) {
            if ($match->id() != $this->id()) {
              $invalid = true;
            }
          }
        }
            
        if ($invalid) {
          $this->_add_error($field, '%s has already been taken', $options);
        }
      }

      // TODO:
      // To mimic ActiveRecord and MVCoffee, we need to do all the possible tests,
      // like 'numericality', plus the option of a custom test by function name.
      // I don't need that for this plugin, so I can defer it...
      
      else {
        $function = array($this, $test);
        if (isset($options['message'])) {
          $message = $options['message'];
        } else {
          $message = '%s is invalid';
        }
        
        if (! $function($value)) {
          $this->_add_error($field, $message, $options);
        }
      }
    }
    
    return $this->is_valid();
  }
    
  private function _add_error($field, $message, $options) {
    $name = static::display_for_field($field);
    
    if (isset($options['message'])) {
      $message = $options['message'];
    }
    $error_message = sprintf(__($message, 'mvcoffee-wp'), $name);
    
    $this->errors[] = $error_message;
    if (!array_key_exists( $field, $this->errors_for_field )) {
      $this->errors_for_field[$field] = array();
    }
    $this->errors_for_field[$field][] = $error_message;
  }
  
  public function is_valid() {
    return empty($this->errors);
  }
}
