var $mapply_validate = jQuery.noConflict();
$mapply_validate(function ($) {

	function MaValidateForm(form, element_obj) {
		form.validate({
			rules: {
				purpose: {
					required: true
				},
				home_description: {
					required: true
				},
				credit_rating: {
					required: true
				},
				property_use: {
					required: true
				},
				zip_code: {
					required: true,
				},
				first_time_buyer: {
					required: true
				},
				loan_purpose_purchase: {
					required: true
				},
				loan_vendor: {
					required: true
				},
				desired_rate_type: {
					required: true
				},
				employment_status: {
					required: true
				},
				late_payments: {
					required: true
				},
				bankruptcy: {
					required: true
				},
				foreclosure: {
					required: true
				},
				proof_of_income: {
					required: true
				},
				reverse_mortgage: {
					required: true
				},
				monthly_income: {
					required: true,
					number: true,
				},
				refinanced_before: {
					required: true
				},
				mailing_address: {
					required: true
				},
				agree_access: {
					required: true
				},
				email: {
					required: true,
					email: true
				},
				first_name: "required",
				last_name: "required",
				phone_number: {
					required: true,
					phoneUS: true,
				},
			},
			messages: {
				purpose: {
					required: "Please Select your purpose"
				},
				home_description: {
					required: "Please Select Home Description"
				},
				credit_rating: {
					required: "Please Select Your Credit Profile"
				},
				property_use: {
					required: "Please Select Property Use"
				},
				zip_code: {
					//required: "Please enter Property ZIP Code",
					required: "Please enter Property Address",
				},
				first_time_buyer: {
					required: "Please Select Are you a first-time home buyer field"
				},
				loan_vendor: {
					required: "Please Select 1st Mortgage With field"
				},
				loan_purpose_purchase: {
					required: "Please Select When Do You Plan to Purchase field"
				},
				desired_rate_type: {
					required: "Please Select Desired Type of Rate field"
				},
				employment_status: {
					required: "Please Select Employment status field"
				},
				bankruptcy: {
					required: "Please Select Any bankruptcy in the past 3 years field"
				},
				late_payments: {
					required: "Please Select Number of late mortgage payments in the past 12 months field"
				},
				foreclosure: {
					required: "Please Select Any foreclosure in the past 3 years field"
				},
				proof_of_income: {
					required: "Please Select Can you show proof of your income field"
				},
				mailing_address: {
					required: "Please Enter Current Mailing Address Street"
				},
				reverse_mortgage: {
					required: "Please Select Can we interest you in a reverse mortgage field"
				},
				monthly_income: {
					required: 'Please Enter Income Amount (Monthly Income)'
				},
				refinanced_before: {
					required: "Please Select Have you ever refinanced before field"
				},
				agree_access: {
					required: "Please Select disclaimer field"
				},
				email: "Please enter a valid email address",
				first_name: "Please enter First Name",
				last_name: "Please enter Last Name",
				phone_number: "Please enter a valid Phone Number",
			},
			errorPlacement: function (error, element) {
				if (element.is(":radio") || element.is(":checkbox")) {
					error.appendTo(element.parents(".field"));
				} else {
					error.insertAfter(element);
				}
			}
		});
		return form;
	}

	function MaAfterValidateCheck(form, element_obj) {
		if (form.valid() == true) {
			var bar_element = element_obj.closest('.mortgage-form-main-container').find('.mortgage-Progress-bar .bar');
			var bar_score = bar_element.find('.bar-score').data('score');
			//get progress data
			var progress = element_obj.closest('.mortgage-form').find('fieldset.active').data('progress');
			//get current score
			bar_score = parseInt(bar_score) + parseInt(progress);
			element_obj.closest('.mortgage-form').find('fieldset.active').removeClass('active').removeClass('right').removeClass('left').next('fieldset').addClass('active').addClass('right');
			//check required field validation
			if (!element_obj.closest('.mortgage-form').find('fieldset.active').next().is('fieldset')) {
				jQuery(this).hide();
				jQuery('.mortgage-form .action .button.prev').show();
				jQuery('.mortgage-form .action input.submit').show();
			}
			if (bar_score && bar_score > 100) {
				bar_score = 100;
			}
			//update progress bar
			bar_element.css('width', bar_score + '%');
			bar_element.find('.bar-score').html(bar_score + '%').data('score', bar_score);
			element_obj.closest('.mortgage-form').find('#application_status').val(bar_score);
			//send to top
			jQuery('html, body').animate({
				scrollTop: ((jQuery(".mortgage-form-main-container").offset().top) - 75)
			}, 500);
		} else {
			jQuery('html, body').animate({
				scrollTop: ((jQuery(".mortgage-form-main-container label.error").offset().top) - 150)
			}, 500);
		}
	}

	$('.mortgage-form .action .button.next').click(function () {
		var form = $(".mortgage-form");
		//console.log(form);
		var element_obj = jQuery(this);
		//var valForm = MaValidateForm(form, element_obj);
		var valFormCk = MaAfterValidateCheck(form, element_obj);
		//console.log(valFormCk);
	});
	//
	jQuery(document).on('click', '.mortgage-form .action .submit', function (e) {
		var form = $(".mortgage-form");
		var element_obj = jQuery(this);
		var valForm = MaValidateForm(form, element_obj);
		if (form.valid() == true) {
			//scroll to form top
			jQuery('html, body').animate({
				scrollTop: ((jQuery(".mortgage-form-main-container").offset().top) - 75)
			}, 500);
			var bar_element = element_obj.closest('.mortgage-form-main-container').find('.mortgage-Progress-bar .bar');
			var bar_score = bar_element.find('.bar-score').data('score');
			var progress = element_obj.closest('.mortgage-form').find('fieldset.active').data('progress');
			bar_score = parseInt(bar_score) + parseInt(progress);
			if (bar_score && bar_score > 100) {
				bar_score = 100;
			}
			//update progress bar
			bar_element.css('width', bar_score + '%');
			bar_element.find('.bar-score').html(bar_score + '%').data('score', bar_score);
			element_obj.closest('.mortgage-form').find('#application_status').val(bar_score);
			element_obj.closest('.mortgage-form').find('fieldset').removeClass('active').removeClass('right').removeClass('left');
			element_obj.closest('.mortgage-form').find('.action').hide();
			jQuery(".mortgage-form").submit();
		}
	});


	/*auto save after email submit*/
	jQuery(document).on('blur', '.mortgage-form fieldset #email', function (e) {
		var form = $(".mortgage-form");
		var element_obj = jQuery(this);
		var valForm = MaValidateForm(form, element_obj);
		if (form.valid() == true) {
			var bar_element = element_obj.closest('.mortgage-form-main-container').find('.mortgage-Progress-bar .bar');
			var bar_score = bar_element.find('.bar-score').data('score');
			var progress = element_obj.closest('.mortgage-form').find('fieldset.active').data('progress');
			var bar_score = parseInt(bar_score) + parseInt(progress);
			if (bar_score && bar_score > 100) {
				bar_score = 100;
			}
			element_obj.closest('.mortgage-form').find('#application_status').val(bar_score);
			jQuery(".mortgage-form").submit();
		}
	});
	//prev button click
	$('.mortgage-form .action .button.prev').click(function () {
		jQuery('.mortgage-form .action .button.prev').hide();
		jQuery('.mortgage-form .action input.submit').hide();
		var bar_element = jQuery(this).closest('.mortgage-form-main-container').find('.mortgage-Progress-bar .bar');
		var bar_score = bar_element.find('.bar-score').data('score');
		//get progress data
		var progress = jQuery(this).closest('.mortgage-form').find('fieldset.active').prev('fieldset').data('progress');
		//get current score
		bar_score = parseInt(bar_score) - parseInt(progress);

		jQuery(this).closest('.mortgage-form').find('fieldset.active').removeClass('active').removeClass('right').removeClass('left').prev('fieldset').addClass('active').addClass('left');
		if (!jQuery(this).closest('.mortgage-form').find('fieldset.active').prev().is('fieldset')) {
			jQuery(this).hide();
		}
		if (bar_score && bar_score > 100) {
			bar_score = 100;
		}
		//update progress bar
		bar_element.css('width', bar_score + '%');
		bar_element.find('.bar-score').html(bar_score + '%').data('score', bar_score);
		jQuery(this).closest('.mortgage-form').find('#application_status').val(bar_score);
		//send to top
		jQuery('html, body').animate({
			scrollTop: ((jQuery(".mortgage-form-main-container").offset().top) - 75)
		}, 500);
	});


});
