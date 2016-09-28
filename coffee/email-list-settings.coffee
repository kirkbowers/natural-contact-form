this.NaturalContactForm || (this.NaturalContactForm = {})

class NaturalContactForm.EmailProvider
  constructor: (@$row, @from_server) ->
    @$cell = @$row.find(".placeholder")

  needs_saving: ->
    @$cell.html(
      """
      <p>Please click "Save Changes" to configure #{@provider}.</p>
      """
    )    

    @$row.show()


class NaturalContactForm.MailChimp extends NaturalContactForm.EmailProvider
  constructor: ($row, from_server) ->
    super($row, from_server)
  
  provider: "MailChimp"
  
  display: ->
    settings = @from_server.email_list_settings
    admin_url = @from_server.admin_url
    contact_form_id = @from_server.contact_form_id

    if settings
      console.log "Settings = " + JSON.stringify(settings)

      @$cell.html( 
        """
        <p>
          There are settings for MailChimp.
          <a href="#{ admin_url }admin.php?page=natural_contact_form_mailchimp_api_key&id=#{ contact_form_id }">
          Configure</a>
        </p>
        """ 
      )
    else
      console.log("There are no settings")
      @$cell.html( 
        """
        <p>
          There are no settings for MailChimp.
          <a href="#{ admin_url }admin.php?page=natural_contact_form_mailchimp_api_key&id=#{ contact_form_id }">
          Configure</a>
        </p>
        """ 
      )
      
    @$row.show()


jQuery(document).ready( ($) ->
  $list_settings_row = $("tr#email_list_settings")
  $radio = $("input[name=email_list_provider]")
  original_value = $("input[name=email_list_provider]:checked").val()

  # Pull the json from the page if there is some embedded
  json = $("#natural_contact_form_json").html()

  parsed = null
  if json
    parsed = $.parseJSON(json)
  
  mailchimp = new NaturalContactForm.MailChimp($list_settings_row, parsed)


  display_list_settings = (provider) ->
    console.log "Provider is " + provider
    if provider is "MailChimp"
      if original_value is provider
        mailchimp.display()
      else
        mailchimp.needs_saving()        
    else
      $list_settings_row.hide() 

  display_list_settings(original_value)
  
  $radio.change( ->
    display_list_settings($(this).val())
  )
)
