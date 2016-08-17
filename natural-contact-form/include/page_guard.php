<?php
namespace com\kirkbowers\naturalcontactform;

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

  return $fields;
}

function render_page_guard_metabox($post) {
?>
  <p><?php _e('If you would like this page to only be visible after a contact form has been successfully filled out, you can activate the page guard by supplying a "Contact Form ID" here', 'natural-contact-form') ?></p>
  
<?php
  
  $form = new \com\kirkbowers\editforms\MetaEditForm(
    'com_kirkbowers_naturalcontactform_pageguard', $post->ID);

  $form->render(get_page_guard_fields());
}


function handle_page_guard_form_post($post_id) {
  $form = new \com\kirkbowers\editforms\MetaEditForm(
    'com_kirkbowers_naturalcontactform_pageguard', $post_id);

  $form->handle_post(get_page_guard_fields());
}

function handle_page_guard_redirect() {
  $post = get_post();
  
  if ($post) {
    $slug = get_post_meta($post->ID, 
      'com_kirkbowers_naturalcontactform_pageguard_form_slug', true);
    $redirect = get_post_meta($post->ID, 
      'com_kirkbowers_naturalcontactform_pageguard_redirect', true);
  
    if ($slug) {
      if(!isset($_COOKIE[Shortcode::cookie_name($slug)])) {
        if ($redirect) {
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
