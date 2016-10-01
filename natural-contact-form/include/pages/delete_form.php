<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function display_page_delete_form() {
  $form = ContactForm::find($_GET['id']);
  
  if (! $form) {
    wp_redirect(all_forms_url());
    exit;
  }
?>

  <div class="wrap">
    <h1>Natural Contact Form</h1>
    <h2><?php _e('Delete Form', 'natural-contact-form') ?></h2>

    <p>
      <?php echo sprintf( __('Are you sure you want to delete form %s', 'natural-contact-form'), $form->get('title') ) ?>
    </p>

    <form name="deleteform" method="post"> 
      <?php echo_form_post_marker( Plugin::PREFIX . "delete_form" ) ?>

      <input type="hidden" name="id" value="<?php echo $form->id() ?>">

      <p class="submit"><input type="submit" name="submit" id="submit" class="button button-secondary" value="<?php _e('Yes, I\'m sure.  Delete this contact form.', 'natural-contact-form') ?>"  /></p>

    </form>
    
    <p>
      <a href="<?php echo all_forms_url() ?>">Cancel</a>
    </p>
  </div>
<?php

}

function handle_delete_form_post() {
  if (form_was_posted( Plugin::PREFIX . "delete_form" )) {
    ContactForm::delete( $_POST['id'] );
    wp_redirect(all_forms_url());
    exit;
  }
}

add_action('admin_init', Plugin::namespaced('handle_delete_form_post'));

