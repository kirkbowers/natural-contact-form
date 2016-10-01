<?php
namespace com\kirkbowers\naturalcontactform;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This file handles setting up the meta box to allow editing the page guard settings.
 * It also handles the actual page guarding on the front end, performing the redirect
 * it the required cookie isn't present.
 */
function add_page_guard_metabox() {
  add_meta_box( 
    'com_kirkbowers_naturalcontactform_pageguard',
    __( 'Natural Contact Form Page Guard', 'natural-contact-form' ),
    Plugin::namespaced('render_page_guard_metabox'),
    'page'
  );
}

function get_page_guard_fields() {
  $fields = array();
  
  $fields[] = array(
    'type' => 'text',
    'name' => 'com_kirkbowers_naturalcontactform_pageguard_form_slug',
    'title' => __( 'Contact Form ID', 'natural-contact-form' ),
    'desc' => __( 'This is the "id" of the contact form that must be filled out in order for visitors to be able to see this page.', 'natural-contact-form' )
  );
  
  $fields[] = array(
    'type' => 'text',
    'name' => 'com_kirkbowers_naturalcontactform_pageguard_redirect',
    'title' => __( 'Redirect', 'natural-contact-form' ),
    'desc' => __( 'This is the page that should be displayed instead if the visitor has not filled out the related contact form.  If this field is left blank, a "404 Page not found" will be displayed.  Otherwise, the broswer will be redirected to the URL supplied.  To redirect to the home page, simply put a forward slash / for this value.', 'natural-contact-form' )
  );

  $fields[] = array(
    'type' => 'text',
    'name' => 'com_kirkbowers_naturalcontactform_pageguard_error_message',
    'title' => __( 'Error Message', 'natural-contact-form' ),
    'desc' => __( 'The error message to display above the contact form if the Redirect is back to the form guarding this page.  The error message will <em>only</em> display on a page displaying the form with the Contact Form ID above.', 'natural-contact-form' )
  );

  return $fields;
}

function render_page_guard_metabox($post) {
?>
  <p><?php _e('If you would like this page to only be visible after a contact form has been successfully filled out, you can activate the page guard by supplying a "Contact Form ID" here', 'natural-contact-form') ?></p>
  
<?php

  $slug = get_post_meta($post->ID, 
    'com_kirkbowers_naturalcontactform_pageguard_form_slug', true);
  if ($slug) {
    if (! ContactForm::find_by(array('slug' => $slug))) {  
?>
  <p><?php _e('WARNING: The Contact Form ID supplied does not match any contact form in the system. <strong>This page is not being guarded!</strong>') ?>
  </p>  
<?php
    }
  }  
  
  $form = new \com\kirkbowers\editforms\MetaEditForm(
    'com_kirkbowers_naturalcontactform_pageguard', $post->ID);

  $form->render(get_page_guard_fields());
}


function handle_page_guard_form_post($post_id) {
  $form = new \com\kirkbowers\editforms\MetaEditForm(
    'com_kirkbowers_naturalcontactform_pageguard', $post_id);

  $was_posted = $form->handle_post(get_page_guard_fields());
  
  
  if ($was_posted) {
    $slug = $_POST['com_kirkbowers_naturalcontactform_pageguard_form_slug'];
   
    if (! ContactForm::find_by(array('slug' => $slug))) {
      update_option(Plugin::PREFIX . 'page_guard_error_slug', $slug);
    } 
  }
}

function display_page_guard_error() {
  $slug = get_option(Plugin::PREFIX . 'page_guard_error_slug');
  if ($slug) {
    $title = __("Page Guard", 'natural-contact-form');
    $message = sprintf( 
      __('The Contact Form ID "%1$s" does not match any contact form in the system.
         <strong>This page is not being guarded!</strong> Please check the spelling 
         of the ID %2$shere%3$s.', 'natural-contact-form'), 
      $slug, 
      '<a href="' . all_forms_url() . '" target="_new">', 
      '</a>');
?>
    <div class="notice notice-error is-dismissible">
      <h2><?php echo $title ?></h2>
      <p>
        <?php echo $message ?>
      </p>
    </div>

<?php
  
    update_option(Plugin::PREFIX . 'page_guard_error_slug', null);
  }
}

function handle_page_guard_redirect() {
  $post = get_post();
  
  if ($post) {
    $slug = get_post_meta($post->ID, 
      'com_kirkbowers_naturalcontactform_pageguard_form_slug', true);
    $redirect = get_post_meta($post->ID, 
      'com_kirkbowers_naturalcontactform_pageguard_redirect', true);
    $error_message = get_post_meta($post->ID, 
      'com_kirkbowers_naturalcontactform_pageguard_error_message', true);
  
    if ($slug) {
      if(!isset($_COOKIE[Shortcode::cookie_name($slug)])) {
        if ($redirect) {
          Shortcode::set_flash_error_message($slug, $error_message);
          wp_redirect(home_url($redirect));
          exit();
        } else {
          global $wp_query;
          $wp_query->set_404();
        }
      }
    }
  }
}

add_action( 'add_meta_boxes_page', Plugin::namespaced('add_page_guard_metabox') );
add_action( 'save_post', Plugin::namespaced('handle_page_guard_form_post'));
add_action( 'template_redirect', Plugin::namespaced('handle_page_guard_redirect'));
add_action( 'admin_notices', Plugin::namespaced('display_page_guard_error'));

