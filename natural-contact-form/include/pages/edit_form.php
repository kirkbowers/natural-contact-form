<?php
namespace com\kirkbowers\naturalcontactform;

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
    'name' => 'form_appearance',
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
    'name' => 'message_label',
    'default' => 'Message'
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
    'default' => 'donotreply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']))
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'subject',
    'title' => __('Subject', 'natural-contact-form'),
    'default' => sprintf(__('Contact from %s', 'natural-contact-form'), get_bloginfo('name'))
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
    'desc' => __('This is the url (without the site domain) of the page to visit after the message has been sent.', 'natural-contact-form')
  );
      
  $form_opts[] = array(
    'type' => 'textarea',
    'name' => 'success_message',
    'title' => __('Message to display afterwards', 'natural-contact-form'),
    'desc' => __('This is the message to display after the message has been sent.  This will only be displayed if the "Page to visit afterwards" is NOT set.', 'natural-contact-form'),
    'default' => __('Your message has been sent!', 'natural-contact-form')
    
  );
  
?>


<h1>Natural Contact Form</h1>
<h2><?php _e('Edit Form', 'natural-contact-form') ?></h2>
    
    <p><?php _e('The options below set how the contact form will be drawn.  The contact form will
    appear any place you put the shortcode:', 'natural-contact-form') ?></p>
    
    <p>
      <code>[natural-contact-form id="<?php echo $form->get('slug') ?>"]</code>
    </p>
    
    
    
<?php

  $edit_form->render($form_opts);

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

