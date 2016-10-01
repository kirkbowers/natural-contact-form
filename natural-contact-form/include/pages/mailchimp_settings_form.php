<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function display_page_mailchimp_settings_form() {
  // The plugin is guarenteed to have a reference to an existing edit form because it
  // is instantiated in `handle_edit_form_post` below, which is called on `init`.
  $mailchimp_settings_form = Plugin::$mailchimp_settings_form;
  
  // First try to see if there is an existing model in use from an attempted form 
  // submission.
  if (!$mailchimp_settings_form->containing_model) {
    // If there isn't, fetch the model fresh from the db.
    $mailchimp_settings_form->set_containing_model( ContactForm::find($_GET['id']) );
  }
  // Hang onto this ref for shorthand
  $form = $mailchimp_settings_form->containing_model;
  $form_url = edit_form_url($form);
    
  $settings = $mailchimp_settings_form->model;
    
  // Get the info about this MailChimp account
  $lists = array();
  try {
    $mailchimp = new \DrewM\MailChimp\MailChimp($settings->get('apikey'));

    $account = $mailchimp->get("/");
    
    if (! isset($account['account_name'])) {
      throw new \Exception('MailChimp account unreachable');
    }
    $account_name = $account['account_name'];
    
    $lists = $mailchimp->get("lists");
    $lists = $lists['lists'];
  } catch (\Exception $e) {
    $account_name = '';
  }
   
  $form_opts = array();

  if ($lists) {
    $list_values = array();
    foreach ($lists as $list) {
      $list_values[] = array(
        'name' => $list['id'],
        'title' => $list['name']
      );
    }

    $form_opts[] = array(
      'type' => 'radio',
      'name' => 'list',
      'values' => $list_values,
      'default' => $list_values[0]['name']
    );
    
    $form_opts[] = array(
      'type' => 'text',
      'name' => 'name_merge_field',
      'default' => 'NAME',
      'desc' => __('The MailChimp identifier for a list member\'s name.', 'natural-contact-form')
    );
  
    $form_opts[] = array(
      'type' => 'text',
      'name' => 'first_name_merge_field',
      'default' => 'FNAME',
      'desc' => __('The MailChimp identifier for a list member\'s first name.', 'natural-contact-form')
    );
  
    $form_opts[] = array(
      'type' => 'text',
      'name' => 'last_name_merge_field',
      'default' => 'LNAME',
      'desc' => __('The MailChimp identifier for a list member\'s last name.', 'natural-contact-form')
    );
  }  
  
?>
    
<div class="wrap">


  <h1>Natural Contact Form</h1>

  <h2><?php _e('MailChimp Integration', 'natural-contact-form') ?></h2>

<?php
  if ($mailchimp_settings_form->successful_save) {
?>
  <div class="notice notice-success is-dismissible">
    <p>MailChimp settings saved.</p>
  </div>
<?php
  }
?>

    <p><?php echo sprintf( __('MailChimp integration settings associated with the contact form <a href="' . $form_url . '" >%s</a>.', 'natural-contact-form'), $form->get('title') ) ?></p>
       
<?php
  if ($account_name) {
?>
    <p><?php echo sprintf( __('Currently using MailChimp account "%s". <a href="' . mailchimp_api_key_form_url($form) . '" >Change account</a>.', 'natural-contact-form'), $account_name ) ?></p>

<?php

    if ($lists) {
      $mailchimp_settings_form->render($form_opts);
    } else {
?>
    <p><strong>There are no email lists for this account.</strong>  Please log onto <a href="http://www.mailchimp.com" target="_mailchimp">MailChimp</a> and create a list.</p>
    
<?php    
    }
  } else {
?>

    <div class="notice notice-error"><?php _e('Either MailChimp is temporarily unreachable or the account for the provided API Key is no longer valid. <a href="' . mailchimp_api_key_form_url($form) . '" >Try a different API Key</a>.', 'natural-contact-form') ?></div>

<?php
  }
?>

</div> <!-- class="wrap" -->


<?php
}

function handle_mailchimp_settings_form_post() {
  $mailchimp_settings_form = 
    new \com\kirkbowers\editforms\ArrayFieldEditForm(
      Plugin::PREFIX . 'mailchimp_settings',
      Plugin::namespaced('MailChimpSettings'),
      Plugin::namespaced('ContactForm'),
      'email_list_settings',
      'MailChimp'
    );
  // Stash the reference away for use by display_page_edit_form above
  Plugin::$mailchimp_settings_form = $mailchimp_settings_form;
  
  $mailchimp_settings_form->handle_post();
}

add_action('admin_init', Plugin::namespaced('handle_mailchimp_settings_form_post'));

