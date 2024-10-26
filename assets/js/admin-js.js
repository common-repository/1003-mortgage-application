jQuery(document).ready(function ($) {
	$(".color-picker").wpColorPicker();

	function split(val) {
		return val.split(/,\s*/);
	}
	function extractLast(term) {
		return split(term).pop();
	}
	/* Select application type*/
	jQuery(document).on("click", ".export-application .select_type", function () {
		if ($(this).prop("checked") == true && $(this).val() === "specific") {
			$(this).closest(".field-row").next(".field-row.specific-application").show();
		} else {
			$(this).closest(".field-row").next(".field-row.specific-application").hide();
		}
	});

	//autocompete application field search
	$("#application_field_search")
		.on("keydown", function (event) {
			if (event.keyCode === $.ui.keyCode.TAB && $(this).autocomplete("instance").menu.active) {
				event.preventDefault();
			}
		})
		.autocomplete({
			source: function (request, response) {
				jQuery.ajax({
					url: mortgage_application.ajax_url,
					type: "post",
					data: {
						action: "mortgage_application_autocomplete_fields_by_name",
						term: request.term,
						exists_terms: $("#application_fields option")
							.map(function () {
								return this.value;
							})
							.get(),
					},
					success: function (resp) {
						$(".application_fields").closest(".specific-application").find(".error").remove();
						$("#application_field_search").removeClass("ui-autocomplete-loading ui-autocomplete-input");
						if (resp.success) {
							response(resp.data);
						} else {
							$(".application_fields")
								.closest(".specific-application")
								.append('<p class="error">' + resp.data + "</p>");
							return false;
						}
					},
				});
			},
			search: function () {
				// custom minLength
				var term = extractLast(this.value);
				if (term.length < 2) {
					return false;
				}
			},
			focus: function () {
				// prevent value inserted on focus
				return false;
			},
			select: function (event, ui) {
				var terms = split(this.value);
				// remove the current input
				terms.pop();
				if (!$('#application_fields option[value="' + ui.item.value + '"]').prop("selected", true).length) {
					$("#application_fields").show();
					$("#application_fields").append(new Option(ui.item.label, ui.item.value));
					var selected = $("#application_fields option:selected")
						.map(function () {
							return this.value;
						})
						.get();
					selected.push(ui.item.value);
					$("#application_fields").val(selected);
					//terms.push( ui.item.label );
				}
				//terms.push( "" );
				this.value = "";
				return false;
			},
		});

	//activate licenses
	$(document).on("click", "#mortgage_app_active", function (e) {
		e.preventDefault();
		var key = $(".ma_license_key").val();
		var nonce = $(this).data("nonce");
		var button = $(this);
		$.ajax({
			url: mortgage_application.ajax_url,
			data: { licenses_key: key, action: "mortgage_application_activate_licenses_key", nonce_data: nonce },
			type: "POST",
			success: function (result) {
				console.log(result);
				//button.next("#message").remove();
				if (result.success) {
					//webhook_button.after('<div id="message" class="updated notice notice-success is-dismissible"><p>Send successfully.</p></div>');
					console.log(result.data);
				} else {
					//webhook_button.after('<div id="message" class="notice notice-error is-dismissible"><p>'+ result.data +'</p></div>');
					window.location.href = result.data.redirect_url;
				}
			},
			error: function (errorMessage) {
				console.log(errorMessage);
			},
		});
	});
	//activate licenses
	$(document).on("click", "#mortgage_app_deactivate", function (e) {
		e.preventDefault();
		var key = $(".ma_license_key").val();
		var nonce = $(this).data("nonce");
		var button = $(this);
		console.log(key);
		console.log(nonce);
		console.log(button);
		$.ajax({
			url: mortgage_application.ajax_url,
			data: { licenses_key: key, action: "mortgage_application_deactivate_licenses_key", nonce_data: nonce },
			type: "POST",
			success: function (result) {
				console.log(result);
				//button.next("#message").remove();
				if (result.success) {
					//webhook_button.after('<div id="message" class="updated notice notice-success is-dismissible"><p>Send successfully.</p></div>');
					console.log(result.data);
				} else {
					//webhook_button.after('<div id="message" class="notice notice-error is-dismissible"><p>'+ result.data +'</p></div>');
					window.location.href = result.data.redirect_url;
				}
			},
			error: function (errorMessage) {
				console.log(errorMessage);
			},
		});
	});
	//test send on webhooks
	$(document).on("click", "#mortgage_application_test_webhooks", function (e) {
		e.preventDefault();
		var nonce = $(this).data("nonce");
		var webhooks = $("textarea[name=mortgage_application_webhooks]").val();
		var test_webhook = $(this);
		$.ajax({
			url: mortgage_application.ajax_url,
			data: { action: "mortgage_application_send_test_webhooks_request", nonce_data: nonce, webhooks: webhooks },
			type: "POST",
			success: function (result) {
				test_webhook.next("#message").remove();
				if (result.success) {
					test_webhook.after('<div id="message" class="updated notice notice-success is-dismissible"><p>Send successfully.</p></div>');
					console.log(result.data);
				} else {
					test_webhook.after('<div id="message" class="notice notice-error is-dismissible"><p>' + result.data + "</p></div>");
					console.log(result.data);
				}
			},
			error: function (errorMessage) {
				console.log(errorMessage);
			},
		});
	});
	//send on webhook by application edit screen
	$(document).on("click", "input[name=send_on_webhook_button]", function (e) {
		e.preventDefault();
		var item_id = $(this).data("id");
		var nonce = $(this).data("nonce");
		var webhook_button = $(this);
		$.ajax({
			url: mortgage_application.ajax_url,
			data: { post_id: item_id, action: "mortgage_application_admin_send_on_webhook", nonce_data: nonce },
			type: "POST",
			success: function (result) {
				console.log(result);
				webhook_button.next("#message").remove();
				if (result.success) {
					webhook_button.after('<div id="message" class="updated notice notice-success is-dismissible"><p>Send successfully.</p></div>');
					console.log(result.data);
				} else {
					webhook_button.after('<div id="message" class="notice notice-error is-dismissible"><p>' + result.data + "</p></div>");
					console.log(result.data);
				}
			},
			error: function (errorMessage) {
				console.log(errorMessage);
			},
		});
	});

	//send reminder by application edit screen
	$(document).on("click", "#edit_post_reminder", function (e) {
		e.preventDefault();
		var item_id = $(this).data("id");
		var nonce = $(this).data("nonce");
		var reminder_button = $(this);
		$.ajax({
			url: mortgage_application.ajax_url,
			data: { post_id: item_id, action: "mortgage_application_admin_send_reminder", nonce_data: nonce },
			type: "POST",
			success: function (result) {
				console.log(result);
				reminder_button.next("#message").remove();
				if (result.success) {
					reminder_button.after('<div id="message" class="updated notice notice-success is-dismissible"><p>Message send successfully.</p></div>');
					console.log(result.data);
				} else {
					reminder_button.after('<div id="message" class="notice notice-error is-dismissible"><p>' + result.data + "</p></div>");
					console.log(result.data);
				}
			},
			error: function (errorMessage) {
				console.log(errorMessage);
			},
		});
	});
	//accept terms and conditions
	$(document).on("click", "#mortgage_application_admin_terms", function (e) {
		e.preventDefault();
		var nonce = $(this).data("nonce");
		var reminder_button = $(this);
		$.ajax({
			url: mortgage_application.ajax_url,
			data: { action: "mortgage_application_admin_terms_accept", nonce_data: nonce },
			type: "POST",
			success: function (result) {
				if (result.success && result.data) {
					console.log("reload");
					location.reload(true);
				}
			},
			error: function (errorMessage) {
				console.log(errorMessage);
			},
		});
	});
	/* Add Repeater field */
	jQuery(document).on("click", ".ma-add-button", function (e) {
		var content = '<div class="condition_table"><div class="condition_table_fields"><select class="arg1" name="key[]"><option value="">Select Any field</option>';

		jQuery.each(mortgage_application.post_meta, function (key, value) {
			content += '<option value="' + key + '">' + value + "</option>";
		});

		content += '</select></div><div class="condition_table_fields"><select name="compare[]" class="condition"><option value="contains">contains</option><option value="is">is</option></select></div><div class="condition_table_fields"><input type="text" value="" name="value[]" class="gform-filter-value"></div>';

		content += '<div class="condition_table_fields"><img class="ma-add-button "src="' + mortgage_application.plugin_path + 'assets/img/add.png" alt="Add a condition" title="Add a condition"> <img class="ma-remove-button" src="' + mortgage_application.plugin_path + 'assets/img/remove.png" alt="Remove a condition" title="Remove a condition"></div></div>';

		jQuery(".ma-field-filter").append(content);
	});
	/*Remove Repeater field */
	jQuery(document).on("click", ".ma-remove-button", function (e) {
		if (jQuery(".condition_table").length > 1) {
			jQuery(this).closest(".condition_table").remove();
		}
	});

	jQuery(document).on("click", "#mortgage_application_client_email_recipients_id", function (e) {
		if (jQuery("#mortgage_application_client_email_recipients_id").prop("checked") == true) {
			jQuery(".mortgage_application_client_email_recipients_cls").addClass("client_email_recipients_show");
			jQuery(".mortgage_application_client_email_recipients_cls").removeClass("client_email_recipients_hide");
		} else {
			jQuery(".mortgage_application_client_email_recipients_cls").addClass("client_email_recipients_hide");
			jQuery(".mortgage_application_client_email_recipients_cls").removeClass("client_email_recipients_show");
		}
	});
});

jQuery(document).ready(function ($) {
	jQuery(document).on("click", ".map_dwn_file_by_id", function (e) {
		e.preventDefault();
		var post_id = jQuery(this).data("fid");

		// Instead of using AJAX, we'll send a POST request via a hidden form that will trigger the download
		var form = jQuery("<form>", {
			action: mortgage_application.ajax_url,
			method: "post",
		})
			.append(
				jQuery("<input>", {
					type: "hidden",
					name: "action",
					value: "mortgage_application_download_file",
				})
			)
			.append(
				jQuery("<input>", {
					type: "hidden",
					name: "post_id",
					value: post_id,
				})
			);

		// Append the form to the body and submit it
		form.appendTo(document.body).submit();
	});
});
