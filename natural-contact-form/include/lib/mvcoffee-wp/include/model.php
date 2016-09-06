<?php
namespace org\mvcoffee;

/**
 * This is admittedly a bit of overkill for this plugin as there is only one derived 
 * model from this base class, but it paves the way for a more general purpose solution
 * should it be needed by anyone in the future.
 *
 * This base class provides for a Rails ActiveRecord-style object relational model (ORM),
 * with methods for the usual create, read, update and delete (CRUD) operations.  All a
 * subclass of this base needs to provide are these four values:
 *
 * - a `static protected function table_basename()` that returns a string.  
 *   This will have the WordPress table prefix
 *   prepended to it for you.  The name needs to be unique in the WP 
 *   ecosystem.  Using a reverse URL style naming with underscores is recommended:
 *   (eg. `com_example_pluginname_model_name` with the model name pluralized).
 * - a `static protected function version_option_name()` that returns a string.  
 *   This will be the name of the option
 *   stored in the `wp_options` table to remember which version of this table is 
 *   currently installed in the system.  This allows for upgrading the table in future
 *   versions of the plugin.  Again, a reverse URL style naming recommended to avoid
 *   name clashes (eg. com_example_pluginname_model_name_version).
 * - a `const VERSION` of type string providing the version of this model.  It does not
 *   need to match the version number of the plugin, as it only needs to change when the
 *   model itself changes, which is likely to be rare.
 * - a `static protected $fields` of type array that maps the field names as keys to the
 *   SQL DDL to create that column in the database.  Eg.:
 *           'title'               => 'tinytext NOT NULL',
 *
 */
class Model {
  static protected function table_name() {
    global $wpdb;

    return $wpdb->prefix . static::table_basename();
  }

  //------------------------------------------------------------------------------
  //
  // Creating and upgrading the table

  static protected function create_or_alter_table() {
    global $wpdb;
    
    $installed_ver = get_option( static::version_option_name() );

    if ( $installed_ver != static::VERSION ) {
      $table_name = static::table_name();
  
      $charset_collate = $wpdb->get_charset_collate();

      $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,";

      foreach (static::$fields as $field_name => $options) {
        $sql .= "\n        $field_name $options,";
      }
        
      $sql .= "
        UNIQUE KEY id (id)
      ) $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
      
      update_option(static::version_option_name(), static::VERSION);
    }
  }
  
  static protected function drop_table() {
    global $wpdb;

    $table_name = static::table_name();
  
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "DROP TABLE IF EXISTS $table_name;";
  
    $wpdb->query($sql);

    delete_option(static::version_option_name(), static::VERSION);
  }
    
  static public function install() {
    static::create_or_alter_table();
  }

  static public function uninstall() {
    static::drop_table();
  }
  
  static public function upgrade() {
    static::create_or_alter_table();
  }
  
  //------------------------------------------------------------------------------
  //
  // Validations
  
  private static $types_hash    = array();
  private static $displays_hash    = array();
  private static $validations_hash = array();
  
  public $errors = array();
  public $errors_for_field = array();

  public static function types($field, $type) {
    $classname = get_called_class();
  
    if (!isset(self::$types_hash[$classname])) {
      self::$types_hash[$classname] = array();
    }
    self::$types_hash[$classname][$field] = $type;
  }

  public static function type_for_field($field) {
    $classname = get_called_class();
    if (isset(self::$types_hash[$classname][$field])) {
      return self::$types_hash[$classname][$field];
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
  
    if (!isset(self::$displays_hash[$classname])) {
      self::$displays_hash[$classname] = array();
    }
    self::$displays_hash[$classname][$field] = $display;
  }

  public static function display_for_field($field) {
    $classname = get_called_class();
    if (isset(self::$displays_hash[$classname][$field])) {
      $name = self::$displays_hash[$classname][$field];
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
  
    if (!isset(self::$validations_hash[$classname])) {
      self::$validations_hash[$classname] = array();
    }
    self::$validations_hash[$classname][] = array(
      'field'   => $field,
      'test'    => $test,
      'options' => $options
    );
  }
  
  public static function get_validations() {
    $classname = get_called_class();
    return self::$validations_hash[$classname];
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

    }
    
    return $this->is_valid();
  }
  
  private function _add_error($field, $message, $options) {
    $name = static::display_for_field($field);
    
    // TODO:
    // Do something with the options.  To mimic ActiveRecord and MVCoffee, we'd have to
    // respect custom messages.  I don't need that for the Natural Contact Form plugin,
    // so I can defer that until such a day as I do need it...
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
  
  //------------------------------------------------------------------------------
  
  protected $id;
  protected $new_record;
  public $values;
  
  /**
   * Mimics `new` in Rails.  Creates a new instance copying the values in the optionally
   * supplied array but does not save it to the database.
   */
  public function __construct($infields = array()) {
    $this->new_record = true;
    
    $this->set($infields);
  }
  
  /**
   * Mimics `new_record?` in Rails.  Returns true if this instance has never been saved
   * to the database.
   */
  public function is_new_record() {
    return $this->new_record;
  }

  /**
   * Mimics `create` in Rails.  Shorthand for `new` followed by `save`.
   * 
   * @return BaseModel The newly created model.  If it is invalid, the save will not be
   *    successful, but the model is still returned so its errors may be inspected.
   */
  public static function create($infields = array()) {
    // This is a bit of PHP metaprogramming magic
    $classname = get_called_class();
    $record = new $classname($infields);
    $record->save();
    return $record;
  }

  public static function count($constraints = null) {
    global $wpdb;
    
    $table_name = static::table_name();
    
    $where = self::_build_where($constraints);
    
		return $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where" );
  }
  
  public static function max($field) {
    global $wpdb;
    
    $table_name = static::table_name();
		return $wpdb->get_var( "SELECT MAX($field) FROM $table_name" );
  }
  
  public function set($infields) {
    foreach (static::$fields as $field_name => $options) {
      if (isset($infields[$field_name])) {
        if (static::type_for_field($field_name) == 'boolean') {
          $result = 0;
          $value = $infields[$field_name];

          if ($value) {
            $result = 1;
          }
          $this->values[$field_name] = $result;
        } else {
          $this->values[$field_name] = $infields[$field_name];
        }
      }
    }
  }
  
  public function save() {
	  global $wpdb;
  
    $table_name = static::table_name();

    if ($this->is_new_record()) {
      $result = $wpdb->insert( 
        $table_name, 
        $this->values
      );
      $this->new_record = false;
      if ($result) {
        // Re-find the record we just created so we can glean the id from it
        $new_row = static::find_by($this->values, 'id');
        $this->id = $new_row->id();
      
        return $this;
      } else {
        return null;
      } 
    } else {
      $wpdb->update(
        $table_name,
        $this->values,
        array('id' => $this->id)
      );
      return $this;
    }
  }
  
  protected static function _build_columns($columns) {
    if ($columns) {
      if (is_array($columns)) {
        $columns = implode(',', $columns);  
      }
      $columns = 'id,' . $columns;
    } else {
      $columns = '*';
    }
    
    return $columns;  
  }
  
  protected static function _build_where($constraints) {
	  global $wpdb;

    if (is_array($constraints)) {
      $where = array();
      foreach ($constraints as $field => $value) {
        if (is_float($value)) {
          $where[] = $wpdb->prepare("$field = %f", $value);
        } else if (is_int($value)) {
          $where[] = $wpdb->prepare("$field = %d", $value);
        } else {
          $where[] = $wpdb->prepare("$field = %s", $value);
        }
      }
    
      $where = implode(' AND ', $where);
    
      return "WHERE $where";
    } else {
      return "";
    }
  }
  
  public static function find($id, $columns = null) {
    return static::find_by(array('id' => $id), $columns);
  }
  
  public static function find_by($constraints, $columns = null) {
	  global $wpdb;
  
    $table_name = static::table_name();
  
    $columns = self::_build_columns($columns);
    
    $where = self::_build_where($constraints);
    
    $sql = "SELECT $columns from $table_name $where";
    // error_log($sql);

    $query_result = $wpdb->get_row($sql, ARRAY_A);
    
    // error_log(var_export($query_result, true));
  
    if ($query_result) {
      $classname = get_called_class();
      $result = new $classname($query_result);
      $result->id = $query_result['id'];
      $result->new_record = false;
      
      return $result;
    } else {
      return null;
    }
  }

  public static function all($columns = null) {
    return self::where(null, $columns);
  }

  public static function where($constraints, $columns = null) {
	  global $wpdb;
  
    $table_name = static::table_name();
  
    $columns = self::_build_columns($columns);
    
    $where = self::_build_where($constraints);
    
    $sql = "SELECT $columns from $table_name $where";
    // error_log($sql);

    $query_result = $wpdb->get_results($sql, ARRAY_A);
  
    // error_log(var_export($query_result, true));
    
    $result = array();
    $classname = get_called_class();
    
    if ($query_result) {
      foreach ($query_result as $row) {
        $record = new $classname($row);
        $record->id = $row['id'];
        $record->new_record = false;
        
        $result[] = $record;
      }
    } 
    
    return $result;
  }
  
  public static function delete($id) {
    global $wpdb;
    
    $table_name = static::table_name();

    $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
  }

  
  public function id() {
    return $this->id;
  }
  
  public function get($field_name) {
    if (isset($this->values[$field_name])) {
      return $this->values[$field_name];
    } else {
      return null;
    }
  }
}
