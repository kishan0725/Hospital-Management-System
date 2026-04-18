var FormElements = function() {"use strict";

	//function to initiate jquery.maskedinput
	var maskInputHandler = function() {
		$.mask.definitions['~'] = '[+-]';
		$('.input-mask-date').mask('99/99/9999');
		$('.input-mask-phone').mask('(999) 999-9999');
		$('.input-mask-eyescript').mask('~9.99 ~9.99 999');
		$(".input-mask-product").mask("a*-999-a999", {
			placeholder: " ",
			completed: function() {
				alert("You typed the following: " + this.val());
			}
		});
	};
	//function to initiate bootstrap-touchspin
	var touchSpinHandler = function() {
		$("input[name='demo1']").TouchSpin({
			min: 0,
			max: 100,
			step: 0.1,
			decimals: 2,
			boostat: 5,
			maxboostedstep: 10,
			postfix: '%'
		});
		$("input[name='demo2']").TouchSpin({
			min: -1000000000,
			max: 1000000000,
			stepinterval: 50,
			maxboostedstep: 10000000,
			prefix: '$'
		});
		$("input[name='demo3']").TouchSpin({
			verticalbuttons: true
		});
		$("input[name='demo4']").TouchSpin({
			verticalbuttons: true,
			verticalupclass: 'fa fa-plus',
			verticaldownclass: 'fa fa-minus'
		});
		$("input[name='demo5']").TouchSpin({
			postfix: "a button",
			postfix_extraclass: "btn btn-default"
		});
		$("input[name='demo6']").TouchSpin({
			postfix: "a button",
			postfix_extraclass: "btn btn-default"
		});
		$("input[name='demo7']").TouchSpin({
			prefix: "pre",
			postfix: "post"
		});
	};
	
	var autosizeHandler = function() {
		$('.autosize.area-animated').append("\n");
		autosize($('.autosize'));
	};
	var select2Handler = function() {
		$(".js-example-basic-single").select2();
		$(".js-example-basic-multiple").select2();
		$(".js-example-placeholder-single").select2({
			placeholder: "Select a state"
		});
		var data = [{
			id: 0,
			text: 'enhancement'
		}, {
			id: 1,
			text: 'bug'
		}, {
			id: 2,
			text: 'duplicate'
		}, {
			id: 3,
			text: 'invalid'
		}, {
			id: 4,
			text: 'wontfix'
		}];
		$(".js-example-data-array-selected").select2({
			data: data
		});
		$(".js-example-basic-hide-search").select2({
			minimumResultsForSearch: Infinity
		});
	};
	var datePickerHandler = function() {
		$('.datepicker').datepicker({
			autoclose: true,
			todayHighlight: true
		});
		$('.format-datepicker').datepicker({
			format:  "M, d yyyy",
			todayHighlight: true
		});
		
	};
	var timePickerHandler = function() {
		$('#timepicker-default').timepicker();		
	};
	return {
		//main function to initiate template pages
		init: function() {
			maskInputHandler();
			touchSpinHandler();
			autosizeHandler();
			select2Handler();
			datePickerHandler();
			timePickerHandler();
		}
	};
}();
