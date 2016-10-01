jQuery(document).ready(function($) {
  $("form#natural-contact-form").submit(function() {
    var $form = $(this);
    var valid = true;

    var validate_presence = function(selector) {
      var $input = $form.find("#natural-contact-" + selector);
      var val = $input.val();
      if (! /\S+/.test(val)) {
        valid = false;
        $input.parent().find("label").addClass("required-error");
        $input.addClass("required-error");
        return false;
      }
      return true;
    }

    $("label").removeClass("required-error");
    $("input").removeClass("required-error");
    $("p.error").text("");

    if ($form.find("#natural-contact-first_name").length) {
      validate_presence("first_name");
      validate_presence("last_name");
    } else {
      validate_presence("contact_name");
    }
    
    validate_presence("email");

    if (!valid) {
      $("p.error").text("Please fill in the required fields");
      return false;
    } else {
      return true;
    }
  });
});
