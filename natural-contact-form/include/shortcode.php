<?php
namespace com\kirkbowers\naturalcontactform;

class Shortcode {
  private static $message = '';
  private static $error_message = '';
  private static $contact_form;
  
  private static function flash_cookie_name() {
    return Plugin::PREFIX . "flash";
  }
  
  public static function cookie_name($slug) {
    return Plugin::PREFIX . 'success_' . $slug;
  }

  public static function set_flash_error_message($slug, $message) {
    self::set_flash_value(self::error_message_flash_name($slug), $message);
  }

  public static function error_message_flash_name($slug) {
    return Plugin::PREFIX . 'error_' . $slug;
  }
  
  public static function get_flash_value($name) {
    if (isset($_COOKIE[self::flash_cookie_name()]) && isset($_COOKIE[$name])) {
      return $_COOKIE[$name];
    } else {
      return null;
    }
  }
  
  public static function age_flash() {
    $name = self::flash_cookie_name();
    
    if (isset($_COOKIE[$name])) {
      $value = $_COOKIE[$name] - 1;

      if ($value > 0) {
        $_COOKIE[$name] = $value;
        setcookie($name, $value, time() + 30, "/");    
      } else {
        unset($_COOKIE[$name]);
        setcookie($name, null, -1, "/");
      }
    }
  }

  public static function set_flash_value($name, $value) {
    if ($value) {
      // 30 seconds seems long enough for a flash value
      setcookie($name, $value, time() + 30, "/");
      setcookie(self::flash_cookie_name(), 2, time() + 30, "/");
    } else {
      unset($_COOKIE[$name]);
      setcookie($name, null, -1, '/');
    }
  }

  public static function handle_post() {
    // We always want to age the flash, even if this page load isn't a post.
    // This is just a convenient place to handle this without adding another action.
    self::age_flash();
  
    if (form_was_posted(Plugin::PREFIX . 'contactform')) {
      // First check if the honeypot was triggered.
      // Only proceed if it wasn't
      if ($_POST['website'] == '') {
        // Everything passes.  We should be able to find the contact form record
        // in the database now.
        $contact_form = ContactForm::find($_POST['id']);
      
        // First do the validations
        $valid = true;
        if ($contact_form->get('name_fields') == 'first-and-last') {
          if (!preg_match('/\S/', $_POST['first_name'])) {
            $valid = false;
          }
          if (!preg_match('/\S/', $_POST['last_name'])) {
            $valid = false;
          }
        } else {
          if (!preg_match('/\S/', $_POST['contact_name'])) {
            $valid = false;
          }
        }
      
        if (!preg_match('/\S/', $_POST['email'])) {
          $valid = false;
        }
    
        if (! $valid) {
          self::$error_message = 'Please fill in the required fields';
        } else {
          self::$error_message = '';

          if ($contact_form->get('name_fields') == 'first-and-last') {
            $name = $_POST['first_name'] . ' ' . $_POST['last_name'];
          } else {
            $name = $_POST['contact_name'];
          }
          $name = sanitize_text_field($name);
       
          $email = sanitize_email($_POST['email']);
          
          $message = "";
          if (isset($_POST['message'])) {
            $message = sanitize_text_field($_POST['message']);
      
            $message = $name . " said:\n\n" . $message;
          }
                
          $from = 'From: ' . $name . ' <' . $contact_form->get('sender_email') . '>';
      
          $to = get_option('admin_email');
          $reply_to = 'Reply-To: ' . $name . ' <' . $email . '>';
    
          if ($contact_form->get('page_guard_test_mode')) {
            // Testing mode, make the cookie expire in just 10 seconds so we can retest
            // being bounced soon after successfully filling out the form.
            // Plus, we don't send the mail in testing mode.
            setcookie(self::cookie_name($contact_form->get('slug')), 'true', 
              time() + 10, "/");
          } else {
            // Not testing mode.
            
            // Really send the mail.
            wp_mail($to, $contact_form->get('subject'), $message, array($from, $reply_to));
            
            // Do the default one month expiration on the page guard cookie.
            setcookie(self::cookie_name($contact_form->get('slug')), 'true', 
              time() + (86400 * 30), "/");
          }
                
          if ($contact_form->get('success_redirect') && preg_match('/\S/', $contact_form->get('success_redirect'))) {
            wp_redirect(get_site_url() . '/' . $contact_form->get('success_redirect'));
            exit;
          } else {
            self::$message = $contact_form->get('success_message');
          }
        } 
      } else {
        // self::$message = "Honeypot activated.";
      }
    }
  }

  private static function build_css($contact_form) {
    // I normally hate it when themes or plugins put style tags in the middle of the 
    // HTML that can't be overridden.  In this case, it makes sense, because it MUST
    // override everything if set, and it should only appear on the page using a 
    // particular contact form.  In other words, the styles here should not spill into
    // other pages.  We don't want to put it in the head of every page.  So adding it
    // inline makes sense.
  
    $result = "<style>\n";
  
    $value = $contact_form->get('space_below_text_fields');
    if ($value) {
      if (preg_match('/\\d/', substr($value, -1))) {
        $value .= 'px';
      }
        
      $result .= '  input[type="text"] { margin-bottom: ' . $value . "}\n";
    }
  
    $value = $contact_form->get('space_below_message');
    if ($value) {
      if (preg_match('/\\d/', substr($value, -1))) {
        $value .= 'px';
      }
        
      $result .= '  textarea { margin-bottom: ' . $value . "}\n";
    }
  
    if ($contact_form->get('extra_css')) {
      $result .= $contact_form->get('extra_css') . "\n";
    }
  
    $result .= "</style>\n";
    
    return $result;
  }

  public static function shortcode($args) {  
    $slug = null;
    if (is_array($args)) {
      $slug = $args['id'];
    }
      
    $constraints = null;
    
    if ($slug) {
      $constraints = array('slug' => $slug);
    }
    
    $contact_form = ContactForm::find_by($constraints);
    // The instance needs a reference to this model record, but I've got a local copy
    // for convenience since $this-> gets really tedious after a while....
    self::$contact_form = $contact_form;
  
    if (!$contact_form) {
      return "";
    }
  
    $result = "";
    $errors = false;
  
    $result .= self::build_css($contact_form);
  
    $result .= '<div class="natural-contact-form-container">';
  
    if (isset(self::$message) && self::$message != '') {
      $result .= '<p class="notice">' . self::$message . '</p>';
    }
    
    // Check and see if there is a "session" error message from the page guard
    $flash_name = self::error_message_flash_name($contact_form->get('slug'));
    $flash_value = self::get_flash_value($flash_name);
    if ($flash_value) {
      self::$error_message = $flash_value;
      // self::set_flash_value($flash_name, null);
    }
    
    $result .= '<p class="error">';
    if (isset(self::$error_message) && (self::$error_message != '')) {
      $result .= self::$error_message;
      $errors = true;
    }
    $result .= '</p>';

    $id = $contact_form->id();
    $result .= <<<EOF
 <form class="natural-contact-form" id="natural-contact-form" name="natural-contact-form" method='post'>
    <input type='hidden' name='id' value='$id' />  
EOF;
    $result .= get_form_post_marker(Plugin::PREFIX . 'contactform');
    
    if ($contact_form->get('name_fields') == 'first-and-last') {
      $result .= self::labeledText('first_name', $contact_form->get('first_name_label'), $errors);
      $result .= self::labeledText('last_name', $contact_form->get('last_name_label'), $errors);    
    } else {    
      $result .= self::labeledText('contact_name', $contact_form->get('name_label'), $errors);
    }
    $result .= self::labeledText('email', $contact_form->get('email_label'), $errors);
    $result .= self::labeledText('website', 'Website', $errors);
  
    $result .= <<<EOF
    <div class="form-label-and-textarea">
EOF;
    
    if ($contact_form->get('display_message')) {
      $result .=  '<label>' .  $contact_form->get('message_label') . '</label>';

      $result .= '<textarea id="natural-contact-message" name="message">';
      if ($errors) {
        if (isset($_POST['message'])) {
          $result .= esc_textarea($_POST['message']);
        }
      }
      $result .= '</textarea>';
    }
        
    $submit_label = $contact_form->get('submit_label');
    
    $result .= <<<EOF
    </div>
    <div class="actions">
      <input type="submit" value="$submit_label">
    </div>
  </form>  
  </div>
EOF;

    return $result;
  
  }

  private static function labeledText($id, $label, $errors) {
    $contact_form = self::$contact_form;
  
    $value = isset($_POST[$id]) ? $_POST[$id] : '';
    $error_class = '';
    if ($errors && !preg_match('/\S/', $value)) {
      $error_class = ' required-error';
    }
  
    $result = '<div class="form-label-and-field" id="form-label-and-field-' . $id . '">';
    
    if ($contact_form->get('label_location') == 'before') {
      $result .= '<label for="' . $id . '" class="required' . $error_class . '">' . $label;
      
      if ($contact_form->get('required_indicator') != '') {
        $result .= ' <span class="required-indicator">'. $contact_form->get('required_indicator') . '</span>';
      }
      
      
      $result .= '</label>';
    }
    $result .= '<input type="text" class="natural-contact' . $error_class . '" id="natural-contact-' . $id . '" name="' . $id . '"';
    if ($contact_form->get('label_location') == 'placeholder')  {
      $result .= ' placeholder="' . $label . '"';
    }
    if ($errors) {
      $result .= ' value="' . esc_attr($value) . '"';
    }
    $result .= '></div>';
    
    return $result;
  }
}

add_action('init', 
  array(Plugin::namespaced('Shortcode'), 'handle_post'));
add_shortcode('natural-contact-form', 
  array(Plugin::namespaced('Shortcode'), 'shortcode'));

