<?php
namespace com\kirkbowers\naturalcontactform;

function enqueue_scripts() {
  wp_enqueue_style('natural-contact-form-style', Plugin::$url . "/css/style.css", array(), Plugin::VERSION);

  wp_enqueue_script('jquery');
  wp_enqueue_script('natural-contact-form-script', Plugin::$url . "/js/natural-contact-form.js", array('jquery'), Plugin::VERSION);
}

add_action('wp_enqueue_scripts', Plugin::namespaced('enqueue_scripts'));


function admin_enqueue_scripts() {
  wp_enqueue_script('natural-contact-form-tabs-script', Plugin::$url . "/js/custom-nav-tabs.js", array(), Plugin::VERSION);
}

add_action('admin_enqueue_scripts', Plugin::namespaced('admin_enqueue_scripts'));
