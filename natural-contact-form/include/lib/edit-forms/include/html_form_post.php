<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This file simply provides shortcuts for putting a nonce and form marker into an html
 * form and for checking the existence (and validaty) of those markers.
 */
function echo_form_post_marker($name) {
  echo get_form_post_marker($name);
}

function get_form_post_marker($name, $for_ajax = false) {
  $result = <<<EOF
  <input type="hidden" name="$name" value="true">
EOF;

  if ($for_ajax) {
    $result .= <<<EOF
  <input type="hidden" name="action" value="$name">
EOF;
  }

  $result .= wp_nonce_field($name, $name . '_nonce', true, false);
  
  return $result;
}

function form_was_posted($name) {
  if (isset($_POST[$name]) && $_POST[$name] == 'true') {
    if (isset($_POST[$name]) && check_admin_referer($name, $name . '_nonce')) {
      return true;
    }
  }
}
 