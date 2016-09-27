jQuery(document).ready( ($) ->
  $list_settings_row = $("tr#email_list_settings")

  display_list_settings = (provider) ->
    if provider is "MailChimp"
      $list_settings_row.show() 
    else
      $list_settings_row.hide() 

  display_list_settings("none")
  
  $("input[name=email_list_provider]").change( ->
    display_list_settings($(this).val())
  )
)
