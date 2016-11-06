<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function display_page_edit_form() {
  // The plugin is guarenteed to have a reference to an existing edit form because it
  // is instantiated in `handle_edit_form_post` below, which is called on `init`.
  $edit_form = Plugin::$edit_contact_form_form;
  
  // First try to see if there is an existing model in use from an attempted form 
  // submission.
  if (!$edit_form->model) {
    // If there isn't, fetch the model fresh from the db.
    $edit_form->model = ContactForm::find($_GET['id']);
  }
  // Hang onto this ref for shorthand
  $form = $edit_form->model;
  
  $form_opts = array();

  $form_opts[] = array(
    'type' => 'section',
    'name' => 'form_title_and_id',
    'title' => __('Form Title and ID', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'title',
    'desc' => __('This title does not appear anywhere site visitors see.  It is just for your reference, something to call this contact form by.', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'slug',
    'desc' => __('This is the short name for this contact form given to the shortcode as the id value.  See the example above.', 'natural-contact-form')
  );
    
  //--------------------------------
  

  $form_opts[] = array(
    'type' => 'section',
    'name' => 'form_appearance',
    'title' => __('Form Appearance', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'radio',
    'name' => 'name_fields',
    'default' => 'name',
    'values' => array(
      array(
        'name' => 'name',
        'title' => __('Name', 'natural-contact-form')
      ),
      array (
        'name' => 'first-and-last',
        'title' => __('First and Last', 'natural-contact-form')
      )
    )
  );
  
  $form_opts[] = array(
    'type' => 'radio',
    'name' => 'label_location',
    'default' => 'placeholder',
    'values' => array(
      array(
        'name' => 'before',
        'title' => __('Before', 'natural-contact-form')
      ),
      array (
        'name' => 'placeholder',
        'title' => __('As Placeholder', 'natural-contact-form')
      )
    )
  );

  $form_opts[] = array(
    'type' => 'text',
    'name' => 'required_indicator',
    'title' => __('Required Field Indicator', 'natural-contact-form'),
    'desc' => __('Characters to put after each required field\'s label, usually an asterisk.  Only appears if the label location is "Before".', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'name_label',
    'default' => 'Name'
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'first_name_label',
    'default' => 'First Name'
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'last_name_label',
    'default' => 'Last Name'
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'email_label',
    'default' => 'Email'
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'phone_label',
    'default' => 'Phone'
  );
  
  $form_opts[] = array(
    'type' => 'checkbox',
    'name' => 'display_phone',
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'message_label',
    'default' => 'Message'
  );
  
  $form_opts[] = array(
    'type' => 'checkbox',
    'name' => 'display_message',
    'desc' => __('If you are using this contact form as an opt-in for a lead magnet, you may not want to ask for a message.  All you need is the name and email address to collect the lead.  Uncheck this box to make the message text area go away for this form.', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'submit_label',
    'title' => __('Submit Button Label', 'natural-contact-form'),
    'default' => 'Submit'
  );
  
  //--------------------------------
  

  $form_opts[] = array(
    'type' => 'section',
    'name' => 'message_details',
    'title' => __('Message Details', 'natural-contact-form')
  );
      
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'sender_email',
    'title' => __('Sender Email', 'natural-contact-form'),
    'default' => 'donotreply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])),
    'desc' => __('This can be any valid email address, even your own, anything that works so your hosting puts the mail through.  The reply-to on any message you receive will be set correctly as the person who contacted you, so this value is merely a technicality.', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'subject',
    'title' => __('Subject', 'natural-contact-form'),
    'default' => sprintf(__('Contact from %s', 'natural-contact-form'), get_bloginfo('name')),
    'desc' => __('This will be the subject on all emails you recieve through this form.  If you use multiple contact forms for different purposes, make this unique so you know where the contact came from.', 'natural-contact-form')
  );
  

      
  //--------------------------------
  

  $form_opts[] = array(
    'type' => 'section',
    'name' => 'after_message_sent',
    'title' => __('After Message Sent', 'natural-contact-form')
  );
      
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'success_redirect',
    'title' => __('Page to visit afterwards', 'natural-contact-form'),
    'desc' => __('This is the url (without the site domain) of the page to visit after the message has been sent. If blank, the visitor will remain on the contact form page, but the form will be reset and the "Message to display afterward" will be displayed.', 'natural-contact-form')
  );
      
  $form_opts[] = array(
    'type' => 'textarea',
    'name' => 'success_message',
    'title' => __('Message to display afterwards', 'natural-contact-form'),
    'desc' => __('This is the message to display after the message has been sent.  This will only be displayed if the "Page to visit afterwards" is NOT set.', 'natural-contact-form'),
    'default' => __('Your message has been sent!', 'natural-contact-form')
    
  );

  $form_opts[] = array(
    'type' => 'checkbox',
    'name' => 'page_guard_test_mode',
    'desc' => __('By default, the Page Guard allows a visitor who has filled out the guarding contact form to visit the guarded page for a month after filling out the form. If you are testing a Page Guard, you likely don\'t want to wait a month to retest.  Checking this reduces the time to 10 seconds before resetting the guard.', 'natural-contact-form')
  );  


  //--------------------------------
  

  $form_opts[] = array(
    'type' => 'section',
    'name' => 'styling',
    'title' => __('Styling', 'natural-contact-form')
  );
      
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'space_below_text_fields',
    'desc' => __('This is how much space to give below the name and email text fields.  If a simple number is given, the unit is pixels.  You may also supply an explicit unit (eg. "em").', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'space_below_message',
    'desc' => __('This is how much space to give below the message text area.  If a simple number is given, the unit is pixels.  You may also supply an explicit unit (eg. "em").', 'natural-contact-form')
  );

  $form_opts[] = array(
    'type' => 'text',
    'name' => 'text_fields_width',
    'desc' => __('This is how wide at a maximum to make the name and email text fields.  If a simple number is given, the unit is pixels.  You may also supply an explicit unit (eg. "em").', 'natural-contact-form')
  );

  $form_opts[] = array(
    'type' => 'text',
    'name' => 'message_textarea_width',
    'desc' => __('This is how wide at a maximum to make the textarea for the message field.  If a simple number is given, the unit is pixels.  You may also supply an explicit unit (eg. "em").', 'natural-contact-form')
  );

  $form_opts[] = array(
    'type' => 'text',
    'name' => 'message_textarea_height',
    'desc' => __('This is how tall to make the textarea for the message field. If a simple number is given, the unit is pixels.  You may also supply an explicit unit (eg. "em").', 'natural-contact-form')
  );

  $form_opts[] = array(
    'type' => 'text',
    'name' => 'error_message_color',
    'desc' => __('This is the color to make the error message above the contact form, specified in 6 digit hexadecimal (eg. "ff0000" for red). It can be supplied with or without a leading hash ("#ff0000" and "ff0000" are equivalent).', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'error_label_color',
    'desc' => __('This is the color to make the labels for fields with errors, specified in 6 digit hexadecimal (eg. "ff0000" for red). It can be supplied with or without a leading hash.', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'error_text_field_color',
    'desc' => __('This is the color to make the borders around the text fields that have errors, specified in 6 digit hexadecimal (eg. "ff0000" for red). It can be supplied with or without a leading hash.', 'natural-contact-form')
  );
  

  $form_opts[] = array(
    'type' => 'textarea',
    'name' => 'extra_css',
    'desc' => __('Any arbitrary CSS you want to add.', 'natural-contact-form')
  );
  

  //--------------------------------
  

  $form_opts[] = array(
    'type' => 'section',
    'name' => 'email_list_integration',
    'title' => __('Email List Integration', 'natural-contact-form')
  );
  
  $form_opts[] = array(
    'type' => 'radio',
    'name' => 'email_list_provider',
    'default' => 'none',
    'values' => array(
      array(
        'name' => 'none',
        'title' => __('None', 'natural-contact-form')
      ),
      array (
        'name' => 'MailChimp',
        'title' => __('MailChimp', 'natural-contact-form')
      )
    )
  );
  
  $form_opts[] = array(
    'type' => 'placeholder',
    'name' => 'email_list_settings',
  );
  
?>

<div class="wrap">

  <h1>Natural Contact Form</h1>

  <h2><?php _e('Edit Form', 'natural-contact-form') ?></h2>
    
    <p><?php _e('The options below set how the contact form will be drawn.  The contact form will
    appear any place you put the shortcode:', 'natural-contact-form') ?></p>
    
    <p>
      <code>[natural-contact-form id="<?php echo $form->get('slug') ?>"]</code>
    </p>
    
    
    
<?php

  $edit_form->render($form_opts, "natural-contact-form-tabs");

?>

    <p class="submitbox"><a href="<?php echo delete_form_url($form) ?>" class="submitdelete deletion"><?php _e('Delete this contact form', 'natural-contact-form') ?></a></p>

</div> <!-- class="wrap" -->

<script id="natural_contact_form_json" type="text/json">
  <?php

 echo json_encode(array(
   'admin_url' => admin_url(),
   'contact_form_id' => $form->id(),
   'email_list_settings' => $form->get('email_list_settings'),
  ));
   
  ?>    
</script>

<?php
}

function handle_edit_form_post() {
  $edit_form = 
    new \com\kirkbowers\editforms\MVCoffeeModelEditForm(Plugin::PREFIX . 'edit', 
      Plugin::namespaced('ContactForm'));
  // Stash the reference away for use by display_page_edit_form above
  Plugin::$edit_contact_form_form = $edit_form;

  $edit_form->handle_post();
}

add_action('admin_init', Plugin::namespaced('handle_edit_form_post'));

