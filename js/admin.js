jQuery(".dspdl-device-login-settings").submit(function(e) {
  e.preventDefault();
  jQuery(".dspdl-device-login-settings :input").prop('readonly', true);
  jQuery(".dspdl-device-login-settings button").prop('disabled', true);
  var save_message = jQuery(".dspdl-device-login-settings").find(".save-message");
  jQuery(save_message).removeClass('dspdl-options-save-success').removeClass('dspdl-options-save-failure');
  var url = dspdl_ajax.ajaxurl;
  var dspdl_device_settings_save = jQuery.post(
    url,
    {
        'action': 'dspdl_save_admin_options',
        'dspdl_dsp_api_key': jQuery('[name=dspdl-device-login-api-key]').val()
    }
  );

  dspdl_device_settings_save.done(function (response) {
    jQuery(".dspdl-device-login-settings :input").prop('readonly', false);
    jQuery(".dspdl-device-login-settings button").prop('disabled', false);
    jQuery(save_message).html("Saved!").addClass('dspdl-options-save-success').fadeIn("fast", function() {
      setTimeout(function() {
        jQuery(save_message).fadeOut("fast", function() {
          jQuery(save_message).removeClass('dspdl-options-save-success').html("");
        });
      }, 3000);
    });
  });

  dspdl_device_settings_save.fail(function (response) {
    jQuery(".dspdl-device-login-settings :input").prop('readonly', false);
    jQuery(".dspdl-device-login-settings button").prop('disabled', false);
    var err = response.data.message;
    jQuery(save_message).html(err).addClass('dspdl-options-save-failure').fadeIn("fast");
  });
});