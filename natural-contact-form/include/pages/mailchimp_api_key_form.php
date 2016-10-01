<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function display_page_mailchimp_api_key_form() {
  // The plugin is guarenteed to have a reference to an existing edit form because it
  // is instantiated in `handle_edit_form_post` below, which is called on `init`.
  $mailchimp_api_key_form = Plugin::$mailchimp_api_key_form;
  
  // First try to see if there is an existing model in use from an attempted form 
  // submission.
  if (!$mailchimp_api_key_form->containing_model) {
    // If there isn't, fetch the model fresh from the db.
    $mailchimp_api_key_form->set_containing_model( ContactForm::find($_GET['id']) );
  }
  // Hang onto this ref for shorthand
  $form = $mailchimp_api_key_form->containing_model;
  $form_url = edit_form_url($form);
    
  $form_opts = array();
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'apikey',
    'desc' => __('The MailChimp API Key for your account.', 'natural-contact-form')
  );
  
  
?>

<div class="wrap">

  <h1>Natural Contact Form</h1>

  <h2><?php _e('MailChimp Integration', 'natural-contact-form') ?></h2>
    
    <p><?php echo sprintf( __('Please enter the API Key for the MailChimp account for which you wish to associate the contact form <a href="' . $form_url . '" >%s</a>.', 'natural-contact-form'), $form->get('title') ) ?></p>
       

<?php

  $mailchimp_api_key_form->render($form_opts);

?>

</div> <!-- class="wrap" -->


<?php
}

function handle_mailchimp_api_key_form_post() {
  $mailchimp_api_key_form = 
    new \com\kirkbowers\editforms\ArrayFieldEditForm(
      Plugin::PREFIX . 'mailchimp_apikey',
      Plugin::namespaced('MailChimpSettings'),
      Plugin::namespaced('ContactForm'),
      'email_list_settings',
      'MailChimp'
    );
  // Stash the reference away for use by display_page_edit_form above
  Plugin::$mailchimp_api_key_form = $mailchimp_api_key_form;
  
  $mailchimp_api_key_form->redirect_on_save = 
    Plugin::namespaced('mailchimp_settings_form_url');
  
  $mailchimp_api_key_form->handle_post();
}

add_action('admin_init', Plugin::namespaced('handle_mailchimp_api_key_form_post'));

