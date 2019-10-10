'use strict';
var Main = function() {
	var $html = $('html'), $win = $(window), wrap = $('.app-aside'), MEDIAQUERY = {}, app = $('#app');

	MEDIAQUERY = {
		desktopXL : 1200,
		desktop : 992,
		tablet : 768,
		mobile : 480
	};
	$(".current-year").text((new Date()).getFullYear());
	//sidebar
	var sidebarHandler = function() {
		var eventObject = isTouch() ? 'click' : 'mouseenter', elem = $('#sidebar'), ul = "", menuTitle, _this, sidebarMobileToggler = $('.sidebar-mobile-toggler'), $winOffsetTop = 0, $winScrollTop = 0, $appWidth;

		elem.on('click', 'a', function(e) {

			_this = $(this);
			if (isSidebarClosed() && !isSmallDevice() && !_this.closest("ul").hasClass("sub-menu"))
				return;

			_this.closest("ul").find(".open").not(".active").children("ul").not(_this.next()).slideUp(200).parent('.open').removeClass("open");
			if (_this.next().is('ul') && _this.parent().toggleClass('open')) {

				_this.next().slideToggle(200, function() {
					$win.trigger("resize");

				});
				e.stopPropagation();
				e.preventDefault();
			} else {
				//_this.parent().addClass("active");

			}
		});
		elem.on(eventObject, 'a', function(e) {
			if (!isSidebarClosed() || isSmallDevice())
				return;
			_this = $(this);

			if (!_this.parent().hasClass('hover') && !_this.closest("ul").hasClass("sub-menu")) {
				wrapLeave();
				_this.parent().addClass('hover');
				menuTitle = _this.find(".item-inner").clone();
				if (_this.parent().hasClass('active')) {
					menuTitle.addClass("active");
				}
				var offset = $("#sidebar").position().top;
				var itemTop = isSidebarFixed() ? _this.parent().position().top + offset : (_this.parent().position().top);
				menuTitle.css({
					position : isSidebarFixed() ? 'fixed' : 'absolute',
					height : _this.outerHeight(),
					top : itemTop
				}).appendTo(wrap);
				if (_this.next().is('ul')) {
					ul = _this.next().clone(true);

					ul.appendTo(wrap).css({
						top : itemTop + _this.outerHeight(),
						position : isSidebarFixed() ? 'fixed' : 'absolute',
					});
					if (_this.parent().position().top + _this.outerHeight() + offset + ul.height() > $win.height() && isSidebarFixed()) {
						ul.css('bottom', 0);
					} else {
						ul.css('bottom', 'auto');
					}

					wrap.children().first().scroll(function() {
						if (isSidebarFixed())
							wrapLeave();
					});

					setTimeout(function() {

						if (!wrap.is(':empty')) {
							$(document).on('click tap', wrapLeave);
						}
					}, 300);

				} else {
					ul = "";
					return;
				}

			}
		});
		wrap.on('mouseleave', function(e) {
			$(document).off('click tap', wrapLeave);
			$('.hover', wrap).removeClass('hover');
			$('> .item-inner', wrap).remove();
			$('> ul', wrap).remove();

		});

		sidebarMobileToggler.on('click', function() {
			
			$winScrollTop = $winOffsetTop;
			if (!$('#app').hasClass('app-slide-off') && !$('#app').hasClass('app-offsidebar-open')) {
				$winOffsetTop = $win.scrollTop();
				$winScrollTop = 0;
				$('footer').hide();
				$appWidth = $('#app .main-content').innerWidth();
				$('#app .main-content').css({
					position : 'absolute',
					top : -$winOffsetTop,
					width : $appWidth
				});
			} else {
				resetSidebar();
			}

		});

		$(document).on("mousedown touchstart", function(e) {
			if (elem.has(e.target).length === 0 && !elem.is(e.target) && !sidebarMobileToggler.is(e.target) && ($('#app').hasClass('app-slide-off') || $('#app').hasClass('app-offsidebar-open'))) {
				resetSidebar();
			}
		});

		var resetSidebar = function() {
			$winScrollTop = $winOffsetTop;
			$("#app .app-content").one("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function() {

				if (!$('#app').hasClass('app-slide-off') && !$('#app').hasClass('app-offsidebar-open')) {
					$('#app .main-content').css({
						position : 'relative',
						top : 'auto',
						width : 'auto'
					});

					window.scrollTo(0, $winScrollTop);
					$('footer').show();
					$("#app .app-content").off("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd");
				}

			});
		};
	};
	// navbar collapse
	var navbarHandler = function() {
		var navbar = $('.navbar-collapse > .nav');
		var pageHeight = $win.innerHeight() - $('header').outerHeight();
		var collapseButton = $('#menu-toggler');
		if (isSmallDevice()) {
			navbar.css({
				height : pageHeight
			});
		} else {
			navbar.css({
				height : 'auto'
			});
		}
		$(document).on("mousedown touchstart", toggleNavbar);
		function toggleNavbar(e) {
			if (navbar.has(e.target).length === 0 && !navbar.is(e.target) && navbar.parent().hasClass("collapse in")) {
				collapseButton.trigger("click");
			}
		}

	};
	// tooltips handler
	var tooltipHandler = function() {
		$('[data-toggle="tooltip"]').tooltip();
	};
	// popovers handler
	var popoverHandler = function() {
		$('[data-toggle="popover"]').popover();
	};
	// perfect scrollbar
	var perfectScrollbarHandler = function() {
		var pScroll = $(".perfect-scrollbar");

		if (!isMobile() && pScroll.length) {
			pScroll.perfectScrollbar({
				suppressScrollX : true
			});
			pScroll.on("mousemove", function() {
				$(this).perfectScrollbar('update');
			});

		}
	};
	//toggle class
	var toggleClassOnElement = function() {
		var toggleAttribute = $('*[data-toggle-class]');
		toggleAttribute.each(function() {
			var _this = $(this);
			var toggleClass = _this.attr('data-toggle-class');
			var outsideElement;
			var toggleElement;
			typeof _this.attr('data-toggle-target') !== 'undefined' ? toggleElement = $(_this.attr('data-toggle-target')) : toggleElement = _this;
			_this.on("click", function(e) {
				if (_this.attr('data-toggle-type') !== 'undefined' && _this.attr('data-toggle-type') == "on") {
					toggleElement.addClass(toggleClass);
				} else if (_this.attr('data-toggle-type') !== 'undefined' && _this.attr('data-toggle-type') == "off") {
					toggleElement.removeClass(toggleClass);
				} else {
					toggleElement.toggleClass(toggleClass);
				}
				e.preventDefault();
				if (_this.attr('data-toggle-click-outside')) {

					outsideElement = $(_this.attr('data-toggle-click-outside'));
					$(document).on("mousedown touchstart", toggleOutside);

				}

			});

			var toggleOutside = function(e) {
				if (outsideElement.has(e.target).length === 0 && !outsideElement.is(e.target) && !toggleAttribute.is(e.target) && toggleElement.hasClass(toggleClass)) {
					toggleElement.removeClass(toggleClass);
					$(document).off("mousedown touchstart", toggleOutside);
				}
			};

		});
	};
	//switchery
	var switcheryHandler = function() {
		var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));

		elems.forEach(function(html) {
			var switchery = new Switchery(html);
		});
	};
	//search form
	var searchHandler = function() {
		var elem = $('.search-form');
		var searchForm = elem.children('form');
		var formWrap = elem.parent();

		$(".s-open").on('click', function(e) {
			searchForm.prependTo(wrap);
			e.preventDefault();
			$(document).on("mousedown touchstart", closeForm);
		});
		$(".s-remove").on('click', function(e) {
			searchForm.appendTo(elem);
			e.preventDefault();
		});
		var closeForm = function(e) {
			if (!searchForm.is(e.target) && searchForm.has(e.target).length === 0) {
				$(".s-remove").trigger('click');
				$(document).off("mousedown touchstart", closeForm);
			}
		};
	};
	// settings
	var settingsHandler = function() {
		var clipSetting = {}, appSetting = {};
		clipSetting = {
			fixedHeader : true,
			fixedSidebar : true,
			closedSidebar : false,
			fixedFooter : false,
			theme : 'theme-1'
		};
		if ($.cookie) {
			if ($.cookie("clip-setting")) {
				appSetting = $.parseJSON($.cookie("clip-setting"));
			} else {
				appSetting = clipSetting;
			}
		}

		appSetting.fixedHeader ? app.addClass('app-navbar-fixed') : app.removeClass('app-navbar-fixed');
		appSetting.fixedSidebar ? app.addClass('app-sidebar-fixed') : app.removeClass('app-sidebar-fixed');
		appSetting.closedSidebar ? app.addClass('app-sidebar-closed') : app.removeClass('app-sidebar-closed');
		appSetting.fixedFooter ? app.addClass('app-footer-fixed') : app.removeClass('app-footer-fixed');
		app.hasClass("app-navbar-fixed") ? $('#fixed-header').prop('checked', true) : $('#fixed-header').prop('checked', false);
		app.hasClass("app-sidebar-fixed") ? $('#fixed-sidebar').prop('checked', true) : $('#fixed-sidebar').prop('checked', false);
		app.hasClass("app-sidebar-closed") ? $('#closed-sidebar').prop('checked', true) : $('#closed-sidebar').prop('checked', false);
		app.hasClass("app-footer-fixed") ? $('#fixed-footer').prop('checked', true) : $('#fixed-footer').prop('checked', false);
		$('#skin_color').attr("href", "assets/css/themes/" + appSetting.theme + ".css");
		$('input[name="setting-theme"]').each(function() {
			$(this).val() == appSetting.theme ? $(this).prop('checked', true) : $(this).prop('checked', false);
		});
		switchLogo(appSetting.theme);

		$('input[name="setting-theme"]').change(function() {
			var selectedTheme = $(this).val();
			$('#skin_color').attr("href", "assets/css/themes/" + selectedTheme + ".css");
			switchLogo(selectedTheme);
			appSetting.theme = selectedTheme;
			$.cookie("clip-setting", JSON.stringify(appSetting));

		});

		$('#fixed-header').change(function() {
			$(this).is(":checked") ? app.addClass("app-navbar-fixed") : app.removeClass("app-navbar-fixed");
			appSetting.fixedHeader = $(this).is(":checked");
			$.cookie("clip-setting", JSON.stringify(appSetting));
		});
		$('#fixed-sidebar').change(function() {
			$(this).is(":checked") ? app.addClass("app-sidebar-fixed") : app.removeClass("app-sidebar-fixed");
			appSetting.fixedSidebar = $(this).is(":checked");
			$.cookie("clip-setting", JSON.stringify(appSetting));
		});
		$('#closed-sidebar').change(function() {
			$(this).is(":checked") ? app.addClass("app-sidebar-closed") : app.removeClass("app-sidebar-closed");
			appSetting.closedSidebar = $(this).is(":checked");
			$.cookie("clip-setting", JSON.stringify(appSetting));
		});
		$('#fixed-footer').change(function() {
			$(this).is(":checked") ? app.addClass("app-footer-fixed") : app.removeClass("app-footer-fixed");
			appSetting.fixedFooter = $(this).is(":checked");
			$.cookie("clip-setting", JSON.stringify(appSetting));
		});
		function switchLogo(theme) {
			switch (theme) {
				case "theme-2":
				case "theme-3":
				case "theme-5":
				case "theme-6":
					$(".navbar-brand img").attr("src", "assets/images/logo2.png");
					break;

				default:
					$(".navbar-brand img").attr("src", "assets/images/logo.png");
					break;
			}
		}

		function defaultSetting() {
			$('#fixed-header').prop('checked', true);
			$('#fixed-sidebar').prop('checked', true);
			$('#closed-sidebar').prop('checked', false);
			$('#fixed-footer').prop('checked', false);
			$('#skin_color').attr("href", "assets/css/themes/theme-1.css");
			$(".navbar-brand img").attr("src", "assets/images/logo.png");

		}

	};
	// function to allow a button or a link to open a tab
	var showTabHandler = function(e) {
		if ($(".show-tab").length) {
			$('.show-tab').on('click', function(e) {
				e.preventDefault();
				var tabToShow = $(this).attr("href");
				if ($(tabToShow).length) {
					$('a[href="' + tabToShow + '"]').tab('show');
				}
			});
		}
	};
	// function to enable panel scroll with perfectScrollbar
	var panelScrollHandler = function() {
		var panelScroll = $(".panel-scroll");
		if (panelScroll.length && !isMobile()) {
			panelScroll.perfectScrollbar({
				suppressScrollX : true
			});
		}
	};
	//function to activate the panel tools
	var panelToolsHandler = function() {

		// panel close
		$('body').on('click', '.panel-close', function(e) {
			var panel = $(this).closest('.panel');

			destroyPanel();

			function destroyPanel() {
				var col = panel.parent();
				panel.fadeOut(300, function() {
					$(this).remove();
					if (col.is('[class*="col-"]') && col.children('*').length === 0) {
						col.remove();
					}
				});
			}


			e.preventDefault();
		});
		// panel refresh
		$('body').on('click', '.panel-refresh', function(e) {
			var $this = $(this), csspinnerClass = 'csspinner', panel = $this.parents('.panel').eq(0), spinner = $this.data('spinner') || "load1";
			panel.addClass(csspinnerClass + ' ' + spinner);

			window.setTimeout(function() {
				panel.removeClass(csspinnerClass);
			}, 1000);
			e.preventDefault();
		});
		// panel collapse
		$('body').on('click', '.panel-collapse', function(e) {
			e.preventDefault();
			var el = $(this);
			var panel = $(this).closest(".panel");
			var bodyPanel = panel.children(".panel-body");
			bodyPanel.slideToggle(200, function() {
				panel.toggleClass("collapses");
			});

		});

	};
	// function to activate the Go-Top button
	var goTopHandler = function(e) {
		$('.go-top').on('click', function(e) {
			$("html, body").animate({
				scrollTop : 0
			}, "slow");
			e.preventDefault();
		});
	};
	var customSelectHandler = function() {
		[].slice.call(document.querySelectorAll('select.cs-select')).forEach(function(el) {
			new SelectFx(el);
		});
	};
	// Window Resize Function
	var resizeHandler = function(func, threshold, execAsap) {
		$(window).resize(function() {
			navbarHandler();
			if (isLargeDevice()) {
				$('#app .main-content').css({
					position : 'relative',
					top : 'auto',
					width : 'auto'
				});
				$('footer').show();
			}
		});
	};
	function wrapLeave() {
		wrap.trigger('mouseleave');
	}

	function isTouch() {
		return $html.hasClass('touch');
	}

	function isSmallDevice() {
		return $win.width() < MEDIAQUERY.desktop;
	}

	function isLargeDevice() {
		return $win.width() >= MEDIAQUERY.desktop;
	}

	function isSidebarClosed() {
		return $('.app-sidebar-closed').length;
	}

	function isSidebarFixed() {
		return $('.app-sidebar-fixed').length;
	}

	function isMobile() {
		if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
			return true;
		} else {
			return false;
		}
	}

	return {
		init : function() {
			settingsHandler();
			sidebarHandler();
			toggleClassOnElement();
			navbarHandler();
			searchHandler();
			tooltipHandler();
			popoverHandler();
			perfectScrollbarHandler();
			switcheryHandler();
			resizeHandler();
			showTabHandler();
			panelScrollHandler();
			panelToolsHandler();
			customSelectHandler();
			goTopHandler();
		}
	};
}();
