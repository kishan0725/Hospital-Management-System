'use strict';
var UIElements = function() {

	var staticPopoverHandler = function() {
		var radioPopover = $("input[name='popoverType']");
		radioPopover.first().prop('checked', true);
		radioPopover.change(function() {
			$("#static-popover > .popover").removeAttr('class').attr('class', 'popover ' + $(this).val());
		});
	};
	var animatedProgressbarHandler = function() {
		$(".active-progressbar").on("click", function() {
			$(".progress-animated .progress-bar").each(function() {
				var _this = $(this);
				_this.progressbar({
					display_text: _this.data("display-text"),
					use_percentage: _this.data("use-percentage"),
					transition_delay: _this.data("transition-delay")
				});
			});
		});
		$(".reset-progressbar").on("click", function() {
			$(".progress-animated .progress-bar").each(function() {
				var _this = $(this);
				var _tempData = _this.data('transitiongoal');
				_this.attr('data-transitiongoal', 0).progressbar().attr('data-transitiongoal', _tempData);

			});
		});
	};
	var paginationHandler = function() {
		$("#pagination-demo").twbsPagination({
			totalPages: 35,
			visiblePages: 7,
			onPageClick: function(event, page) {
				$("#page-content").text('Page ' + page);
			}
		});
		$("#pagination-demo-2").twbsPagination({
			totalPages: 35,
			visiblePages: 8,
			href: '#page={{number}}',
			onPageClick: function(event, page) {
				$("#page-content-2").text('Page ' + page);
			}
		});
		$(".pagination-demo-3").twbsPagination({
			totalPages: 35,
			visiblePages: 10,
			onPageClick: function(event, page) {
				$("#page-content-3").text('Page ' + page);
			}
		});
	};
	var ratingHandler = function() {

		$('.rating, .rating-tooltip').each(function() {
			$(this).val() > 0 ? $(this).next(".label").show().text($(this).val() || ' ') : $(this).next(".label").hide();
		});
		$('.rating-tooltip').rating({
			extendSymbol: function(rate) {
				$(this).tooltip({
					container: 'body',
					placement: 'bottom',
					title: 'Rate ' + rate
				});
			}
		});
		$('.rating, .rating-tooltip').on('change', function() {
			$(this).next('.label').show().text($(this).val());
		});
	};
	return {
		init: function() {
			staticPopoverHandler();
			animatedProgressbarHandler();
			paginationHandler();
			ratingHandler();
		}
	};
}();
