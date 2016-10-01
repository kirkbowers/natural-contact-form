<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function display_page_new_form() {
  // The plugin is guarenteed to have a reference to an existing edit form because it
  // is instantiated in `handle_new_form_post` below, which is called on `init`.
  $edit_form = Plugin::$new_contact_form_form;
  
  // First try to see if there is an existing model in use from an attempted form 
  // submission.
  if (!$edit_form->model) {
    // If there isn't, make a new model.
    $edit_form->model = new ContactForm();

    $new_id = ContactForm::max('id') + 1;
    $edit_form->model->set(
      array_merge(
        get_contact_form_defaults(),
        array(
          'title' => "Contact Form $new_id",
          'slug'  => "contact-form-$new_id"
        )
      )
    );
  }
  // Hang onto this ref for shorthand
  $form = $edit_form->model;
  
  $form_opts = array();

  $form_opts[] = array(
    'type' => 'text',
    'name' => 'title',
    'desc' => __('This title does not appear anywhere site visitors see.  It is just for your reference, something to call this contact form by.', 'natural-contact-page')
  );
  
  $form_opts[] = array(
    'type' => 'text',
    'name' => 'slug',
    'desc' => __('This is the short name for this contact form given to the shortcode as the id value.  See the example above.', 'natural-contact-page')
  );
    
  $form_opts[] = array(
    'type' => 'hidden',
    'name' => 'sender_email'
  );

  $form_opts[] = array(
    'type' => 'hidden',
    'name' => 'subject'
  );

  $form_opts[] = array(
    'type' => 'hidden',
    'name' => 'success_message'
  );

?>

<div class="wrap">

  <h1>Natural Contact Form</h1>
  <h2><?php _e('Create a New Form', 'natural-contact-page') ?></h2>
    
    
    
    
<?php

  $edit_form->render($form_opts);
?>

</div>

<?php
}

function handle_new_form_post() {
  $edit_form = new \com\kirkbowers\editforms\MVCoffeeModelEditForm(Plugin::PREFIX . 'new', 
    Plugin::namespaced('ContactForm'));
  // Stash the reference away for use by display_page_edit_form above
  Plugin::$new_contact_form_form = $edit_form;

  $edit_form->redirect_on_save = Plugin::namespaced('edit_form_url');

  $edit_form->handle_post();
}

add_action('admin_init', Plugin::namespaced('handle_new_form_post'));

