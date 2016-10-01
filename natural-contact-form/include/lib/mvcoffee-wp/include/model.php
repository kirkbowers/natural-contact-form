<?php
namespace org\mvcoffee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
class Model extends Validatable {
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
        } else if (static::type_for_field($field_name) == 'array') {
          $value = $infields[$field_name];
          if (is_array($value)) {
            $this->values[$field_name] = serialize($value);
          } else {
            $this->values[$field_name] = $value;
          }
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
      if (static::type_for_field($field_name) == 'array') {
        return unserialize($this->values[$field_name]);
      } else {
        return $this->values[$field_name];
      }
    } else {
      return null;
    }
  }
}
