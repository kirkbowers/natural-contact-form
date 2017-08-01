<?php
namespace com\kirkbowers\naturalcontactform;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Shortcode class handles rendering the front end contact forms when the shortcode
 * is found, and handles submission of the contact form, sending mail and optionally
 * opting into an email list.
 */
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
          if (isset($_POST['phone'])) {
            $phone = sanitize_text_field($_POST['phone']);
      
            $message .= "Phone number: " . $phone . "\n\n";
          }
          if (isset($_POST['message'])) {
            $inmessage = sanitize_text_field($_POST['message']);
      
            $message .= $name . " said:\n\n" . $inmessage;
          } else {
            $message .= $contact_form->get('subject');
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
          
          // Do email service provider integration
          self::handle_email_service($contact_form);
                
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

  private static function px_units($value) {
    if (preg_match('/\\d/', substr($value, -1))) {
      $value .= 'px';
    }    

    return $value;
  }
  
  private static function format_color($value) {
    if (substr($value, 0, 1) != '#') {
      $value = '#' . $value;
    }    

    return $value;
  }
  
  private static function concat_style(&$selector, $rule, $value) {
    $selector[] = "  $rule: $value;\n";
  }
      


  private static function build_css($contact_form) {
    // I normally hate it when themes or plugins put style tags in the middle of the 
    // HTML that can't be overridden.  In this case, it makes sense, because it MUST
    // override everything if set, and it should only appear on the page using a 
    // particular contact form.  In other words, the styles here should not spill into
    // other pages.  We don't want to put it in the head of every page.  So adding it
    // inline makes sense.
  
    $result = "<style>\n";
  
    // Accumulators for the new style rules.
    $text = array();
    $textarea = array();
    $error_message = array();
    $error_label = array();
    $error_field = array();
 
    $text_margin = $contact_form->get('space_below_text_fields');
    $text_width  = $contact_form->get('text_fields_width');

    if ($text_margin) {
      self::concat_style($text, 'margin-bottom', self::px_units($text_margin));
    }
    
    if ($text_width) {
      self::concat_style($text, 'max-width', self::px_units($text_width));
      self::concat_style($text, 'width', '100%');    
    }

    if ($text) {
      $result .= "  .natural-contact-form input[type=\"text\"] {\n";

      $result .= implode($text);
    
      $result .= "}\n";
    }
  
    $message_margin = $contact_form->get('space_below_message');
    $message_width  = $contact_form->get('message_textarea_width');
    $message_height = $contact_form->get('message_textarea_height');
    
    if ($message_margin) {
      self::concat_style($textarea, 'margin-bottom', self::px_units($message_margin));
    }
    
    if ($message_width) {
      self::concat_style($textarea, 'max-width', self::px_units($message_width));
      self::concat_style($textarea, 'width', '100%');    
    }
    
    if ($message_height) {
      self::concat_style($textarea, 'height', self::px_units($message_height));
    }

    if ($textarea) {
      $result .= "  .natural-contact-form textarea {\n";

      $result .= implode($textarea);
    
      $result .= "}\n";
    }



    $error_message_color = $contact_form->get('error_message_color');
    if ($error_message_color) {
      self::concat_style($error_message, 'color', self::format_color($error_message_color));
    }

    if ($error_message) {
      $result .= "  .natural-contact-form-container p.error {\n";

      $result .= implode($error_message);
    
      $result .= "}\n";
    }


    $error_label_color = $contact_form->get('error_label_color');
    if ($error_label_color) {
      self::concat_style($error_label, 'color', self::format_color($error_label_color));
    }

    if ($error_label) {
      $result .= "  .natural-contact-form label.required-error {\n";

      $result .= implode($error_label);
    
      $result .= "}\n";
    }


    $text_error_color = $contact_form->get('error_text_field_color');
    if ($text_error_color) {
      self::concat_style($error_field, 'border-color', self::format_color($text_error_color));
    }

    if ($error_field) {
      $result .= "  .natural-contact-form input[type=\"text\"].required-error {\n";

      $result .= implode($error_field);
    
      $result .= "}\n";
    }

  
    if ($contact_form->get('extra_css')) {
      $result .= stripslashes($contact_form->get('extra_css')) . "\n";
    }
  
    $result .= "</style>\n";
    
    return $result;
  }
  
  public static function handle_email_service($contact_form) {
    $provider = $contact_form->get('email_list_provider');
    if ($provider == 'MailChimp') {
      $settings = $contact_form->get('email_list_settings');
      if (isset($settings['MailChimp'])) {
        $settings = $settings['MailChimp'];
      } else {
        return;
      }
            
      try {
        $mailchimp = new \DrewM\MailChimp\MailChimp($settings['apikey']);
      
        $list_id = $settings['list'];

        $values = array(
          'email_address' => sanitize_email($_POST['email']),
          'status'        => 'pending'
        );
      
        $merge_fields = array();
      
        if ($contact_form->get('name_fields') == 'first-and-last') {
          $merge_fields[ $settings['first_name_merge_field'] ] = 
            sanitize_text_field($_POST['first_name']);
          $merge_fields[ $settings['last_name_merge_field'] ] = 
            sanitize_text_field($_POST['last_name']);
        } else {
          $merge_fields[ $settings['name_merge_field'] ] = 
            sanitize_text_field($_POST['contact_name']);
        }
      
        $values['merge_fields'] = $merge_fields;

        $result = $mailchimp->post("lists/$list_id/members", $values);
      
        error_log("MailChimp add email to list: " . var_export($result, true));
      } catch (\Exception $e) {
        error_log("MailChimp add email threw exception: " . $e);
      }
    }
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
    // The class needs a reference to this model record, but I've got a local copy
    // for convenience since self:: gets really tedious after a while....
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
    if ($contact_form->get('display_phone')) {
      $result .= self::labeledText('phone', $contact_form->get('phone_label'), $errors);
    }
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
      $error_class = ' required-error error';
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

