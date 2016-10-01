<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function display_page_all_forms() {
  $forms = ContactForm::all(array('title', 'slug'));

?>
  <div class="wrap">
    <h1>Natural Contact Form</h1>
    <h2><?php _e('All Forms', 'natural-contact-form') ?>&nbsp;<a href="<?php echo new_form_url() ?>" class="page-title-action">New Form</a></h2>

    <p>
      <?php _e('Select a contact form below to edit its properties.', 'natural-contact-form') ?>
    </p>

    <table class="wp-list-table widefat fixed striped pages">
      <thead>
        <tr>
          <th><?php _e('Contact Form Title', 'natural-contact-form') ?></th>
          <th><?php _e('Contact Form id', 'natural-contact-form') ?></th>
        </tr>
      </thead>
      <tbody>

<?php
  foreach ($forms as $form) {
?>    
        <tr>
          <td>
            <a href="<?php echo edit_form_url($form->id()) ?>">
              <?php echo $form->get('title') ?>
            </a>
          </td>
          <td>
            <a href="<?php echo edit_form_url($form->id()) ?>">
              <?php echo $form->get('slug') ?>
            </a>
          </td>
        </tr>
<?php
  }
?>
      </tbody>
    </table>
  </div>  
<?php

}
