jQuery(document).ready(function(){
	jQuery('.dspdl-customer-form-button > button').click(function(){
		// Make sure we have cleared the error message before we submit again
		jQuery('.dspdl-customer-form-message').html("").fadeOut('slow');
		// Get button saved in a variable for access later
		var btn = this;
		var input = jQuery('.dspdl-customer-form-code > input');
		var container = jQuery(input).parent().parent();
		var code = jQuery(input).val();
		if (!code) {
			jQuery('.dspdl-customer-form-message').html("Please enter a code to attach your device.").fadeIn('slow');
			return;
		}
		jQuery(btn).prop('disabled', true).parent().addClass('disabled');
		jQuery(input).prop('disabled', true).parent().addClass('disabled');

		// In order to make sure we can put the loader in and take the text out without screwing up the height of the button
		jQuery(".dspdl-customer-form-button > button").height(jQuery(".dspdl-customer-form-button > button").height());
		// Remove the button text so we don't have any overlayed text on our background loader
		jQuery(".dspdl-customer-form-button > button").html("").addClass('activateloader');
		jQuery.post(dspdl_ajax.ajax_url, {
			code: code,
			action: "dspdl_ajax_customer_code"
		}, function(res) {
			var result = JSON.parse(res);
			if (!result.success) {
				jQuery(btn).prop('disabled', false).parent().removeClass('disabled');
				jQuery(input).prop('disabled', false).parent().removeClass('disabled');
				jQuery(".dspdl-customer-form-button > button").html("Submit Code").removeClass('activateloader');
				jQuery('.dspdl-customer-form-message').html(result.message).fadeIn('slow');
				return;
			}
			jQuery(container).fadeOut('slow', function() {
				jQuery(container).html("<h3 class='dspdl-connection-success'>Your device has been successfully connected!</h3>").fadeIn('slow');
			});
		});
	});
	jQuery('.dspdl-customer-login-button').click(function(){
		window.location.href = dspdl_ajax.login_url;
	});
  function createCookie(name, value, days) {
      var expires;

      if (days) {
          var date = new Date();
          date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
          expires = "; expires=" + date.toGMTString();
      } else {
          expires = "";
      }
      document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/; domain=." + window.location.hostname + ";";
  }
});