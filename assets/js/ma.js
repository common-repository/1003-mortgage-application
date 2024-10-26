var $mapply_main = jQuery.noConflict();
$mapply_main(function($) {

    if (jQuery('.mortgage-form fieldset.active').length == 0) {
        jQuery('.mortgage-form fieldset').first().addClass('active');
    }
    /*update fields base on purpose*/
    if (jQuery('.mortgage-form fieldset input[name=purpose]').val() != "") {
        var purpose = jQuery('.mortgage-form fieldset input[name=purpose]:checked').val();
        if (purpose && purpose == 'Home Refinance') {
            jQuery('.purpose-refi').show().find(".required").prop("required", true);
            jQuery('.purpose-purch').hide().find(".required").prop("required", false);
        } else {
            jQuery('.purpose-purch').show().find(".required").prop("required", true);
            jQuery('.purpose-refi').hide().find(".required").prop("required", false);
        }
    };

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


    jQuery(document).on('change', '.mortgage-form fieldset input[name=purpose]', function(e) {
        var purpose = jQuery(this).val();
        console.log(purpose);
        if (purpose && purpose == 'Home Refinance') {
            jQuery('.purpose-refi').show().find("input").prop("required", true).prop("disabled", false);
            jQuery('.purpose-purch').hide().find("input").prop("required", false).prop("disabled", true);
        } else {
            jQuery('.purpose-purch').show().find("input").prop("required", true).prop("disabled", false);
            jQuery('.purpose-refi').hide().find("input").prop("required", false).prop("disabled", true);
        }
    });
    /*click next button after option seleted*/
    jQuery(document).on('click', '.mortgage-form fieldset input[name=purpose], .mortgage-form fieldset input[name=home_description], .mortgage-form fieldset input[name=credit_rating], .mortgage-form fieldset input[name=property_use]', function(e) {
        setTimeout(function() {
            jQuery(".mortgage-form .action .button.next").click();
        }, 500);
    });
    /*update mailing address in display mailing address field*/
    /*jQuery(document).on('blur','.mortgage-form fieldset #mailing_address', function(e){
    	var mailing_address = jQuery(this).val();
    	jQuery('.mailing_address').html(mailing_address);

    });*/

    jQuery(document).on('click', 'fieldset .action .btn-step-prev', function(e) {
        jQuery(".mortgage-form .action .button.prev").click();
    });

    jQuery(document).on('click', 'fieldset .action .btn-step-next', function(e) {
        jQuery(".mortgage-form .action .button.next").click();
    });

    /*validate phone number*/
    jQuery.validator.addMethod("phoneUS", function(phone_number, element) {
        phone_number = phone_number.replace(/\s+/g, "");
        return this.optional(element) || phone_number.length > 9 && phone_number.match(/^(\+?1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
    }, "Please specify a valid phone number");
    /*validate zip code and get address*/

    var input = document.getElementById('zip_code');
    var autocomplete = new google.maps.places.Autocomplete(input);
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var data = jQuery("#zip_code").val();
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            'address': data
        }, function(results_out, status) {
            if (status == google.maps.GeocoderStatus.OK) {

                $('#property_location_1003').remove();
                $('.mortgage-form').append('<div id="property_location_1003"></div>');

                searched_address = results_out[0].formatted_address;
                jQuery("#zip_code").val(searched_address);
                jQuery('.zip_code').val(searched_address);
                jQuery('.zip_code').html(searched_address);

                var arrAddress = results_out[0].address_components;
                var i;
                for (i = 0; i < arrAddress.length; i++) {
                    var addressComp = arrAddress[i];
                    var mainType = addressComp.types[0];
                    $('#property_location_1003').append('<input type="hidden" value="' + addressComp.long_name + '" name="property_location_' + mainType + '_long_name">');
                    $('#property_location_1003').append('<input type="hidden" value="' + addressComp.short_name + '" name="property_location_' + mainType + '_short_name">');
                }

                jQuery.each(arrAddress, function(i, address_component) {
                    if (address_component.types[0] == "route") {
                        jQuery("#address").val(address_component.long_name);
                    } else if (address_component.types[0] == "locality" && address_component.types[1] == "political") {
                        jQuery("#city").val(address_component.long_name);
                    } else if (address_component.types[0] == "administrative_area_level_1" && address_component.types[1] == "political") {
                        jQuery("#state").val(address_component.long_name);
                    } else if (address_component.types[0] == "postal_code") {
                        jQuery("#zip_code_only").val(address_component.long_name);
                    }
                });


            }
        });
    });

    var input = document.getElementById('mailing_address');
    if(input){
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var data = jQuery("#mailing_address").val();
            var geocoder = new google.maps.Geocoder();

            geocoder.geocode({
                'address': data
            }, function(results_out, status) {
                if (status == google.maps.GeocoderStatus.OK) {

                    $('#mailing_address_1003').remove();
                    $('.mortgage-form').append('<div id="mailing_address_1003"></div>');
                    var searched_address = results_out[0].formatted_address;
                    var arrAddress = results_out[0].address_components;
                    var i;
                    for (i = 0; i < arrAddress.length; i++) {
                        var addressComp = arrAddress[i];
                        var mainType = addressComp.types[0];
                        $('#mailing_address_1003').append('<input type="hidden" value="' + addressComp.long_name + '" name="mailing_address_' + mainType + '_long_name">');
                        $('#mailing_address_1003').append('<input type="hidden" value="' + addressComp.short_name + '" name="mailing_address_' + mainType + '_short_name">');
                    }
                    jQuery('#mailing_address').val(searched_address);
                    jQuery('.mailing_address').html(searched_address);

                }
            });
        });
    }


    /*auto save after email submit*/
    jQuery(".mortgage-form").submit(function(e) {
        e.preventDefault();
        var form_element = jQuery(this);
        var form_data_array = jQuery(this).serialize();
        jQuery.ajax({
            type: 'POST', // http method
            url: mortgage_application.ajax_url,
            data: {
                form_data: form_data_array,
                action: 'mortgate_application_data_save'
            }, // data to submit
            success: function(result) {
                form_element.find('.success-message, .error-message').remove();
                if (result.success == false) {} else {
                    jQuery(document).on('click', 'fieldset .action .mail', function(e) {
                        var form = $(".mortgage-form");
                        var element_obj = jQuery(this);
                        var valFormCk = MaAfterValidateCheck(form, element_obj);
                    });
                }
                if (result.success) {
                    jQuery("#crud").val("ma_update");
                    jQuery("#rec_id").val(result.data.id);
                    if (result.data.message.check == "yes") {
                        form_element.append('<p class="success-message">' + result.data.message.msg + '</p>')
                        setTimeout(function() {
                            jQuery('.success-message').hide()
                        }, 3000);
                        /*jQuery( "#first_name" ).focus(function() {
                        			  setTimeout(function() {
                        				 jQuery('.success-message').hide()
                        			}, 1000);
                        			});*/
                        jQuery("#first_name").focus();

                    }
                    if (result.data.message.check == "no") {
                        form_element.append('<p class="success-message">' + result.data.message.sub_msg + '</p>');
                        /*setTimeout(function() {
                        				 jQuery('.success-message').hide()
                        			}, 3000); */
                    }
                } else {
                    if (result.data) {
                        //update email error message
                        if (jQuery(document).find('p.email-error-message').length > 0) {
                            jQuery(".mortgage-form fieldset .email-error-message").html(result.data);
                            jQuery(".btn-step-next").hide();
                        } else {
                            jQuery('#email').after('<p class="error-message email-error-message">' + result.data + '</p>');
                        }

                    }
                }
            },
            error: function(errorMessage) {
                if (errorMessage) {
                    //update application error message
                    if (jQuery(document).find('p.application-error-message').length > 0) {
                        jQuery(".mortgage-form fieldset .application-error-message").html(errorMessage);
                    } else {
                        form_element.append('<p class="">' + errorMessage + '</p>');
                    }

                }
            }

        });
    });


    /*jQuery ui range slider for Purchase price of the new home*/
    //get selected value
    if ($("#purchase_price").val() != "") {
        var purchase_price_value = mortgage_application.home_purchase_price_values.indexOf(parseInt($("#purchase_price").val()));
    } else {
        var purchase_price_value = 0;
    }
    $("#purchase_price_range").slider({
        range: "min",
        min: 0,
        max: mortgage_application.home_purchase_price_values.length - 1,
        value: purchase_price_value,
        slide: function(event, ui) {
            $(".purchase_price_display").html(mortgage_application.home_purchase_price_text[ui.value]);
            $("#purchase_price").val(mortgage_application.home_purchase_price_values[ui.value]);
        }
    });
    $(".purchase_price_display").html(mortgage_application.home_purchase_price_text[$("#purchase_price_range").slider("value")]);
    $("#purchase_price").val(mortgage_application.home_purchase_price_values[$("#purchase_price_range").slider("value")]);
    /*jQuery ui range slider for Purchase price of the new home*/
    //get selected value
    if ($("#down_payment").val() != "") {
        var down_payment_value = mortgage_application.down_payment_price_values.indexOf(parseInt($("#down_payment").val()));
    } else {
        var down_payment_value = 0;
    }
    $("#down_payment_range").slider({
        range: "min",
        min: 0,
        max: mortgage_application.down_payment_price_values.length - 1,
        value: down_payment_value,
        slide: function(event, ui) {
            $(".down_payment_display").html(mortgage_application.down_payment_price_text[ui.value]);
            $("#down_payment").val(mortgage_application.down_payment_price_values[ui.value]);
        }
    });
    $(".down_payment_display").html(mortgage_application.down_payment_price_text[$("#down_payment_range").slider("value")]);
    $("#down_payment").val(mortgage_application.down_payment_price_values[$("#down_payment_range").slider("value")]);
    /*jQuery ui range slider for Purchase price of the new home*/
    //get selected value
    if ($("#home_value").val() != "") {
        var home_value_value = mortgage_application.home_value_price_values.indexOf(parseInt($("#home_value").val()));
    } else {
        var home_value_value = 0;
    }
    $("#home_value_range").slider({
        range: "min",
        min: 0,
        max: mortgage_application.home_value_price_values.length - 1,
        value: home_value_value,
        slide: function(event, ui) {
            $(".home_value_display").html(mortgage_application.home_value_price_text[ui.value]);
            $("#home_value").val(mortgage_application.home_value_price_values[ui.value]);
        }
    });
    $(".home_value_display").html(mortgage_application.home_value_price_text[$("#home_value_range").slider("value")]);
    $("#home_value").val(mortgage_application.home_value_price_values[$("#home_value_range").slider("value")]);
    /*jQuery ui range slider for Purchase price of the new home*/
    //get selected value
    if ($("#mortgage_balance").val() != "") {
        try {
            var mortgage_balance_value = mortgage_application.mortgage_balance_price_values.indexOf(parseInt($("#mortgage_balance").val()));
        } catch (err) {}
    } else {
        var mortgage_balance_value = 0;
    }
    try {
        $("#mortgage_balance_range").slider({
            range: "min",
            min: 0,
            max: mortgage_application.mortgage_balance_price_values.length - 1,
            value: mortgage_balance_value,
            slide: function(event, ui) {
                $(".mortgage_balance_display").html(mortgage_application.mortgage_balance_price_text[ui.value]);
                $("#mortgage_balance").val(mortgage_application.mortgage_balance_price_values[ui.value]);
            }
        });

        $(".mortgage_balance_display").html(mortgage_application.mortgage_balance_price_text[$("#mortgage_balance_range").slider("value")]);
        $("#mortgage_balance").val(mortgage_application.mortgage_balance_price_values[$("#mortgage_balance_range").slider("value")]);

        /*jQuery ui range slider for loan interest rate*/
        //get selected value
        if ($("#loan_interest_rate").val() != "") {
            var loan_interest_rate_value = mortgage_application.loan_interest_rate_values.indexOf(parseInt($("#loan_interest_rate").val()));
        } else {
            var loan_interest_rate_value = 0;
        }
        $("#loan_interest_rate_range").slider({
            range: "min",
            min: 0,
            max: mortgage_application.loan_interest_rate_text.length - 1,
            value: loan_interest_rate_value,
            slide: function(event, ui) {
                $(".loan_interest_rate_display").html(mortgage_application.loan_interest_rate_text[ui.value]);
                $("#loan_interest_rate").val(mortgage_application.loan_interest_rate_values[ui.value]);
            }
        });
        $(".loan_interest_rate_display").html(mortgage_application.loan_interest_rate_text[$("#loan_interest_rate_range").slider("value")]);
        $("#loan_interest_rate").val(mortgage_application.loan_interest_rate_values[$("#loan_interest_rate_range").slider("value")]);
        /*jQuery ui range slider for loan interest rate*/
        //get selected value
        if ($("#additional_funds").val() != "") {
            var additional_funds_value = mortgage_application.additional_funds_values.indexOf(parseInt($("#additional_funds").val()));
        } else {
            var additional_funds_value = 0;
        }
        $("#additional_funds_range").slider({
            range: "min",
            min: 0,
            max: mortgage_application.additional_funds_text.length - 1,
            value: additional_funds_value,
            slide: function(event, ui) {
                $(".additional_funds_display").html(mortgage_application.additional_funds_text[ui.value]);
                $("#additional_funds").val(mortgage_application.additional_funds_values[ui.value]);
                $('#cash_out_box').html(mortgage_application.additional_funds_values[ui.value]);
                $('input[name=cash_out_box]').val(mortgage_application.additional_funds_values[ui.value]);
            }
        });
        $(".additional_funds_display").html(mortgage_application.additional_funds_text[$("#additional_funds_range").slider("value")]);
        $("#additional_funds").val(mortgage_application.additional_funds_values[$("#additional_funds_range").slider("value")]);
        $("#cash_out_box").html(mortgage_application.additional_funds_values[$("#additional_funds_range").slider("value")]);
        $("input[name=cash_out_box]").val(mortgage_application.additional_funds_values[$("#additional_funds_range").slider("value")]);
        /*jQuery ui range slider for loan interest rate*/
        //get selected value
        if ($("#additional_funds").val() != "") {
            var purchase_year_value = mortgage_application.purchase_year_values.indexOf(parseInt($("#purchase_year").val()));
        } else {
            var purchase_year_value = 0;
        }
        $("#purchase_year_range").slider({
            range: "min",
            min: 0,
            max: mortgage_application.purchase_year_values.length - 1,
            value: purchase_year_value,
            slide: function(event, ui) {
                $(".purchase_year_display").html(mortgage_application.purchase_year_values[ui.value]);
                $("#purchase_year").val(mortgage_application.purchase_year_values[ui.value]);
            }
        });
        $(".purchase_year_display").html(mortgage_application.purchase_year_values[$("#purchase_year_range").slider("value")]);
        $("#purchase_year").val(mortgage_application.purchase_year_values[$("#purchase_year_range").slider("value")]);

        /*jQuery ui range slider for age*/
        if ($("#age").val() != "") {
            var age_value = mortgage_application.age_values.indexOf(parseInt($("#age").val()));
        } else {
            var age_value = 0;
        }
        $("#age_range").slider({
            range: "min",
            min: 0,
            max: mortgage_application.age_text.length - 1,
            value: age_value,
            slide: function(event, ui) {
                $(".age_display").html(mortgage_application.age_text[ui.value]);
                $("#age").val(mortgage_application.age_values[ui.value]);
                var age = mortgage_application.age_values[ui.value];
                if (age && age == '62') {
                    jQuery('.mortgage-form fieldset .mortgage_sub_field input[name=reverse_mortgage]').prop("disabled", false).closest(".field").css("display", "block");
                } else {
                    jQuery('.mortgage-form fieldset .mortgage_sub_field input[name=reverse_mortgage]').prop("disabled", true).closest(".field").css("display", "none");
                }
            }
        });
        $(".age_display").html(mortgage_application.age_text[$("#age_range").slider("value")]);
        $("#age").val(mortgage_application.age_text[$("#age_range").slider("value")]);

    } catch (err) {}





});