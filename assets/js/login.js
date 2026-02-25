var Login = function() {
	"use strict";
	
	
	
	var runSetDefaultValidation = function() {
		$.validator.setDefaults({
			errorElement : "span",
			errorClass : 'help-block',
			errorPlacement : function(error, element) {
				if (element.attr("type") == "radio" || element.attr("type") == "checkbox") {
					error.insertAfter($(element).closest('.form-group').children('div').children().last());
				} else if (element.attr("name") == "card_expiry_mm" || element.attr("name") == "card_expiry_yyyy") {
					error.appendTo($(element).closest('.form-group').children('div'));
				} else {
					error.insertAfter(element);

				}
			},
			ignore : ':hidden',
			success : function(label, element) {
				label.addClass('help-block valid');

				$(element).closest('.form-group').removeClass('has-error');
			},
			highlight : function(element) {
				$(element).closest('.help-block').removeClass('valid');

				$(element).closest('.form-group').addClass('has-error');

			},
			unhighlight : function(element) {
				$(element).closest('.form-group').removeClass('has-error');

			}
		});
	};
	var runLoginValidator = function() {
		var form = $('.form-login');
		var errorHandler = $('.errorHandler', form);
		form.validate({
			rules : {
				username : {
					minlength : 2,
					required : true
				},
				password : {
					minlength : 6,
					required : true
				}
			},
			submitHandler : function(form) {
				errorHandler.hide();
				form.submit();
			},
			invalidHandler : function(event, validator) {
				errorHandler.show();
			}
		});
	};
	var runForgotValidator = function() {
		var form2 = $('.form-forgot');
		var errorHandler2 = $('.errorHandler', form2);
		form2.validate({
			rules : {
				email : {
					required : true
				}
			},
			submitHandler : function(form) {
				errorHandler2.hide();
				form2.submit();
			},
			invalidHandler : function(event, validator) {
				errorHandler2.show();
			}
		});
	};
	var runRegisterValidator = function() {
		var form3 = $('.form-register');
		var errorHandler3 = $('.errorHandler', form3);
		form3.validate({
			rules : {
				full_name : {
					minlength : 2,
					required : true
				},
				address : {
					minlength : 2,
					required : true
				},
				city : {
					minlength : 2,
					required : true
				},
				gender : {
					required : true
				},
				email : {
					required : true
				},
				password : {
					minlength : 6,
					required : true
				},
				password_again : {
					required : true,
					minlength : 5,
					equalTo : "#password"
				},
				agree : {
					minlength : 1,
					required : true
				}
			},
			submitHandler : function(form) {
				errorHandler3.hide();
				form3.submit();
			},
			invalidHandler : function(event, validator) {
				errorHandler3.show();
			}
		});
	};
	return {

		init : function() {
			runSetDefaultValidation();
			runLoginValidator();
			runForgotValidator();
			runRegisterValidator();
		}
	};
}();
