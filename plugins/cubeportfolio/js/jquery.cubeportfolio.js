/**
 * Cube Portfolio - Responsive jQuery Grid Plugin
 *
 * version: 1.4.1 (July 23, 2014)
 * requires jQuery v1.7 or later
 *
 * Copyright (c) 2014, Mihai Buricea (http://scriptpie.com)
 * Released under CodeCanyon License http://codecanyon.net/licenses
 *
 */

(function($, window, document, undefined) {

    'use strict';

    var namespace = 'cbp',
        eventNamespace = '.' + namespace;

    // Utility
    if (typeof Object.create !== 'function') {
        Object.create = function(obj) {
            function F() {}
            F.prototype = obj;
            return new F();
        };
    }

    // jquery new filter for images uncached
    $.expr[':'].uncached = function(obj) {
        // Ensure we are dealing with an `img` element with a valid `src` attribute.
        if (!$(obj).is('img[src!=""]')) {
            return false;
        }

        // Firefox's `complete` property will always be `true` even if the image has not been downloaded.
        // Doing it this way works in Firefox.
        var img = new Image();
        img.src = obj.src;
        return !img.complete;
    };

    var popup = {

        /**
         * init function for popup
         * @param cubeportfolio = cubeportfolio instance
         * @param type =  'lightbox' or 'singlePage'
         */
        init: function(cubeportfolio, type) {

            var t = this,
                currentBlock;

            // remember cubeportfolio instance
            t.cubeportfolio = cubeportfolio;

            // remember if this instance is for lightbox or for singlePage
            t.type = type;

            // remember if the popup is open or not
            t.isOpen = false;

            t.options = t.cubeportfolio.options;

            if (type === 'singlePageInline') {

                t.matrice = [-1, -1];

                t.height = 0;

                // create markup, css and add events for SinglePageInline
                t._createMarkupSinglePageInline();
                return;
            }

            // create markup, css and add events for lightbox and singlePage
            t._createMarkup();

            if (t.options.singlePageDeeplinking && type === 'singlePage') {
                t.url = location.href;

                if (t.url.slice(-1) == '#') {
                    t.url = t.url.slice(0, -1);
                }

                currentBlock = t.cubeportfolio.blocksAvailable.find(t.options.singlePageDelegate).filter(function(index) {
                    // we split the url in half and store the second entry. If this entry is equal with current element return true
                    return (t.url.split('#cbp=')[1] === this.getAttribute('href'));
                })[0];


                if (currentBlock) {

                    t.url = t.url.replace(/#cbp=(.+)/ig, '');

                    t.openSinglePage(t.cubeportfolio.blocksAvailable, currentBlock);
                }

            }

        },

        /**
         * Create markup, css and add events
         */
        _createMarkup: function() {

            var t = this;

            // wrap element
            t.wrap = $('<div/>', {
                'class': 'cbp-popup-wrap cbp-popup-' + t.type,
                'data-action': (t.type === 'lightbox') ? 'close' : ''
            }).on('click' + eventNamespace, function(e) {
                if (t.stopEvents) {
                    return;
                }

                var action = $(e.target).attr('data-action');

                if (t[action]) {
                    t[action]();
                    e.preventDefault();
                }
            });

            // content element
            t.content = $('<div/>', {
                'class': 'cbp-popup-content',
            }).appendTo(t.wrap);

            // append loading div
            $('<div/>', {
                'class': 'cbp-popup-loadingBox',
            }).appendTo(t.wrap);

            // add background only for ie8
            if (t.cubeportfolio.browser === 'ie8') {
                t.bg = $('<div/>', {
                    'class': 'cbp-popup-ie8bg',
                    'data-action': (t.type === 'lightbox') ? 'close' : ''
                }).appendTo(t.wrap);
            }

            // create navigation wrap
            t.navigationWrap = $('<div/>', {
                'class': 'cbp-popup-navigation-wrap'
            }).appendTo(t.wrap);

            // create navigation block
            t.navigation = $('<div/>', {
                'class': 'cbp-popup-navigation'
            }).appendTo(t.navigationWrap);

            // close button
            t.closeButton = $('<button/>', {
                'class': 'cbp-popup-close',
                'title': 'Close (Esc arrow key)',
                'type': 'button',
                'data-action': 'close',
            }).appendTo(t.navigation);

            // next button
            t.nextButton = $('<button/>', {
                'class': 'cbp-popup-next',
                'title': 'Next (Right arrow key)',
                'type': 'button',
                'data-action': 'next'
            }).appendTo(t.navigation);


            // prev button
            t.prevButton = $('<button/>', {
                'class': 'cbp-popup-prev',
                'title': 'Previous (Left arrow key)',
                'type': 'button',
                'data-action': 'prev',
            }).appendTo(t.navigation);


            if (t.type === 'singlePage') {

                if (t.options.singlePageShowCounter) {
                    // counter for singlePage
                    t.counter = $('<div/>', {
                        'class': 'cbp-popup-singlePage-counter'
                    }).appendTo(t.navigation);
                }

                t.content.on('click' + eventNamespace, t.options.singlePageDelegate, function(e) {
                    e.preventDefault();
                    var i,
                        len = t.dataArray.length,
                        href = this.getAttribute('href');

                    for (i = 0; i < len; i++) {
                        if (t.dataArray[i].url == href) {
                            break;
                        }
                    }

                    t.singlePageJumpTo(i - t.current);

                });

            }

            $(document).on('keydown' + eventNamespace, function(e) {

                // if is not open => return
                if (!t.isOpen) return;

                // if all events are stopped => return
                if (t.stopEvents) return;

                if (e.keyCode === 37) { // prev key
                    t.prev();
                } else if (e.keyCode === 39) { // next key
                    t.next();
                } else if (e.keyCode === 27) { //esc key
                    t.close();
                }
            });

        },

        _createMarkupSinglePageInline: function() {
            var t = this;

            // wrap element
            t.wrap = $('<div/>', {
                'class': 'cbp-popup-singlePageInline'
            }).on('click' + eventNamespace, function(e) {
                if (t.stopEvents) {
                    return;
                }

                var action = $(e.target).attr('data-action');

                if (action) {
                    t[action]();
                    e.preventDefault();
                }
            });

            // content element
            t.content = $('<div/>', {
                'class': 'cbp-popup-content',
            }).appendTo(t.wrap);

            // append loading div
            $('<div/>', {
                'class': 'cbp-popup-loadingBox',
            }).appendTo(t.wrap);

            // create navigation block
            t.navigation = $('<div/>', {
                'class': 'cbp-popup-navigation'
            }).appendTo(t.wrap);

            // close button
            t.closeButton = $('<button/>', {
                'class': 'cbp-popup-close',
                'title': 'Close (Esc arrow key)',
                'type': 'button',
                'data-action': 'close',
            }).appendTo(t.navigation);

        },

        destroy: function() {

            var t = this;

            // remove off key down
            $(document).off('keydown' + eventNamespace);

            t.cubeportfolio.$obj.off('click' + eventNamespace, t.options.lightboxDelegate);

            t.cubeportfolio.$obj.off('click' + eventNamespace, t.options.singlePageDelegate);
            t.content.off('click' + eventNamespace, t.options.singlePageDelegate);

            t.cubeportfolio.$obj.off('click' + eventNamespace, t.options.singlePageInlineDelegate);

            t.cubeportfolio.$obj.removeClass('cbp-popup-isOpening');

            t.cubeportfolio.blocks.removeClass('cbp-singlePageInline-active');

            t.wrap.remove();
        },

        openLightbox: function(blocks, currentBlock) {

            var t = this,
                i = 0,
                currentBlockHref, tempHref = [],
                element;

            if (t.isOpen) return;

            // check singlePageInline and close it
            if (t.cubeportfolio.singlePageInline && t.cubeportfolio.singlePageInline.isOpen) {
                t.cubeportfolio.singlePageInline.close();
            }

            // remember that the lightbox is open now
            t.isOpen = true;

            // remember to stop all events after the lightbox has been shown
            t.stopEvents = false;

            // array with elements
            t.dataArray = [];

            // reset current
            t.current = null;

            currentBlockHref = currentBlock.getAttribute('href');
            if (currentBlockHref === null) {
                throw new Error('HEI! Your clicked element doesn\'t have a href attribute.');
            }

            $.each(blocks.find(t.options.lightboxDelegate), function(index, item) {
                var href = item.getAttribute('href'),
                    src = href, // default if element is image
                    type = 'isImage'; // default if element is image

                if ($.inArray(href, tempHref) === -1) {

                    if (currentBlockHref == href) {
                        t.current = i;
                    } else if (!t.options.lightboxGallery) {
                        return;
                    }

                    if (/youtube/i.test(href)) {

                        // create new href
                        src = '//www.youtube.com/embed/' + href.substring(href.lastIndexOf('v=') + 2) + '?autoplay=1';

                        type = 'isYoutube';

                    } else if (/vimeo/i.test(href)) {

                        // create new href
                        src = '//player.vimeo.com/video/' + href.substring(href.lastIndexOf('/') + 1) + '?autoplay=1';

                        type = 'isVimeo';

                    } else if (/ted\.com/i.test(href)) {

                        // create new href
                        src = 'http://embed.ted.com/talks/' + href.substring(href.lastIndexOf('/') + 1) + '.html';

                        type = 'isTed';

                    } else if (/(\.mp4)|(\.ogg)|(\.ogv)|(\.webm)/i.test(href)) {

                        if ( href.indexOf('|') !== -1 ) {
                            // create new href
                            src = href.split('|');
                        } else {
                            // create new href
                            src = href.split('%7C');
                        }

                        type = 'isSelfHosted';

                    }

                    t.dataArray.push({
                        src: src,
                        title: item.getAttribute(t.options.lightboxTitleSrc),
                        type: type
                    });

                    i++;
                }

                tempHref.push(href);
            });


            // total numbers of elements
            t.counterTotal = t.dataArray.length;

            if (t.counterTotal === 1) {
                t.nextButton.hide();
                t.prevButton.hide();
                t.dataActionImg = '';
            } else {
                t.nextButton.show();
                t.prevButton.show();
                t.dataActionImg = 'data-action="next"';
            }

            // append to body
            t.wrap.appendTo(document.body);

            t.scrollTop = $(window).scrollTop();

            $('html').css({
                overflow: 'hidden',
                paddingRight: window.innerWidth - $(document).width()
            });

            // show the wrapper (lightbox box)
            t.wrap.show();

            // get the current element
            element = t.dataArray[t.current];

            // call function if current element is image or video (iframe)
            t[element.type](element);

        },

        openSinglePage: function(blocks, currentBlock) {

            var t = this,
                i = 0,
                currentBlockHref, tempHref = [];

            if (t.isOpen) return;

            // check singlePageInline and close it
            if (t.cubeportfolio.singlePageInline && t.cubeportfolio.singlePageInline.isOpen) {
                t.cubeportfolio.singlePageInline.close();
            }

            // remember that the lightbox is open now
            t.isOpen = true;

            // remember to stop all events after the popup has been showing
            t.stopEvents = false;

            // array with elements
            t.dataArray = [];

            // reset current
            t.current = null;

            currentBlockHref = currentBlock.getAttribute('href');
            if (currentBlockHref === null) {
                throw new Error('HEI! Your clicked element doesn\'t have a href attribute.');
            }


            $.each(blocks.find(t.options.singlePageDelegate), function(index, item) {
                var href = item.getAttribute('href');

                if ($.inArray(href, tempHref) === -1) {

                    if (currentBlockHref == href) {
                        t.current = i;
                    }

                    t.dataArray.push({
                        url: href,
                        element: item
                    });

                    i++;
                }

                tempHref.push(href);
            });

            // total numbers of elements
            t.counterTotal = t.dataArray.length;

            // append to body
            t.wrap.appendTo(document.body);

            t.scrollTop = $(window).scrollTop();

            $('html').css({
                overflow: 'hidden',
                paddingRight: window.innerWidth - $(document).width()
            });

            // go to top of the page (reset scroll)
            t.wrap.scrollTop(0);

            // register callback function
            if ($.isFunction(t.options.singlePageCallback)) {
                t.options.singlePageCallback.call(t, t.dataArray[t.current].url, t.dataArray[t.current].element);
            }

            // show the wrapper
            t.wrap.show();

            t.wrap.one(t.cubeportfolio.transitionEnd, function() {
                var width;



                // make the navigation sticky
                if (t.options.singlePageStickyNavigation) {

                    t.wrap.addClass('cbp-popup-singlePage-sticky');

                    width = t.wrap[0].clientWidth;
                    t.navigationWrap.width(width);
                    t.navigation.width(width);
                }

            });

            if (t.cubeportfolio.browser === 'ie8' || t.cubeportfolio.browser === 'ie9') {

                setTimeout(function() {
                    t.wrap.addClass('cbp-popup-singlePage-sticky');
                }, 1000);

                // make the navigation sticky
                if (t.options.singlePageStickyNavigation) {
                    var width = t.wrap[0].clientWidth;

                    t.navigationWrap.width(width);
                    t.navigation.width(width);

                }
            }

            setTimeout(function() {
                t.wrap.addClass('cbp-popup-singlePage-open');
            }, 20);

            // change link
            if (t.options.singlePageDeeplinking) {
                location.href = t.url + '#cbp=' + t.dataArray[t.current].url;
            }

        },


        openSinglePageInline: function(blocks, currentBlock, fromOpen) {

            var t = this,
                i = 0,
                start = 0,
                end = 0,
                currentBlockHref, tempHref = [],
                currentRow, rows;

            fromOpen = fromOpen || false;

            t.storeBlocks = blocks;
            t.storeCurrentBlock = currentBlock;

            // check singlePageInline and close it
            if (t.isOpen) {

                if (t.dataArray[t.current].url != currentBlock.getAttribute('href')) {
                    t.cubeportfolio.singlePageInline.close('open', {
                        blocks: blocks,
                        currentBlock: currentBlock,
                        fromOpen: true
                    });

                } else {
                    t.close();
                }

                return;
            }

            t.wrap.addClass('cbp-popup-loading');

            // remember that the lightbox is open now
            t.isOpen = true;

            // remember to stop all events after the popup has been showing
            t.stopEvents = false;

            // array with elements
            t.dataArray = [];

            // reset current
            t.current = null;

            currentBlockHref = currentBlock.getAttribute('href');
            if (currentBlockHref === null) {
                throw new Error('HEI! Your clicked element doesn\'t have a href attribute.');
            }

            $.each(blocks.find(t.options.singlePageInlineDelegate), function(index, item) {
                var href = item.getAttribute('href');

                if ($.inArray(href, tempHref) === -1) {

                    if (currentBlockHref == href) {
                        t.current = i;
                    }

                    t.dataArray.push({
                        url: href,
                        element: item
                    });

                    i++;
                }

                tempHref.push(href);
            });

            $(t.dataArray[t.current].element).parents('.cbp-item').addClass('cbp-singlePageInline-active');

            // total numbers of elements
            t.counterTotal = t.dataArray.length;

            if (t.cubeportfolio.blocksClone) {

                if (t.cubeportfolio.ulHidden === 'clone') {
                    t.wrap.prependTo(t.cubeportfolio.$ul);
                } else {
                    t.wrap.prependTo(t.cubeportfolio.$ulClone);
                }

            } else {
                // append
                t.wrap.prependTo(t.cubeportfolio.$ul);
            }

            if (t.options.singlePageInlinePosition === 'top') {

                start = 0;
                end = t.cubeportfolio.cols - 1;

            } else if (t.options.singlePageInlinePosition === 'above') {

                i = Math.floor(t.current / t.cubeportfolio.cols);

                start = t.cubeportfolio.cols * i;
                end = t.cubeportfolio.cols * (i + 1) - 1;

            } else { //below

                i = Math.floor(t.current / t.cubeportfolio.cols);

                start = Math.min(t.cubeportfolio.cols * (i + 1), t.counterTotal);
                end = Math.min(t.cubeportfolio.cols * (i + 2) - 1, t.counterTotal);

                currentRow = Math.ceil((t.current + 1) / t.cubeportfolio.cols);
                rows = Math.ceil(t.counterTotal / t.cubeportfolio.cols);

                if (currentRow == rows) {
                    t.lastColumn = true;
                } else {
                    t.lastColumn = false;
                }

                if (fromOpen) {
                    if (t.lastColumn) {
                        t.top = t.lastColumnHeight;
                    }
                } else {
                    t.lastColumnHeight = t.cubeportfolio.height;
                    t.top = t.lastColumnHeight;
                }

            }

            t.matrice = [start, end];

            t._resizeSinglePageInline();

            // register callback function
            if ($.isFunction(t.options.singlePageInlineCallback)) {
                t.options.singlePageInlineCallback.call(t, t.dataArray[t.current].url, t.dataArray[t.current].element);
            }



            if (t.options.singlePageInlineInFocus) {
                t.scrollTop = $(window).scrollTop();

                // scroll
                $('body, html').animate({
                    scrollTop: t.wrap.offset().top - 150
                });
            }

        },

        _resizeSinglePageInline: function(removeLoadingMask) {

            var t = this,
                customHeight;

            removeLoadingMask = removeLoadingMask || false;

            t.height = t.content.outerHeight(true);

            t.cubeportfolio._layout();

            // repositionate the blocks with the best transition available
            t.cubeportfolio._processStyle(t.cubeportfolio.transition);

            if (removeLoadingMask) {
                t.wrap.removeClass('cbp-popup-loading');
            }

            t.cubeportfolio.$obj.addClass('cbp-popup-isOpening');

            t.wrap.css({
                height: t.height
            });

            t.wrap.css({
                top: t.top
            });

            customHeight = (t.lastColumn) ? t.height : 0;

            // resize main container height
            t.cubeportfolio._resizeMainContainer(t.cubeportfolio.transition, customHeight);

            if (t.options.singlePageInlineInFocus) {
                // scroll
                $('body, html').animate({
                    scrollTop: t.wrap.offset().top - 150
                });
            }

        },


        updateSinglePage: function(html) {

            var t = this,
                selectorSlider;

            t.content.html(html);

            t.wrap.addClass('cbp-popup-ready');

            t.wrap.removeClass('cbp-popup-loading');

            // update counter navigation
            if (t.options.singlePageShowCounter) {
                t.counter.text((t.current + 1) + ' of ' + t.counterTotal);
            }

            // instantiate slider if exists
            selectorSlider = t.content.find('.cbp-slider');
            if (selectorSlider) {
                t.slider = Object.create(slider);
                t.slider._init(t, selectorSlider);
            } else {
                t.slider = null;
            }

        },


        updateSinglePageInline: function(html) {

            var t = this,
                selectorSlider;

            t.content.html(html);

            t._loadSinglePageInline();

            // instantiate slider if exists
            selectorSlider = t.content.find('.cbp-slider');
            if (selectorSlider) {
                t.slider = Object.create(slider);
                t.slider._init(t, selectorSlider);
            } else {
                t.slider = null;
            }

        },


        /**
         * Wait to load all images
         */
        _loadSinglePageInline: function() {

            var t = this,
                imgs = [],
                i, img, propertyValue, src,
                matchUrl = /url\((['"]?)(.*?)\1\)/g;

            // loading background image of plugin
            propertyValue = t.wrap.children().css('backgroundImage');
            if (propertyValue) {
                var match;
                while ((match = matchUrl.exec(propertyValue))) {
                    imgs.push({
                        src: match[2]
                    });
                }
            }

            // get all elements
            t.wrap.find('*').each(function() {

                var elem = $(this);

                if (elem.is('img:uncached')) {
                    imgs.push({
                        src: elem.attr('src'),
                        element: elem[0]
                    });
                }

                // background image
                propertyValue = elem.css('backgroundImage');
                if (propertyValue) {
                    var match;
                    while ((match = matchUrl.exec(propertyValue))) {
                        imgs.push({
                            src: match[2],
                            element: elem[0]
                        });
                    }
                }
            });

            var imgsLength = imgs.length,
                imgsLoaded = 0;

            if (imgsLength === 0) {
                t._resizeSinglePageInline(true);
            }

            var loadImage = function() {
                imgsLoaded++;

                if (imgsLoaded == imgsLength) {
                    t._resizeSinglePageInline(true);
                }
            };

            // load  the image and call _beforeDisplay method
            for (i = 0; i < imgsLength; i++) {
                img = new Image();
                $(img).on('load' + eventNamespace + ' error' + eventNamespace, loadImage);
                img.src = imgs[i].src;
            }
        },


        isImage: function(el) {

            var t = this,
                img = new Image();

            t.tooggleLoading(true);

            if ($('<img src="' + el.src + '">').is('img:uncached')) {

                $(img).on('load' + eventNamespace + ' error' + eventNamespace, function() {

                    t.updateImagesMarkup(el.src, el.title, (t.current + 1) + ' of ' + t.counterTotal);

                    t.tooggleLoading(false);

                });
                img.src = el.src;

            } else {

                t.updateImagesMarkup(el.src, el.title, (t.current + 1) + ' of ' + t.counterTotal);

                t.tooggleLoading(false);
            }


        },

        isVimeo: function(el) {

            var t = this;

            t.updateVideoMarkup(el.src, el.title, (t.current + 1) + ' of ' + t.counterTotal);

        },

        isYoutube: function(el) {

            var t = this;

            t.updateVideoMarkup(el.src, el.title, (t.current + 1) + ' of ' + t.counterTotal);

        },

        isTed: function(el) {

            var t = this;

            t.updateVideoMarkup(el.src, el.title, (t.current + 1) + ' of ' + t.counterTotal);

        },

        isSelfHosted: function(el) {

            var t = this;

            t.updateSelfHostedVideo(el.src, el.title, (t.current + 1) + ' of ' + t.counterTotal);

        },

        updateSelfHostedVideo: function(src, title, counter) {

            var t = this, i;
            t.wrap.addClass('cbp-popup-lightbox-isIframe');

            var markup = '<div class="cbp-popup-lightbox-iframe">' +
                '<video controls="controls" width="100%" height="auto">';

            for (i = 0; i < src.length; i++) {
                if (/(\.mp4)/i.test(src[i])) {
                    markup += '<source src="' + src[i] + '" type="video/mp4">';
                } else if (/(\.ogg)|(\.ogv)/i.test(src[i])) {
                    markup += '<source src="' + src[i] + '" type="video/ogg">';
                } else if (/(\.webm)/i.test(src[i])) {
                    markup += '<source src="' + src[i] + '" type="video/webm">';
                }
            }

            markup += 'Your browser does not support the video tag.' +
                '</video>' +
                '<div class="cbp-popup-lightbox-bottom">' +
                ((title) ? '<div class="cbp-popup-lightbox-title">' + title + '</div>' : '') +
                ((t.options.lightboxShowCounter) ? '<div class="cbp-popup-lightbox-counter">' + counter + '</div>' : '') +
                '</div>' +
                '</div>';

            t.content.html(markup);

            t.wrap.addClass('cbp-popup-ready');

            t.preloadNearbyImages();

        },

        updateVideoMarkup: function(src, title, counter) {

            var t = this;
            t.wrap.addClass('cbp-popup-lightbox-isIframe');

            var markup = '<div class="cbp-popup-lightbox-iframe">' +
                '<iframe src="' + src + '" frameborder="0" allowfullscreen scrolling="no"></iframe>' +
                '<div class="cbp-popup-lightbox-bottom">' +
                ((title) ? '<div class="cbp-popup-lightbox-title">' + title + '</div>' : '') +
                ((t.options.lightboxShowCounter) ? '<div class="cbp-popup-lightbox-counter">' + counter + '</div>' : '') +
                '</div>' +
                '</div>';

            t.content.html(markup);

            t.wrap.addClass('cbp-popup-ready');

            t.preloadNearbyImages();

        },

        updateImagesMarkup: function(src, title, counter) {

            var t = this;

            t.wrap.removeClass('cbp-popup-lightbox-isIframe');

            var markup = '<div class="cbp-popup-lightbox-figure">' +
                '<img src="' + src + '" class="cbp-popup-lightbox-img" ' + t.dataActionImg + ' />' +
                '<div class="cbp-popup-lightbox-bottom">' +
                ((title) ? '<div class="cbp-popup-lightbox-title">' + title + '</div>' : '') +
                ((t.options.lightboxShowCounter) ? '<div class="cbp-popup-lightbox-counter">' + counter + '</div>' : '') +
                '</div>' +
                '</div>';

            t.content.html(markup);

            t.wrap.addClass('cbp-popup-ready');

            t.resizeImage();

            t.preloadNearbyImages();

        },

        next: function() {

            var t = this;

            t[t.type + 'JumpTo'](1);

        },

        prev: function() {

            var t = this;

            t[t.type + 'JumpTo'](-1);

        },

        lightboxJumpTo: function(index) {

            var t = this,
                el;

            t.current = t.getIndex(t.current + index);

            // get the current element
            el = t.dataArray[t.current];

            // call function if current element is image or video (iframe)
            t[el.type](el);

        },


        singlePageJumpTo: function(index) {

            var t = this;

            t.current = t.getIndex(t.current + index);

            // register singlePageCallback function
            if ($.isFunction(t.options.singlePageCallback)) {
                t.resetWrap();

                // go to top of the page (reset scroll)
                t.wrap.scrollTop(0);

                t.wrap.addClass('cbp-popup-loading');
                t.options.singlePageCallback.call(t, t.dataArray[t.current].url, t.dataArray[t.current].element);

                if (t.options.singlePageDeeplinking) {
                    location.href = t.url + '#cbp=' + t.dataArray[t.current].url;
                }
            }
        },

        resetWrap: function() {

            var t = this;

            if (t.type === 'singlePage' && t.options.singlePageDeeplinking) {
                location.href = t.url + '#';
            }

        },

        getIndex: function(index) {

            var t = this;

            // go to interval [0, (+ or -)this.counterTotal.length - 1]
            index = index % t.counterTotal;

            // if index is less then 0 then go to interval (0, this.counterTotal - 1]
            if (index < 0) {
                index = t.counterTotal + index;
            }

            return index;

        },

        close: function(method, data) {

            var t = this;

            // now the popup is closed
            t.isOpen = false;

            if (t.type === 'singlePageInline') {

                if (method === 'open') {

                    t.wrap.addClass('cbp-popup-loading');

                    $(t.dataArray[t.current].element).parents('.cbp-item').removeClass('cbp-singlePageInline-active');

                    t.openSinglePageInline(data.blocks, data.currentBlock, data.fromOpen);

                } else {

                    t.matrice = [-1, -1];

                    t.cubeportfolio._layout();

                    // repositionate the blocks with the best transition available
                    t.cubeportfolio._processStyle(t.cubeportfolio.transition);

                    // resize main container height
                    t.cubeportfolio._resizeMainContainer(t.cubeportfolio.transition);

                    t.wrap.css({
                        height: 0
                    });

                    $(t.dataArray[t.current].element).parents('.cbp-item').removeClass('cbp-singlePageInline-active');

                    if (t.cubeportfolio.browser === 'ie8' || t.cubeportfolio.browser === 'ie9') {

                        // reset content
                        t.content.html('');

                        // hide the wrap
                        t.wrap.detach();

                        t.cubeportfolio.$obj.removeClass('cbp-popup-isOpening');

                        if (method === 'promise') {
                            if ($.isFunction(data.callback)) {
                                data.callback.call(t.cubeportfolio);
                            }
                        }

                    } else {

                        t.wrap.one(t.cubeportfolio.transitionEnd, function() {

                            // reset content
                            t.content.html('');

                            // hide the wrap
                            t.wrap.detach();

                            t.cubeportfolio.$obj.removeClass('cbp-popup-isOpening');

                            if (method === 'promise') {
                                if ($.isFunction(data.callback)) {
                                    data.callback.call(t.cubeportfolio);
                                }
                            }

                        });

                    }

                    if (t.options.singlePageInlineInFocus) {
                        $('body, html').animate({
                            scrollTop: t.scrollTop
                        });
                    }
                }

            } else if (t.type === 'singlePage') {

                t.resetWrap();

                $(window).scrollTop(t.scrollTop);

                // weird bug on mozilla. fixed with setTimeout
                setTimeout(function() {
                    t.stopScroll = true;

                    t.navigationWrap.css({
                        top: t.wrap.scrollTop()
                    });

                    t.wrap.removeClass('cbp-popup-singlePage-open cbp-popup-singlePage-sticky');

                    if (t.cubeportfolio.browser === 'ie8' || t.cubeportfolio.browser === 'ie9') {
                        // reset content
                        t.content.html('');

                        // hide the wrap
                        t.wrap.detach();

                        $('html').css({
                            overflow: '',
                            paddingRight: ''
                        });

                        t.navigationWrap.removeAttr('style');
                    }

                }, 0);

                t.wrap.one(t.cubeportfolio.transitionEnd, function() {

                    // reset content
                    t.content.html('');

                    // hide the wrap
                    t.wrap.detach();

                    $('html').css({
                        overflow: '',
                        paddingRight: ''
                    });

                    t.navigationWrap.removeAttr('style');

                });

            } else {

                $('html').css({
                    overflow: '',
                    paddingRight: ''
                });

                $(window).scrollTop(t.scrollTop);

                // reset content
                t.content.html('');

                // hide the wrap
                t.wrap.detach();

            }

        },

        tooggleLoading: function(state) {

            var t = this;

            t.stopEvents = state;
            t.wrap[(state) ? 'addClass' : 'removeClass']('cbp-popup-loading');

        },

        resizeImage: function() {

            // if lightbox is not open go out
            if (!this.isOpen) return;

            var height = $(window).height(),
                img = $('.cbp-popup-content').find('img'),
                padding = parseInt(img.css('margin-top'), 10) + parseInt(img.css('margin-bottom'), 10);

            img.css('max-height', (height - padding) + 'px');

        },

        preloadNearbyImages: function() {

            var arr = [],
                img, t = this,
                src;

            arr.push(t.getIndex(t.current + 1));
            arr.push(t.getIndex(t.current + 2));
            arr.push(t.getIndex(t.current + 3));
            arr.push(t.getIndex(t.current - 1));
            arr.push(t.getIndex(t.current - 2));
            arr.push(t.getIndex(t.current - 3));

            for (var i = arr.length - 1; i >= 0; i--) {

                if (t.dataArray[arr[i]].type === 'isImage') {

                    src = t.dataArray[arr[i]].src;

                    img = new Image();

                    if ($('<img src="' + src + '">').is('img:uncached')) {

                        //$(img).on('load.pm error.pm', {src: src }, function (e) {});

                        img.src = src;

                    }

                }

            }

        }

    };

    var slider = {

        _init: function(tt, obj) {

            var t = this;

            // current item active
            t.current = 0;

            // js element
            t.obj = obj;

            // jquery element
            t.$obj = $(obj);

            // create html markup and add css to plugin
            t._createMarkup();

            // add events
            t._events();

        },

        _createMarkup: function() {

            var t = this,
                arrowWrap,
                bulletWrap;

            // get ul object
            t.$ul = t.$obj.children('.cbp-slider-wrap');

            // get items
            t.$li = t.$ul.children('.cbp-slider-item');

            // add class active on first child
            t.$li.eq(0).addClass('cbp-slider-item-current');

            // get number of items
            t.$liLength = t.$li.length;

            // navigation element
            arrowWrap = $('<div/>', {
                'class': 'cbp-slider-arrowWrap',
            }).appendTo(t.$obj);

            // next element
            $('<div/>', {
                'class': 'cbp-slider-arrowNext',
                'data-action': 'nextItem'
            }).appendTo(arrowWrap);

            // prev element
            $('<div/>', {
                'class': 'cbp-slider-arrowPrev',
                'data-action': 'prevItem'
            }).appendTo(arrowWrap);

            bulletWrap = $('<div/>', {
                'class': 'cbp-slider-bulletWrap',
            }).appendTo(t.$obj);

            for (var i = 0; i < t.$liLength; i++) {

                var firstItem = (i === 0) ? ' cbp-slider-bullet-current' : '';

                $('<div/>', {
                    'class': 'cbp-slider-bullet' + firstItem,
                    'data-action': 'jumpToItem'
                }).appendTo(bulletWrap);
            }

        },

        _events: function() {

            var t = this;

            t.$obj.on('click' + eventNamespace, function(e) {
                var action = $(e.target).attr('data-action');

                if (t[action]) {
                    t[action](e);
                    e.preventDefault();
                }
            });

        },

        nextItem: function() {

            this.jumpTo(1);

        },

        prevItem: function() {

            this.jumpTo(-1);

        },

        jumpToItem: function(e) {

            var target = $(e.target);

            var index = target.index();

            this.jumpTo(index - this.current);
        },

        jumpTo: function(index) {

            var t = this,
                item2,
                item1 = this.$li.eq(this.current);

            // update item2
            this.current = this.getIndex(this.current + index);
            item2 = this.$li.eq(this.current);
            item2.addClass('cbp-slider-item-next');


            item2.animate({
                opacity: 1
            }, function() {
                item1.removeClass('cbp-slider-item-current');
                item2.removeClass('cbp-slider-item-next')
                    .addClass('cbp-slider-item-current')
                    .removeAttr('style');

                var bullets = $('.cbp-slider-bullet');
                bullets.removeClass('cbp-slider-bullet-current');

                bullets.eq(t.current).addClass('cbp-slider-bullet-current');
            });

        },

        getIndex: function(index) {

            // go to interval [0, (+ or -)this.counterTotal.length - 1]
            index = index % this.$liLength;

            // if index is less then 0 then go to interval (0, this.counterTotal - 1]
            if (index < 0) {
                index = this.$liLength + index;
            }

            return index;

        },

    };

    var pluginObject = {

        /**
         * cubeportfolio initialization
         *
         */
        _main: function(obj, options, callbackFunction) {
            var t = this;

            // reset style queue
            t.styleQueue = [];

            // store the state of the animation used for filters
            t.isAnimating = false;

            // default filter for plugin
            t.defaultFilter = '*';

            // registered events (observator & publisher pattern)
            t.registeredEvents = [];

            // register callback function
            if ($.isFunction(callbackFunction)) {
                t._registerEvent('initFinish', callbackFunction, true);
            }

            // extend options
            t.options = $.extend({}, $.fn.cubeportfolio.options, options);

            // js element
            t.obj = obj;

            // jquery element
            t.$obj = $(obj);

            // store main container width
            t.width = t.$obj.width();

            // add loading class and .cbp on container
            t.$obj.addClass('cbp cbp-loading');

            // jquery ul element
            t.$ul = t.$obj.children();

            // add class to ul
            t.$ul.addClass('cbp-wrapper');

            // hide the `ul` if lazyLoading or fadeIn options are enabled
            if (t.options.displayType === 'lazyLoading' || t.options.displayType === 'fadeIn') {
                t.$ul.css({
                    opacity: 0
                });
            }

            if (t.options.displayType === 'fadeInToTop') {
                t.$ul.css({
                    opacity: 0,
                    marginTop: 30
                });
            }

            // check support for modern browsers
            t._browserInfo();

            // create css and events
            t._initCSSandEvents();

            // prepare the blocks
            t._prepareBlocks();

            // is lazyLoading is enable wait to load all images and then show the main container. Otherwise show directly the main container
            if (t.options.displayType === 'lazyLoading' || t.options.displayType === 'sequentially' || t.options.displayType === 'bottomToTop' || t.options.displayType === 'fadeInToTop') {
                t._load();
            } else {
                t._beforeDisplay();
            }

        },


        /**
         * Get info about client browser
         */
        _browserInfo: function() {

            var t = this,
                appVersion = navigator.appVersion,
                transition, animation;

            if (appVersion.indexOf('MSIE 8.') !== -1) { // ie8
                t.browser = 'ie8';
            } else if (appVersion.indexOf('MSIE 9.') !== -1) { // ie9
                t.browser = 'ie9';
            } else if (appVersion.indexOf('MSIE 10.') !== -1) { // ie10
                t.browser = 'ie10';
            } else if (window.ActiveXObject || 'ActiveXObject' in window) { // ie11
                t.browser = 'ie11';
            } else if ((/android/gi).test(appVersion)) { // android
                t.browser = 'android';
            } else if ((/iphone|ipad|ipod/gi).test(appVersion)) { // ios
                t.browser = 'ios';
            } else if ((/chrome/gi).test(appVersion)) {
                t.browser = 'chrome';
            } else {
                t.browser = '';
            }

            // add class to plugin for additional support
            if (t.browser) {
                t.$obj.addClass('cbp-' + t.browser);
            }

            // Check if css3 properties (transition and transform) are available
            // what type of transition will be use: css or animate
            transition = t._styleSupport('transition');
            animation = t._styleSupport('animation');
            t.transition = t.transitionByFilter = (transition) ? 'css' : 'animate';

            if (t.transition == 'animate') return;

            t.transitionEnd = {
                WebkitTransition: 'webkitTransitionEnd',
                MozTransition: 'transitionend',
                OTransition: 'oTransitionEnd otransitionend',
                transition: 'transitionend'
            }[transition];

            t.animationEnd = {
                WebkitAnimation: 'webkitAnimationEnd',
                MozAnimation: 'Animationend',
                OAnimation: 'oAnimationEnd oanimationend',
                animation: 'animationend'
            }[animation];

            t.supportCSSTransform = t._styleSupport('transform');

            // check 3d transform support
            if (t.supportCSSTransform) {
                // add cssHooks to jquery css function
                t._cssHooks();
            }

        },


        /**
         * Feature testing for css3
         */
        _styleSupport: function(prop) {

            var vendorProp, supportedProp, i,
                // capitalize first character of the prop to test vendor prefix
                capProp = prop.charAt(0).toUpperCase() + prop.slice(1),
                prefixes = ['Moz', 'Webkit', 'O', 'ms'],
                div = document.createElement('div');

            if (prop in div.style) {
                // browser supports standard CSS property name
                supportedProp = prop;
            } else {
                // otherwise test support for vendor-prefixed property names
                for (i = prefixes.length - 1; i >= 0; i--) {
                    vendorProp = prefixes[i] + capProp;
                    if (vendorProp in div.style) {
                        supportedProp = vendorProp;
                        break;
                    }
                }
            }
            // avoid memory leak in IE
            div = null;

            return supportedProp;
        },


        /**
         * Add hooks for jquery.css
         */
        _cssHooks: function() {

            var t = this,
                transformCSS3;

            if (t._has3d()) { // 3d transform

                transformCSS3 = {
                    translate: function(x) {
                        return 'translate3d(' + x[0] + 'px, ' + x[1] + 'px, 0) ';
                    },
                    scale: function(x) {
                        return 'scale3d(' + x + ', ' + x + ', 1) ';
                    },
                    skew: function(x) {
                        return 'skew(' + x[0] + 'deg, ' + x[1] + 'deg) ';
                    }
                };

            } else { // 2d transform

                transformCSS3 = {
                    translate: function(x) {
                        return 'translate(' + x[0] + 'px, ' + x[1] + 'px) ';
                    },
                    scale: function(x) {
                        return 'scale(' + x + ') ';
                    },
                    skew: function(x) {
                        return 'skew(' + x[0] + 'deg, ' + x[1] + 'deg) ';
                    }
                };

            }

            // function used for cssHokks

            function setTransformFn(el, value, name) {
                var $el = $(el),
                    data = $el.data('transformFn') || {},
                    newData = {},
                    i,
                    transObj = {},
                    val,
                    trans,
                    scale,
                    values,
                    skew;

                newData[name] = value;

                $.extend(data, newData);

                for (i in data) {
                    val = data[i];
                    transObj[i] = transformCSS3[i](val);
                }

                trans = transObj.translate || '';
                scale = transObj.scale || '';
                skew = transObj.skew || '';
                values = trans + scale + skew;

                // set data back in el
                $el.data('transformFn', data);

                el.style[t.supportCSSTransform] = values;
            }

            // scale
            $.cssNumber.scale = true;

            $.cssHooks.scale = {
                set: function(elem, value) {

                    if (typeof value === 'string') {
                        value = parseFloat(value);
                    }

                    setTransformFn(elem, value, 'scale');

                },
                get: function(elem, computed) {

                    var transform = $.data(elem, 'transformFn');
                    return (transform && transform.scale) ? transform.scale : 1;
                }

            };

            $.fx.step.scale = function(fx) {
                $.cssHooks.scale.set(fx.elem, fx.now + fx.unit);
            };


            // translate
            $.cssNumber.translate = true;

            $.cssHooks.translate = {
                set: function(elem, value) {

                    setTransformFn(elem, value, 'translate');

                },

                get: function(elem, computed) {

                    var transform = $.data(elem, 'transformFn');
                    return (transform && transform.translate) ? transform.translate : [0, 0];

                }
            };

            // skew
            $.cssNumber.skew = true;

            $.cssHooks.skew = {
                set: function(elem, value) {

                    setTransformFn(elem, value, 'skew');

                },

                get: function(elem, computed) {

                    var transform = $.data(elem, 'transformFn');
                    return (transform && transform.skew) ? transform.skew : [0, 0];

                }
            };

        },


        /**
         * Testing for CSS 3D Transforms Support
         * https://gist.github.com/lorenzopolidori/3794226
         */
        _has3d: function() {
            var el = document.createElement('p'),
                has3d,
                transforms = {
                    'webkitTransform': '-webkit-transform',
                    'OTransform': '-o-transform',
                    'msTransform': '-ms-transform',
                    'MozTransform': '-moz-transform',
                    'transform': 'transform'
                };

            // Add it to the body to get the computed style
            document.body.insertBefore(el, null);

            for (var t in transforms) {
                if (el.style[t] !== undefined) {
                    el.style[t] = 'translate3d(1px,1px,1px)';
                    has3d = window.getComputedStyle(el).getPropertyValue(transforms[t]);
                }
            }

            document.body.removeChild(el);

            return (has3d !== undefined && has3d.length > 0 && has3d !== 'none');
        },


        /**
         * Prepare and store the blocks
         */
        _prepareBlocks: function() {

            var t = this,
                element;

            // cache the blocks
            t.blocks = t.$ul.children('.cbp-item');

            t.blocksAvailable = t.blocks;

            t.blocks.wrapInner('<div class="cbp-item-wrapper"></div>');

            // if caption is active
            if (t.options.caption) {
                t._captionInit();
            }
        },


        /**
         * Init function for all captions
         */
        _captionInit: function() {

            var t = this;

            t.$obj.addClass('cbp-caption-' + t.options.caption);

            t['_' + t.options.caption + 'Caption']();

        },


        /**
         * Destroy function for all captions
         */
        _captionDestroy: function() {

            var t = this;

            t.$obj.removeClass('cbp-caption-' + t.options.caption);

            t['_' + t.options.caption + 'CaptionDestroy']();

        },


        _noneCaption: function() {

        },

        _noneCaptionDestroy: function() {

        },


        /**
         * Push Top hover effect
         */
        _pushTopCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap');

                    normal.animate({
                        bottom: '100%'
                    }, 'fast');
                    hover.animate({
                        bottom: 0
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap');

                    normal.animate({
                        bottom: 0
                    }, 'fast');
                    hover.animate({
                        bottom: '-100%'
                    }, 'fast');

                });

            }

        },


        /**
         * Push Top hover effect destroy
         */
        _pushTopCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-defaultWrap').removeAttr('style');
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },


        /**
         * Push Down hover effect
         */
        _pushDownCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap');

                    normal.animate({
                        bottom: '-100%'
                    }, 'fast');
                    hover.animate({
                        bottom: 0
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap');

                    normal.animate({
                        bottom: 0
                    }, 'fast');
                    hover.animate({
                        bottom: '100%'
                    }, 'fast');

                });

            }

        },


        /**
         * Push Down hover effect destroy
         */
        _pushDownCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-defaultWrap').removeAttr('style');
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },


        /**
         * Reveal Bottom hover effect
         */
        _revealBottomCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap');

                    normal.animate({
                        bottom: '100%'
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap');

                    normal.animate({
                        bottom: 0
                    }, 'fast');

                });

            }

        },


        /**
         * Reveal Bottom hover effect destroy
         */
        _revealBottomCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-defaultWrap').removeAttr('style');
            }

        },


        /**
         * Reveal Top hover effect
         */
        _revealTopCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap');

                    normal.animate({
                        bottom: '-100%'
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap');

                    normal.animate({
                        bottom: 0
                    }, 'fast');

                });

            }

        },


        /**
         * Reveal Top hover effect destroy
         */
        _revealTopCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-defaultWrap').removeAttr('style');
            }

        },


        /**
         * Overlay Bottom Reveal hover effect
         */
        _overlayBottomRevealCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        height = me.find('.cbp-caption-activeWrap').height();

                    normal.animate({
                        bottom: height
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap');

                    normal.animate({
                        bottom: 0
                    }, 'fast');

                });

            }

        },


        /**
         * Overlay Bottom Reveal hover effect destroy
         */
        _overlayBottomRevealCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-defaultWrap').removeAttr('style');
            }

        },


        /**
         * Overlay Bottom Push hover effect
         */
        _overlayBottomPushCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap'),
                        height = hover.height();

                    normal.animate({
                        bottom: height
                    }, 'fast');
                    hover.animate({
                        bottom: 0
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap'),
                        height = hover.height();

                    normal.animate({
                        bottom: 0
                    }, 'fast');
                    hover.animate({
                        bottom: -height
                    }, 'fast');

                });

            }

        },


        /**
         * Push Up hover effect destroy
         */
        _overlayBottomPushCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-defaultWrap').removeAttr('style');
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },


        /**
         * Overlay Bottom hover effect
         */
        _overlayBottomCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    $(this).find('.cbp-caption-activeWrap').animate({
                        bottom: 0
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var hover = $(this).find('.cbp-caption-activeWrap');
                    hover.animate({
                        bottom: -hover.height()
                    }, 'fast');

                });

            }

        },

        /**
         * Overlay Bottom hover effect destroy
         */
        _overlayBottomCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },


        /**
         * Move Right hover effect
         */
        _moveRightCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    $(this).find('.cbp-caption-activeWrap').animate({
                        left: 0
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function() {

                    var hover = $(this).find('.cbp-caption-activeWrap');
                    hover.animate({
                        left: -hover.width()
                    }, 'fast');

                });

            }

        },

        /**
         * Move Right hover effect destroy
         */
        _moveRightCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },


        /**
         * Reveal Left hover effect
         */
        _revealLeftCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    $(this).find('.cbp-caption-activeWrap').animate({
                        left: 0
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function() {

                    var hover = $(this).find('.cbp-caption-activeWrap');
                    hover.animate({
                        left: hover.width()
                    }, 'fast');

                });

            }

        },

        /**
         * Reveal Left hover effect destroy
         */
        _revealLeftCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },


        /**
         * Minimal hover effect
         */
        _minimalCaption: function() {

            var t = this;

        },

        /**
         * Minimal hover effect destroy
         */
        _minimalCaptionDestroy: function() {

            var t = this;

        },


        /**
         * Fade hover effect
         */
        _fadeInCaption: function() {

            var t = this,
                opacity;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                opacity = (t.browser === 'ie9') ? 1 : 0.8;

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    $(this).find('.cbp-caption-activeWrap').animate({
                        opacity: opacity
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function() {

                    $(this).find('.cbp-caption-activeWrap').animate({
                        opacity: 0
                    }, 'fast');

                });

            }

        },

        /**
         * Fade hover effect destroy
         */
        _fadeInCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },

        /**
         * Move Left hover effect
         */
        _overlayRightAlongCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap');

                    normal.animate({
                        left: hover.width() / 2
                    }, 'fast');
                    hover.animate({
                        left: 0
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap');

                    normal.animate({
                        left: 0
                    }, 'fast');
                    hover.animate({
                        left: -hover.width()
                    }, 'fast');

                });

            }

        },

        /**
         * Move Left hover effect destroy
         */
        _overlayRightAlongCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-defaultWrap').removeAttr('style');
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },

        /**
         * Overlay Bottom Along hover effect
         */
        _overlayBottomAlongCaption: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap');

                    normal.animate({
                        bottom: hover.height() / 2
                    }, 'fast');
                    hover.animate({
                        bottom: 0
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function(e) {

                    var me = $(this),
                        normal = me.find('.cbp-caption-defaultWrap'),
                        hover = me.find('.cbp-caption-activeWrap');

                    normal.animate({
                        bottom: 0
                    }, 'fast');
                    hover.animate({
                        bottom: -hover.height()
                    }, 'fast');

                });

            }

        },

        /**
         * Overlay Bottom Along hover effect destroy
         */
        _overlayBottomAlongCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-defaultWrap').removeAttr('style');
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }


        },


        /**
         * Zoom hover effect
         */
        _zoomCaption: function() {

            var t = this,
                opacity;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {

                opacity = (t.browser === 'ie9') ? 1 : 0.8;

                $('.cbp-caption').on('mouseenter' + eventNamespace, function() {

                    $(this).find('.cbp-caption-activeWrap').animate({
                        opacity: opacity
                    }, 'fast');

                }).on('mouseleave' + eventNamespace, function() {

                    $(this).find('.cbp-caption-activeWrap').animate({
                        opacity: 0
                    }, 'fast');

                });

            }

        },

        /**
         * Zoom hover effect destroy
         */
        _zoomCaptionDestroy: function() {

            var t = this;

            // is legacy browser
            if (t.browser === 'ie8' || t.browser === 'ie9') {
                $('.cbp-caption').off('mouseenter' + eventNamespace + ' mouseleave' + eventNamespace);
                $('.cbp-caption').find('.cbp-caption-activeWrap').removeAttr('style');
            }

        },


        /**
         * Init main components for plugin
         */
        _initCSSandEvents: function() {

            var t = this,
                n, width, currentWidth, windowWidth;

            // resize
            $(window).on('resize' + eventNamespace, function() {

                if (n) {
                    clearTimeout(n);
                }

                n = setTimeout(function() {

                    if (t.browser === 'ie8') {
                        windowWidth = $(window).width();

                        if (currentWidth === undefined || currentWidth != windowWidth) {
                            currentWidth = windowWidth;
                        } else {
                            return;
                        }

                    }

                    t.$obj.removeClass('cbp-no-transition cbp-appendItems-loading');

                    // make responsive
                    if (t.options.gridAdjustment === 'responsive') {
                        t._responsiveLayout();
                    }

                    // reposition the blocks
                    t._layout();

                    // repositionate the blocks with the best transition available
                    t._processStyle(t.transition);

                    // resize main container height
                    t._resizeMainContainer(t.transition);

                    if (t.lightbox) {
                        t.lightbox.resizeImage();
                    }

                    if (t.singlePage) {

                        if (t.singlePage.options.singlePageStickyNavigation) {

                            width = t.singlePage.wrap[0].clientWidth;

                            if (width > 0) {
                                t.singlePage.navigationWrap.width(width);

                                // set navigation width='window width' to center the buttons
                                t.singlePage.navigation.width(width);
                            }

                        }
                    }

                    if (t.singlePageInline && t.singlePageInline.isOpen) {
                        // @todo => must add support for this features in the future
                        t.singlePageInline.close(); // workaround
                    }

                }, 50);
            });

        },


        /**
         * Wait to load all images
         */
        _load: function() {

            var t = this,
                imgs = [],
                i, img, propertyValue, src,
                matchUrl = /url\((['"]?)(.*?)\1\)/g;

            // loading background image of plugin
            propertyValue = t.$obj.children().css('backgroundImage');
            if (propertyValue) {
                var match;
                while ((match = matchUrl.exec(propertyValue))) {
                    imgs.push({
                        src: match[2]
                    });
                }
            }

            // get all elements
            t.$obj.find('*').each(function() {

                var elem = $(this);

                if (elem.is('img:uncached')) {
                    imgs.push({
                        src: elem.attr('src'),
                        element: elem[0]
                    });
                }

                // background image
                propertyValue = elem.css('backgroundImage');
                if (propertyValue) {
                    var match;
                    while ((match = matchUrl.exec(propertyValue))) {
                        imgs.push({
                            src: match[2],
                            element: elem[0]
                        });
                    }
                }
            });

            var imgsLength = imgs.length,
                imgsLoaded = 0;

            if (imgsLength === 0) {
                t._beforeDisplay();
            }

            var loadImage = function() {
                imgsLoaded++;

                if (imgsLoaded == imgsLength) {
                    t._beforeDisplay();
                    return false;
                }
            };

            // load  the image and call _beforeDisplay method
            for (i = 0; i < imgsLength; i++) {
                img = new Image();
                $(img).on('load' + eventNamespace + ' error' + eventNamespace, loadImage);
                img.src = imgs[i].src;
            }
        },

        /**
         * Before display make some work
         */
        _beforeDisplay: function() {

            var t = this;

            if (t.options.animationType) {
                // if filter need some initialization to be done before displaying the plugin
                if (t['_' + t.options.animationType + 'Init']) {

                    t['_' + t.options.animationType + 'Init']();

                }

                // add filter class to plugin
                t.$obj.addClass('cbp-animation-' + t.options.animationType);

                // set column width one time
                t.localColumnWidth = t.blocks.eq(0).outerWidth() + t.options.gapVertical;

                if (t.options.defaultFilter === '' || t.options.defaultFilter === '*') {
                    t._display();
                } else {

                    t.filter(t.options.defaultFilter, function() {
                        t._display();
                    }, t);

                }

            }

        },

        /**
         * Show the plugin
         */
        _display: function() {

            var t = this,
                i, item;

            // if responsive
            if (t.options.gridAdjustment === 'responsive') {
                t._responsiveLayout();
            }

            // make layout
            t._layout();

            // need css for positionate the blocks
            t._processStyle('css');

            // resize main container height
            t._resizeMainContainer('css');


            // show the plugin
            if (t.options.displayType === 'lazyLoading' || t.options.displayType === 'fadeIn') {
                t.$ul.animate({
                    opacity: 1
                }, t.options.displayTypeSpeed);
            }

            if (t.options.displayType === 'fadeInToTop') {
                t.$ul.animate({
                    opacity: 1,
                    marginTop: 0
                }, t.options.displayTypeSpeed);
            }

            if (t.options.displayType === 'sequentially') {
                i = 0;
                t.blocks.css('opacity', 0);

                (function displayItems() {
                    item = t.blocksAvailable.eq(i++);

                    if (item.length) {
                        item.animate({
                            opacity: 1
                        });
                        setTimeout(displayItems, t.options.displayTypeSpeed);
                    }
                })();
            }

            if (t.options.displayType === 'bottomToTop') {
                i = 0;
                t.blocks.css({
                    'opacity': 0,
                    marginTop: 80
                });

                (function displayItems() {
                    item = t.blocksAvailable.eq(i++);

                    if (item.length) {
                        item.animate({
                            opacity: 1,
                            marginTop: 0
                        }, 400);
                        setTimeout(displayItems, t.options.displayTypeSpeed);
                    }
                })();
            }

            // show main container
            setTimeout(function() {

                // remove loading class
                t.$obj.removeClass('cbp-loading');

                t._triggerEvent('initFinish');

                // trigger public event initComplete
                t.$obj.trigger('initComplete');

                // the plugin is ready to show
                t.$obj.addClass('cbp-ready');

            }, 0);

            // default value for lightbox
            t.lightbox = null;

            // LIGHTBOX
            if (t.$obj.find(t.options.lightboxDelegate)) {

                t.lightbox = Object.create(popup);

                t.lightbox.init(t, 'lightbox');

                t.$obj.on('click' + eventNamespace, t.options.lightboxDelegate, function(e) {

                    t.lightbox.openLightbox(t.blocksAvailable, this);

                    e.preventDefault();

                });

            }

            // default value for singlePage
            t.singlePage = null;

            // SINGLEPAGE
            if (t.$obj.find(t.options.singlePageDelegate)) {

                t.singlePage = Object.create(popup);

                t.singlePage.init(t, 'singlePage');

                t.$obj.on('click' + eventNamespace, t.options.singlePageDelegate, function(e) {
                    e.preventDefault();

                    t.singlePage.openSinglePage(t.blocksAvailable, this);

                });

            }

            // default value for singlePageInline
            t.singlePageInline = null;

            // SINGLEPAGEInline
            if (t.$obj.find(t.options.singlePageInlineDelegate)) {

                t.singlePageInline = Object.create(popup);

                t.singlePageInline.init(t, 'singlePageInline');

                t.$obj.on('click' + eventNamespace, t.options.singlePageInlineDelegate, function(e) {

                    t.singlePageInline.openSinglePageInline(t.blocksAvailable, this);

                    e.preventDefault();

                });

            }
        },


        /**
         * Build the layout
         */
        _layout: function() {

            var t = this;

            // reset layout
            t._layoutReset();

            t.blocksAvailable.each(function(index, el) {

                var $me = $(el),
                    colNr = Math.ceil($me.outerWidth() / t.localColumnWidth),
                    singlePageInlineGap = 0;

                colNr = Math.min(colNr, t.cols);

                if (t.singlePageInline && (index >= t.singlePageInline.matrice[0] && index <= t.singlePageInline.matrice[1])) {
                    singlePageInlineGap = t.singlePageInline.height;
                }

                if (colNr === 1) {

                    t._placeBlocks($me, t.colVert, singlePageInlineGap);

                } else {

                    var count = t.cols + 1 - colNr,
                        groupVert = [],
                        groupColVert,
                        i;

                    for (i = 0; i < count; i++) {

                        groupColVert = t.colVert.slice(i, i + colNr);
                        groupVert[i] = Math.max.apply(Math, groupColVert);

                    }

                    t._placeBlocks($me, groupVert, singlePageInlineGap);

                }

            });

        },


        /**
         * Reset the layout
         */
        _layoutReset: function() {

            var c, t = this,
                columnData;

            // @options gridAdjustment = alignCenter
            if (t.options.gridAdjustment === 'alignCenter') {

                t.$obj.attr('style', '');

                t.width = t.$obj.width();

                // calculate numbers of columns
                t.cols = Math.max(Math.floor((t.width + t.options.gapVertical) / t.localColumnWidth), 1);

                t.width = t.cols * t.localColumnWidth - t.options.gapVertical;
                t.$obj.css('max-width', t.width);

            } else {

                t.width = t.$obj.width();

                // calculate numbers of columns
                t.cols = Math.max(Math.floor((t.width + t.options.gapVertical) / t.localColumnWidth), 1);

            }


            t.colVert = [];

            c = t.cols;

            while (c--) {
                t.colVert.push(0);
            }

        },

        /**
         * Make this plugin responsive
         */
        _responsiveLayout: function() {

            var t = this,
                procent, extra;

            if (!t.columnWidthCache) {
                t.columnWidthCache = t.localColumnWidth;
            } else {
                t.localColumnWidth = t.columnWidthCache;
            }

            t.width = t.$obj.width() + t.options.gapVertical;

            t.cols = Math.max(Math.floor(t.width / t.localColumnWidth), 1);

            extra = t.width % t.localColumnWidth;

            if (extra / t.localColumnWidth > 0.5) {
                t.localColumnWidth = t.localColumnWidth - (t.localColumnWidth - extra) / (t.cols + 1);
            } else {
                t.localColumnWidth = t.localColumnWidth + extra / t.cols;
            }

            t.localColumnWidth = parseInt(t.localColumnWidth, 10);

            procent = t.localColumnWidth / t.columnWidthCache;


            t.blocks.each(function(index, el) {

                var me = $(this),
                    data = $.data(this, 'cbp-wxh');

                if (!data) {
                    data = $.data(this, 'cbp-wxh', {
                        width: me.outerWidth(),
                        height: me.outerHeight()
                    });
                }


                me.css('width', t.localColumnWidth - t.options.gapVertical);
                me.css('height', Math.floor(data.height * procent));

            });

            if (t.blocksClone) {

                t.blocksClone.each(function(index, el) {

                    var me = $(this),
                        data = $.data(this, 'cbp-wxh');

                    if (!data) {
                        data = $.data(this, 'cbp-wxh', {
                            width: me.outerWidth(),
                            height: me.outerHeight()
                        });
                    }

                    me.css('width', t.localColumnWidth - t.options.gapVertical);
                    me.css('height', Math.floor(data.height * procent));

                });

            }

        },

        /**
         * Resize main container vertically
         */
        _resizeMainContainer: function(transition, customHeight) {

            var t = this;

            customHeight = customHeight || 0;

            // set container height for `overflow: hidden` to be applied
            t.height = Math.max.apply(Math, t.colVert) + customHeight;

            t.$obj[transition]({
                height: t.height - t.options.gapHorizontal
            }, 400);

        },

        /**
         * Process style queue
         */
        _processStyle: function(transition) {

            var t = this;

            for (var i = t.styleQueue.length - 1; i >= 0; i--) {

                t.styleQueue[i].$el[transition](t.styleQueue[i].style);
            }

            t.styleQueue = [];

        },


        /**
         * Place the blocks in the correct order
         */
        _placeBlocks: function($block, vert, singlePageInlineGap) {

            var t = this,
                minVert = Math.min.apply(Math, vert),
                coll = 0,
                x, y, setHeight, colsLen, i, len;



            for (i = 0, len = vert.length; i < len; i++) {
                if (vert[i] === minVert) {
                    coll = i;
                    break;
                }
            }

            if (t.singlePageInline && singlePageInlineGap !== 0) {
                t.singlePageInline.top = minVert;
            }

            minVert += singlePageInlineGap;

            // position the block
            x = Math.round(t.localColumnWidth * coll);
            y = Math.round(minVert);

            // add block to queue
            t.styleQueue.push({
                $el: $block,
                style: (t.supportCSSTransform) ? t._withCSS3(x, y) : t._withCSS2(x, y)
            });

            setHeight = minVert + $block.outerHeight() + t.options.gapHorizontal;
            colsLen = t.cols + 1 - len;

            for (i = 0; i < colsLen; i++) {
                t.colVert[coll + i] = setHeight;
            }

        },

        /**
         * Use position absolute with left and top
         */
        _withCSS2: function(x, y) {
            return {
                left: x,
                top: y
            };
        },


        /**
         * Use css3 translate function
         */
        _withCSS3: function(x, y) {
            return {
                translate: [x, y]
            };
        },



        /*  -----------------------------------------------------
                                FILTERS
            ----------------------------------------------------- */

        /**
         * Duplicate the blocks in a new `ul`
         */
        _duplicateContent: function(cssObj) {

            var t = this;

            t.$ulClone = t.$ul.clone();

            t.blocksClone = t.$ulClone.children();

            t.$ulClone.css(cssObj);

            t.ulHidden = 'clone';

            t.$obj.append(t.$ulClone);

        },


        /**
         * FadeOut filter
         */
        _fadeOutFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this;

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');


            t.blocksAvailable = t.blocks.filter(filter);

            if (on2offBlocks.length) {

                t.styleQueue.push({
                    $el: on2offBlocks,
                    style: {
                        opacity: 0
                    }
                });

            }

            if (off2onBlocks.length) {

                t.styleQueue.push({
                    $el: off2onBlocks,
                    style: {
                        opacity: 1
                    }
                });

            }

            // call layout
            t._layout();

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            // filter had finished his job
            setTimeout(function() {
                t._filterFinish();
            }, 400);

        },


        /**
         * Quicksand filter
         */
        _quicksandFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this;

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            t.blocksAvailable = t.blocks.filter(filter);

            if (on2offBlocks.length) {

                t.styleQueue.push({
                    $el: on2offBlocks,
                    style: {
                        scale: 0.01,
                        opacity: 0
                    }
                });

            }

            if (off2onBlocks.length) {

                t.styleQueue.push({
                    $el: off2onBlocks,
                    style: {
                        scale: 1,
                        opacity: 1
                    }
                });

            }

            // call layout
            t._layout();

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            setTimeout(function() {
                t._filterFinish();
            }, 400);

        },


        /**
         * flipOut filter
         */
        _flipOutFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this;

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            t.blocksAvailable = t.blocks.filter(filter);

            if (on2offBlocks.length) {

                if (t.browser === 'ie8' || t.browser === 'ie9') {

                    t.styleQueue.push({
                        $el: on2offBlocks,
                        style: {
                            opacity: 0
                        }
                    });

                } else {
                    on2offBlocks.find('.cbp-item-wrapper').removeClass('cbp-animation-flipOut-in').addClass('cbp-animation-flipOut-out');
                }

            }

            if (off2onBlocks.length) {

                if (t.browser === 'ie8' || t.browser === 'ie9') {
                    t.styleQueue.push({
                        $el: off2onBlocks,
                        style: {
                            opacity: 1
                        }
                    });
                } else {
                    off2onBlocks.find('.cbp-item-wrapper').removeClass('cbp-animation-flipOut-out').addClass('cbp-animation-flipOut-in');
                }
            }

            // call layout
            t._layout();

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            setTimeout(function() {
                t._filterFinish();
            }, 400);

        },


        /**
         * flipBottom filter
         */
        _flipBottomFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this;

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            t.blocksAvailable = t.blocks.filter(filter);

            if (on2offBlocks.length) {

                if (t.browser === 'ie8' || t.browser === 'ie9') {

                    t.styleQueue.push({
                        $el: on2offBlocks,
                        style: {
                            opacity: 0
                        }
                    });

                } else {
                    on2offBlocks.find('.cbp-item-wrapper').removeClass('cbp-animation-flipBottom-in').addClass('cbp-animation-flipBottom-out');
                }

            }

            if (off2onBlocks.length) {

                if (t.browser === 'ie8' || t.browser === 'ie9') {
                    t.styleQueue.push({
                        $el: off2onBlocks,
                        style: {
                            opacity: 1
                        }
                    });
                } else {
                    off2onBlocks.find('.cbp-item-wrapper').removeClass('cbp-animation-flipBottom-out').addClass('cbp-animation-flipBottom-in');
                }

            }

            // call layout
            t._layout();

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            setTimeout(function() {
                t._filterFinish();
            }, 400);

        },


        /**
         * scaleSides filter
         */
        _scaleSidesFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this;

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            t.blocksAvailable = t.blocks.filter(filter);

            if (on2offBlocks.length) {

                if (t.browser === 'ie8' || t.browser === 'ie9') {

                    t.styleQueue.push({
                        $el: on2offBlocks,
                        style: {
                            opacity: 0
                        }
                    });

                } else {
                    on2offBlocks.find('.cbp-item-wrapper').removeClass('cbp-animation-scaleSides-in').addClass('cbp-animation-scaleSides-out');
                }

            }

            if (off2onBlocks.length) {

                if (t.browser === 'ie8' || t.browser === 'ie9') {
                    t.styleQueue.push({
                        $el: off2onBlocks,
                        style: {
                            opacity: 1
                        }
                    });
                } else {
                    off2onBlocks.find('.cbp-item-wrapper').removeClass('cbp-animation-scaleSides-out').addClass('cbp-animation-scaleSides-in');
                }

            }

            // call layout
            t._layout();

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            setTimeout(function() {
                t._filterFinish();
            }, 400);

        },


        /**
         * skew filter
         */
        _skewFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this;

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            t.blocksAvailable = t.blocks.filter(filter);

            if (on2offBlocks.length) {

                t.styleQueue.push({
                    $el: on2offBlocks,
                    style: {
                        skew: [50, 0],
                        scale: 0.01,
                        opacity: 0
                    }
                });

            }

            if (off2onBlocks.length) {

                t.styleQueue.push({
                    $el: off2onBlocks,
                    style: {
                        skew: [0, 0],
                        scale: 1,
                        opacity: 1
                    }
                });

            }

            // call layout
            t._layout();

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            setTimeout(function() {
                t._filterFinish();
            }, 400);

        },


        /**
         *  Slide Up Sequentially custom init
         */
        _sequentiallyInit: function() {

            this.transitionByFilter = 'css';

        },

        /**
         * Slide Up Sequentially filter
         */
        _sequentiallyFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this,
                tempBlocks = t.blocks,
                oldBlocksAvailable = t.blocksAvailable;

            t.blocksAvailable = t.blocks.filter(filter);

            t.$obj.addClass('cbp-no-transition');

            if (t.browser === 'ie8' || t.browser === 'ie9') {
                oldBlocksAvailable[t.transition]({
                    top: '-=30',
                    opacity: 0
                }, 300);
            } else {
                oldBlocksAvailable[t.transition]({
                    top: -30,
                    opacity: 0
                });
            }

            setTimeout(function() {

                if (filter !== '*') {

                    // get elements that are hidden and will be visible
                    off2onBlocks = off2onBlocks.filter(filter);

                    // get visible elements that will pe hidden
                    on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

                }

                // remove hidden class
                off2onBlocks.removeClass('cbp-item-hidden');

                if (on2offBlocks.length) {

                    on2offBlocks.css({
                        'display': 'none'
                    });

                    //t.styleQueue.push({ $el: on2offBlocks, style: { opacity: 0 } });

                }

                if (off2onBlocks.length) {

                    off2onBlocks.css('display', 'block');

                    //t.styleQueue.push({ $el: off2onBlocks, style: { opacity: 1 } });

                }

                // call layout
                t._layout();

                // trigger style queue and the animations
                t._processStyle(t.transitionByFilter);

                // resize main container height
                t._resizeMainContainer(t.transition);

                // ie8 & ie9 trick
                if (t.browser === 'ie8' || t.browser === 'ie9') {
                    t.blocksAvailable.css('top', '-=30');
                }

                var i = 0,
                    item;
                (function displayItems() {
                    item = t.blocksAvailable.eq(i++);

                    if (item.length) {

                        if (t.browser === 'ie8' || t.browser === 'ie9') {
                            item[t.transition]({
                                top: '+=30',
                                opacity: 1
                            });
                        } else {
                            item[t.transition]({
                                top: 0,
                                opacity: 1
                            });
                        }

                        setTimeout(displayItems, 130);
                    } else {
                        setTimeout(function() {
                            t._filterFinish();
                        }, 600);
                    }

                })();

            }, 600);

        },


        /**
         *  Fade Out Top custom init
         */
        _fadeOutTopInit: function() {

            this.transitionByFilter = 'css';

        },

        /**
         * Slide Up filter
         */
        _fadeOutTopFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this;

            t.blocksAvailable = t.blocks.filter(filter);

            if (t.browser === 'ie8' || t.browser === 'ie9') {
                t.$ul[t.transition]({
                    top: -30,
                    opacity: 0
                }, 350);
            } else {
                t.$ul[t.transition]({
                    top: -30,
                    opacity: 0
                });
            }

            t.$obj.addClass('cbp-no-transition');

            setTimeout(function() {

                if (filter !== '*') {

                    // get elements that are hidden and will be visible
                    off2onBlocks = off2onBlocks.filter(filter);

                    // get visible elements that will pe hidden
                    on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

                }

                // remove hidden class
                off2onBlocks.removeClass('cbp-item-hidden');

                if (on2offBlocks.length) {

                    on2offBlocks.css('opacity', 0);

                    //t.styleQueue.push({ $el: on2offBlocks, style: { opacity: 0 } });

                }

                if (off2onBlocks.length) {

                    off2onBlocks.css('opacity', 1);

                    //t.styleQueue.push({ $el: off2onBlocks, style: { opacity: 1 } });

                }

                // call layout
                t._layout();

                // trigger style queue and the animations
                t._processStyle(t.transitionByFilter);

                // resize main container height
                t._resizeMainContainer(t.transition);


                if (t.browser === 'ie8' || t.browser === 'ie9') {
                    t.$ul[t.transition]({
                        top: 0,
                        opacity: 1
                    }, 350);
                } else {
                    t.$ul[t.transition]({
                        top: 0,
                        opacity: 1
                    });
                }

                setTimeout(function() {
                    t._filterFinish();
                }, 400);

            }, 400);


        },

        /**
         *  Box Shadow custom init
         */
        _boxShadowInit: function() {

            var t = this;

            if (t.browser === 'ie8' || t.browser === 'ie9') {
                t.options.animationType = 'fadeOut';
            } else {
                t.blocksAvailable.append('<div class="cbp-animation-boxShadowMask"></div>');
            }

        },

        /**
         * boxShadow filter
         */
        _boxShadowFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this;

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            var boxShadowMask = t.blocks.find('.cbp-animation-boxShadowMask');

            boxShadowMask.addClass('cbp-animation-boxShadowShow');

            boxShadowMask.removeClass('cbp-animation-boxShadowActive cbp-animation-boxShadowInactive');

            t.blocksAvailable = t.blocks.filter(filter);

            var toAnimate = {};

            if (on2offBlocks.length) {

                on2offBlocks.find('.cbp-animation-boxShadowMask').addClass('cbp-animation-boxShadowActive');
                t.styleQueue.push({
                    $el: on2offBlocks,
                    style: {
                        opacity: 0
                    }
                });

                toAnimate = on2offBlocks.last();

            }

            if (off2onBlocks.length) {

                off2onBlocks.find('.cbp-animation-boxShadowMask').addClass('cbp-animation-boxShadowInactive');
                t.styleQueue.push({
                    $el: off2onBlocks,
                    style: {
                        opacity: 1
                    }
                });

                toAnimate = off2onBlocks.last();

            }

            // call layout
            t._layout();

            if (toAnimate.length) {
                toAnimate.one(t.transitionEnd, function() {
                    boxShadowMask.removeClass('cbp-animation-boxShadowShow');
                    t._filterFinish();
                });
            } else {
                boxShadowMask.removeClass('cbp-animation-boxShadowShow');
                t._filterFinish();
            }

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

        },


        /**
         *  Mover left custom init
         */
        _bounceLeftInit: function() {

            var t = this;

            t._duplicateContent({
                left: '-100%',
                opacity: 0
            });

            t.transitionByFilter = 'css';

            t.$ul.addClass('cbp-wrapper-front');

        },

        /**
         *  Mover left custom filter type
         */
        _bounceLeftFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this,
                ul, ulChildren, ulTohide;

            t.$obj.addClass('cbp-no-transition');

            if (t.ulHidden === 'clone') {

                t.ulHidden = 'first';

                ul = t.$ulClone;
                ulTohide = t.$ul;
                ulChildren = t.blocksClone;

            } else {

                t.ulHidden = 'clone';

                ul = t.$ul;
                ulTohide = t.$ulClone;

                ulChildren = t.blocks;

            }

            // get elements that are hidden and will be visible
            off2onBlocks = ulChildren.filter('.cbp-item-hidden');

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // set visible elements that will pe hidden
                ulChildren.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden cbp-item
            off2onBlocks.removeClass('cbp-item-hidden');

            t.blocksAvailable = ulChildren.filter(filter);

            // call layout
            t._layout();

            ulTohide[t.transition]({
                left: '-100%',
                opacity: 0
            }).removeClass('cbp-wrapper-front').addClass('cbp-wrapper-back');

            ul[t.transition]({
                left: 0,
                opacity: 1
            }).addClass('cbp-wrapper-front').removeClass('cbp-wrapper-back');

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            setTimeout(function() {
                t._filterFinish();
            }, 400);

        },

        /**
         *  Bounce Top init
         */
        _bounceTopInit: function() {

            var t = this;

            t._duplicateContent({
                top: '-100%',
                opacity: 0
            });

            t.transitionByFilter = 'css';

            t.$ul.addClass('cbp-wrapper-front');

        },

        /**
         *  Bounce Top filter type
         */
        _bounceTopFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this,
                ul, ulChildren, ulTohide;

            t.$obj.addClass('cbp-no-transition');

            if (t.ulHidden === 'clone') {

                t.ulHidden = 'first';

                ul = t.$ulClone;
                ulTohide = t.$ul;
                ulChildren = t.blocksClone;

            } else {

                t.ulHidden = 'clone';

                ul = t.$ul;
                ulTohide = t.$ulClone;

                ulChildren = t.blocks;

            }

            // get elements that are hidden and will be visible
            off2onBlocks = ulChildren.filter('.cbp-item-hidden');

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // set visible elements that will pe hidden
                ulChildren.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden cbp-item
            off2onBlocks.removeClass('cbp-item-hidden');

            t.blocksAvailable = ulChildren.filter(filter);

            // call layout
            t._layout();

            ulTohide[t.transition]({
                top: '-100%',
                opacity: 0
            }).removeClass('cbp-wrapper-front').addClass('cbp-wrapper-back');

            ul[t.transition]({
                top: 0,
                opacity: 1
            }).addClass('cbp-wrapper-front').removeClass('cbp-wrapper-back');

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            setTimeout(function() {
                t._filterFinish();
            }, 400);

        },


        /**
         *  Bounce Bottom init
         */
        _bounceBottomInit: function() {

            var t = this;

            t._duplicateContent({
                top: '100%',
                opacity: 0
            });

            t.transitionByFilter = 'css';

        },

        /**
         *  Bounce Bottom filter type
         */
        _bounceBottomFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this,
                ul, ulChildren, ulTohide;

            t.$obj.addClass('cbp-no-transition');

            if (t.ulHidden === 'clone') {

                t.ulHidden = 'first';

                ul = t.$ulClone;
                ulTohide = t.$ul;
                ulChildren = t.blocksClone;

            } else {

                t.ulHidden = 'clone';

                ul = t.$ul;
                ulTohide = t.$ulClone;

                ulChildren = t.blocks;

            }

            // get elements that are hidden and will be visible
            off2onBlocks = ulChildren.filter('.cbp-item-hidden');

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // set visible elements that will pe hidden
                ulChildren.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden cbp-item
            off2onBlocks.removeClass('cbp-item-hidden');

            t.blocksAvailable = ulChildren.filter(filter);

            // call layout
            t._layout();

            ulTohide[t.transition]({
                top: '100%',
                opacity: 0
            }).removeClass('cbp-wrapper-front').addClass('cbp-wrapper-back');

            ul[t.transition]({
                top: 0,
                opacity: 1
            }).addClass('cbp-wrapper-front').removeClass('cbp-wrapper-back');

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

            setTimeout(function() {
                t._filterFinish();
            }, 400);
        },


        /**
         *  Move Left init
         */
        _moveLeftInit: function() {

            var t = this;

            t._duplicateContent({
                left: '100%',
                opacity: 0
            });

            t.$ulClone.addClass('no-trans');

            t.transitionByFilter = 'css';

        },


        /**
         *  Move Left filter type
         */
        _moveLeftFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this,
                ul, ulChildren, ulTohide;

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            t.$obj.addClass('cbp-no-transition');

            if (t.ulHidden === 'clone') {

                t.ulHidden = 'first';

                ul = t.$ulClone;
                ulTohide = t.$ul;
                ulChildren = t.blocksClone;

            } else {

                t.ulHidden = 'clone';

                ul = t.$ul;
                ulTohide = t.$ulClone;

                ulChildren = t.blocks;

            }

            ulChildren.css('opacity', 0);

            ulChildren.addClass('cbp-item-hidden');

            t.blocksAvailable = ulChildren.filter(filter);

            t.blocksAvailable.css('opacity', 1);
            t.blocksAvailable.removeClass('cbp-item-hidden');

            // call layout
            t._layout();

            ulTohide[t.transition]({
                left: '-100%',
                opacity: 0
            });

            ul.removeClass('no-trans');

            if (t.transition === 'css') {

                ul[t.transition]({
                    left: 0,
                    opacity: 1
                });


                ulTohide.one(t.transitionEnd, function() {

                    ulTohide.addClass('no-trans').css({
                        left: '100%',
                        opacity: 0
                    });

                    t._filterFinish();

                });

            } else {

                ul[t.transition]({
                    left: 0,
                    opacity: 1
                }, function() {

                    ulTohide.addClass('no-trans').css({
                        left: '100%',
                        opacity: 0
                    });

                    t._filterFinish();

                });
            }



            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height
            t._resizeMainContainer(t.transition);

        },


        /**
         *  Slide Left init
         */
        _slideLeftInit: function() {

            var t = this;

            t._duplicateContent({});

            t.$ul.addClass('cbp-wrapper-front');

            t.$ulClone.css('opacity', 0);

            t.transitionByFilter = 'css';

        },


        /**
         *  Slide Left filter type
         */
        _slideLeftFilter: function(on2offBlocks, off2onBlocks, filter) {

            var t = this,
                ul, ulChildren, ulTohide, slideOut, slideIn, toAnimate;

            // reset from appendItems
            t.blocks.show();
            t.blocksClone.show();

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            t.$obj.addClass('cbp-no-transition');

            t.blocks.find('.cbp-item-wrapper').removeClass('cbp-animation-slideLeft-out cbp-animation-slideLeft-in');
            t.blocksClone.find('.cbp-item-wrapper').removeClass('cbp-animation-slideLeft-out cbp-animation-slideLeft-in');

            t.$ul.css({
                'opacity': 1
            });
            t.$ulClone.css({
                'opacity': 1
            });

            if (t.ulHidden === 'clone') {

                t.ulHidden = 'first';

                slideOut = t.blocks;
                slideIn = t.blocksClone;

                ulChildren = t.blocksClone;

                t.$ul.removeClass('cbp-wrapper-front');
                t.$ulClone.addClass('cbp-wrapper-front');

            } else {

                t.ulHidden = 'clone';

                slideOut = t.blocksClone;
                slideIn = t.blocks;

                ulChildren = t.blocks;

                t.$ul.addClass('cbp-wrapper-front');
                t.$ulClone.removeClass('cbp-wrapper-front');

            }

            ulChildren.css('opacity', 0);

            ulChildren.addClass('cbp-item-hidden');

            t.blocksAvailable = ulChildren.filter(filter);

            t.blocksAvailable.css({
                'opacity': 1
            });
            t.blocksAvailable.removeClass('cbp-item-hidden');

            // call layout
            t._layout();

            if (t.transition === 'css') {

                slideOut.find('.cbp-item-wrapper').addClass('cbp-animation-slideLeft-out');

                slideIn.find('.cbp-item-wrapper').addClass('cbp-animation-slideLeft-in');

                toAnimate = slideOut.find('.cbp-item-wrapper').last();

                if (toAnimate.length) {
                    toAnimate.one(t.animationEnd, function() {
                        t._filterFinish();
                    });
                } else {
                    t._filterFinish();
                }

            } else {

                slideOut.find('.cbp-item-wrapper').animate({
                        left: '-100%'
                    },
                    400, function() {
                        t._filterFinish();
                    });

                slideIn.find('.cbp-item-wrapper').css('left', '100%');

                slideIn.find('.cbp-item-wrapper').animate({
                        left: 0
                    },
                    400
                );

            }

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height (firefox <=25 bug)
            t._resizeMainContainer('animate');

        },


        /**
         *  Slide Delay init
         */
        _slideDelayInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  Slide Delay filter type
         */
        _slideDelayFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, 'slideDelay', true);
        },


        /**
         *  3d Flip init
         */
        _3dflipInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  3d Flip filter type
         */
        _3dflipFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, '3dflip', true);
        },


        /**
         *  Rotate Sides init
         */
        _rotateSidesInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  Rotate Sides filter type
         */
        _rotateSidesFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, 'rotateSides', true);
        },


        /**
         *  Flip Out Delay init
         */
        _flipOutDelayInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  Flip Out Delay filter type
         */
        _flipOutDelayFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, 'flipOutDelay', false);
        },


        /**
         *  Fold Left init
         */
        _foldLeftInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  Fold Left filter type
         */
        _foldLeftFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, 'foldLeft', true);
        },


        /**
         *  Unfold init
         */
        _unfoldInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  Unfold filter type
         */
        _unfoldFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, 'unfold', true);
        },


        /**
         *  Scale Down init
         */
        _scaleDownInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  Scale Down filter type
         */
        _scaleDownFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, 'scaleDown', true);
        },


        /**
         *  Front Row init
         */
        _frontRowInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  Front Row filter type
         */
        _frontRowFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, 'frontRow', true);
        },


        /**
         *  Rotate Room init
         */
        _rotateRoomInit: function() {
            this._wrapperFilterInit();
        },


        /**
         *  Rotate Room filter type
         */
        _rotateRoomFilter: function(on2offBlocks, off2onBlocks, filter) {
            this._wrapperFilter(on2offBlocks, off2onBlocks, filter, 'rotateRoom', true);
        },


        /**
         *  Wrapper Filter Init
         */
        _wrapperFilterInit: function() {

            var t = this;

            t._duplicateContent({});

            t.$ul.addClass('cbp-wrapper-front');

            t.$ulClone.css('opacity', 0);

            t.transitionByFilter = 'css';

        },


        /**
         *  Wrapper Filter
         */
        _wrapperFilter: function(on2offBlocks, off2onBlocks, filter, name, fadeOut) {

            var t = this,
                ul, ulChildren, ulTohide, slideOut, slideIn, toAnimate;

            // reset from appendItems
            t.blocks.show();
            t.blocksClone.show();

            if (filter !== '*') {

                // get elements that are hidden and will be visible
                off2onBlocks = off2onBlocks.filter(filter);

                // get visible elements that will pe hidden
                on2offBlocks = t.blocks.not('.cbp-item-hidden').not(filter).addClass('cbp-item-hidden');

            }

            // remove hidden class
            off2onBlocks.removeClass('cbp-item-hidden');

            t.$obj.addClass('cbp-no-transition');

            t.blocks.find('.cbp-item-wrapper').removeClass('cbp-animation-' + name + '-out cbp-animation-' + name + '-in cbp-animation-' + name + '-fadeOut').css('style', '');
            t.blocksClone.find('.cbp-item-wrapper').removeClass('cbp-animation-' + name + '-out cbp-animation-' + name + '-in cbp-animation-' + name + '-fadeOut').css('style', '');

            t.$ul.css({
                'opacity': 1
            });
            t.$ulClone.css({
                'opacity': 1
            });

            if (t.ulHidden === 'clone') {

                t.ulHidden = 'first';

                slideOut = t.blocks;
                slideIn = t.blocksClone;

                ulChildren = t.blocksClone;

                t.$ul.removeClass('cbp-wrapper-front');
                t.$ulClone.addClass('cbp-wrapper-front');

            } else {

                t.ulHidden = 'clone';

                slideOut = t.blocksClone;
                slideIn = t.blocks;

                ulChildren = t.blocks;

                t.$ul.addClass('cbp-wrapper-front');
                t.$ulClone.removeClass('cbp-wrapper-front');

            }


            slideOut = t.blocksAvailable;

            ulChildren.css('opacity', 0);

            ulChildren.addClass('cbp-item-hidden');

            t.blocksAvailable = ulChildren.filter(filter);

            t.blocksAvailable.css({
                'opacity': 1
            });
            t.blocksAvailable.removeClass('cbp-item-hidden');

            slideIn = t.blocksAvailable;

            // call layout
            t._layout();

            if (t.transition === 'css') {
                var iii = 0,
                    kkk = 0;

                slideIn.each(function(index, el) {
                    $(el).find('.cbp-item-wrapper').addClass('cbp-animation-' + name + '-in').css('animation-delay', (kkk / 20) + 's');
                    kkk++;

                });


                slideOut.each(function(index, el) {

                    if (kkk <= iii && fadeOut) {
                        $(el).find('.cbp-item-wrapper').addClass('cbp-animation-' + name + '-fadeOut');
                    } else {
                        $(el).find('.cbp-item-wrapper').addClass('cbp-animation-' + name + '-out').css('animation-delay', (iii / 20) + 's');
                    }

                    iii++;

                });

                toAnimate = slideOut.find('.cbp-item-wrapper').first();

                if (toAnimate.length) {
                    toAnimate.one(t.animationEnd, function() {
                        t._filterFinish();

                        // ie10, ie11 bug
                        if (t.browser === 'ie10' || t.browser === 'ie11') {
                            setTimeout(function () {
                                $('.cbp-item-wrapper').removeClass('cbp-animation-' + name + '-in');
                            }, 300);
                        }
                    });
                } else {
                    t._filterFinish();

                    // ie10, ie11 bug
                    if (t.browser === 'ie10' || t.browser === 'ie11') {
                        setTimeout(function () {
                            $('.cbp-item-wrapper').removeClass('cbp-animation-' + name + '-in');
                        }, 300);
                    }
                }

            } else {

                slideOut.find('.cbp-item-wrapper').animate({
                        left: '-100%'
                    },
                    400, function() {
                        t._filterFinish();
                    });

                slideIn.find('.cbp-item-wrapper').css('left', '100%');

                slideIn.find('.cbp-item-wrapper').animate({
                        left: 0
                    },
                    400
                );

            }

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height (firefox <=25 bug)
            t._resizeMainContainer('animate');

        },

        _filterFinish: function() {

            var t = this;

            t.isAnimating = false;

            t._triggerEvent('filterFinish');

            t.$obj.trigger('filterComplete');
        },


        /**
         *  Register event
         */
        _registerEvent: function(name, callbackFunction, oneTime) {

            var t = this;

            if (!t.registeredEvents[name]) {

                t.registeredEvents[name] = [];

                t.registeredEvents.push(name);
            }

            t.registeredEvents[name].push({
                func: callbackFunction,
                oneTime: oneTime || false
            });



        },

        /**
         *  Trigger event
         */
        _triggerEvent: function(name) {

            var t = this;

            if (t.registeredEvents[name]) {
                for (var i = t.registeredEvents[name].length - 1; i >= 0; i--) {

                    t.registeredEvents[name][i].func.call(t);

                    if (t.registeredEvents[name][i].oneTime) {

                        t.registeredEvents[name].splice(i, 1);
                    }

                }
            }

        },



        /*  -----------------------------------------------------
                                PUBLIC METHODS
            ----------------------------------------------------- */

        /*
         * Initializate the plugin
         */
        init: function(options, callbackFunction) {

            var t = $.data(this, 'cubeportfolio');

            if (t) {
                throw new Error('cubeportfolio is already initialized. Please destroy it before initialize again!');
            }

            // create new object attached to this element
            t = $.data(this, 'cubeportfolio', Object.create(pluginObject));

            // call private _main method
            t._main(this, options, callbackFunction);

        },


        /*
         * Destroy the plugin
         */
        destroy: function(callbackFunction) {

            var t = $.data(this, 'cubeportfolio');
            if (!t) {
                throw new Error('cubeportfolio is not initialized. Please initialize before calling destroy method!');
            }

            // register callback function
            if ($.isFunction(callbackFunction)) {
                t._registerEvent('destroyFinish', callbackFunction, true);
            }

            // remove data
            $.removeData(this, 'cubeportfolio');

            // remove data from blocks
            $.each(t.blocks, function(index, value) {

                $.removeData(this, 'transformFn');

                $.removeData(this, 'cbp-wxh');

            });

            // remove loading class and .cbp on container
            t.$obj.removeClass('cbp cbp-loading cbp-ready cbp-no-transition');

            // remove class from ul
            t.$ul.removeClass('cbp-wrapper-front cbp-wrapper-back cbp-wrapper no-trans').removeAttr('style');

            // remove attr style
            t.$obj.removeAttr('style');
            if (t.$ulClone) {
                t.$ulClone.remove();
            }

            // remove class from plugin for additional support
            if (t.browser) {
                t.$obj.removeClass('cbp-' + t.browser);
            }

            // remove off resize event
            $(window).off('resize' + eventNamespace);

            // destroy lightbox if enabled
            if (t.lightbox) {
                t.lightbox.destroy();
            }

            // destroy singlePage if enabled
            if (t.singlePage) {
                t.singlePage.destroy();
            }

            // destroy singlePage inline if enabled
            if (t.singlePageInline) {
                t.singlePageInline.destroy();
            }

            // reset blocks
            t.blocks.removeClass('cbp-item-hidden').removeAttr('style');

            t.blocks.find('.cbp-item-wrapper').children().unwrap();

            if (t.options.caption) {
                t._captionDestroy();
            }

            if (t.options.animationType) {
                if (t.options.animationType === 'boxShadow') {
                    $('.cbp-animation-boxShadowMask').remove();
                }

                // remove filter class from plugin
                t.$obj.removeClass('cbp-animation-' + t.options.animationType);

            }

            t._triggerEvent('destroyFinish');

        },

        /*
         * Filter the plugin by filterName
         */
        filter: function(filterName, callbackFunction, context) {

            var t = context || $.data(this, 'cubeportfolio'),
                off2onBlocks, on2offBlocks;

            if (!t) {
                throw new Error('cubeportfolio is not initialized. Please initialize before calling filter method!');
            }

            filterName = (filterName === '*' || filterName === '') ? '*' : filterName;

            if (t.isAnimating || t.defaultFilter == filterName) {
                return;
            }

            if (t.browser === 'ie8' || t.browser === 'ie9') {
                t.$obj.removeClass('cbp-no-transition cbp-appendItems-loading');
            } else {
                t.obj.classList.remove('cbp-no-transition');
                t.obj.classList.remove('cbp-appendItems-loading');
            }

            t.defaultFilter = filterName;

            t.isAnimating = true;

            // register callback function
            if ($.isFunction(callbackFunction)) {
                t._registerEvent('filterFinish', callbackFunction, true);
            }

            // get elements that are hidden and will be visible
            off2onBlocks = t.blocks.filter('.cbp-item-hidden');

            // visible elements that will pe hidden
            on2offBlocks = [];

            if (t.singlePageInline && t.singlePageInline.isOpen) {
                t.singlePageInline.close('promise', {
                    callback: function() {
                        t['_' + t.options.animationType + 'Filter'](on2offBlocks, off2onBlocks, filterName);
                    }
                });
            } else {
                t['_' + t.options.animationType + 'Filter'](on2offBlocks, off2onBlocks, filterName);
            }

        },

        /*
         * Show counter for filters
         */
        showCounter: function(elems) {

            var t = $.data(this, 'cubeportfolio');

            if (!t) {
                throw new Error('cubeportfolio is not initialized. Please initialize before calling showCounter method!');
            }

            t.elems = elems;

            $.each(elems, function(index, val) {

                var me = $(this),
                    filterName = me.data('filter'),
                    count = 0;

                filterName = (filterName === '*' || filterName === '') ? '*' : filterName;

                count = t.blocks.filter(filterName).length;

                me.find('.cbp-filter-counter').text(count);

            });

        },


        /*
         * ApendItems elements
         */
        appendItems: function(items, callbackFunction) {

            var me = this,
                t = $.data(me, 'cubeportfolio'),
                defaultFilter, children, cloneItems, fewItems;

            if (!t) {
                throw new Error('cubeportfolio is not initialized. Please initialize before calling appendItems method!');
            }

            if (t.singlePageInline && t.singlePageInline.isOpen) {
                t.singlePageInline.close('promise', {
                    callback: function() {
                        pluginObject._addItems.call(me, items, callbackFunction);
                    }
                });
            } else {
                pluginObject._addItems.call(me, items, callbackFunction);
            }



        },

        _addItems: function(items, callbackFunction) {

            var t = $.data(this, 'cubeportfolio'),
                defaultFilter, children, cloneItems, fewItems;

            // register callback function
            if ($.isFunction(callbackFunction)) {
                t._registerEvent('appendItemsFinish', callbackFunction, true);
            }

            t.$obj.addClass('cbp-no-transition cbp-appendItems-loading');

            items = $(items).css('opacity', 0);

            items.filter('.cbp-item').wrapInner('<div class="cbp-item-wrapper"></div>');

            fewItems = items.filter(t.defaultFilter);

            if (t.ulHidden) {

                if (t.ulHidden === 'first') { // the second

                    items.appendTo(t.$ulClone);
                    t.blocksClone = t.$ulClone.children();
                    children = t.blocksClone;


                    // modify the ul
                    cloneItems = items.clone();
                    cloneItems.appendTo(t.$ul);
                    t.blocks = t.$ul.children();

                } else { // the first

                    items.appendTo(t.$ul);
                    t.blocks = t.$ul.children();
                    children = t.blocks;

                    // modify the ulClone
                    cloneItems = items.clone();
                    cloneItems.appendTo(t.$ulClone);
                    t.blocksClone = t.$ulClone.children();

                }

            } else {

                items.appendTo(t.$ul);

                // cache the blocks
                t.blocks = t.$ul.children();
                children = t.blocks;

            }

            // if custom hover effect is active
            if (t.options.caption) {
                // destroy hover effects
                t._captionDestroy();

                // init hover effects
                t._captionInit();
            }

            defaultFilter = t.defaultFilter;

            t.blocksAvailable = children.filter(defaultFilter);

            children.not('.cbp-item-hidden').not(defaultFilter).addClass('cbp-item-hidden');

            //make responsive
            if (t.options.gridAdjustment === 'responsive') {
                t._responsiveLayout();
            }

            // call layout
            t._layout();

            // trigger style queue and the animations
            t._processStyle(t.transitionByFilter);

            // resize main container height (firefox <=25 bug)
            t._resizeMainContainer('animate');

            var hiddenItem = items.filter('.cbp-item-hidden');
            switch (t.options.animationType) {
                case 'flipOut':
                    hiddenItem.find('.cbp-item-wrapper')
                        .addClass('cbp-animation-flipOut-out');
                    break;

                case 'scaleSides':
                    hiddenItem.find('.cbp-item-wrapper')
                        .addClass('cbp-animation-scaleSides-out');
                    break;

                case 'flipBottom':
                    hiddenItem.find('.cbp-item-wrapper')
                        .addClass('cbp-animation-flipBottom-out');
                    break;
            }

            fewItems.animate({
                opacity: 1
            }, 800, function() {

                switch (t.options.animationType) {

                    case 'bounceLeft':
                    case 'bounceTop':
                    case 'bounceBottom':
                        t.blocks.css('opacity', 1);
                        t.blocksClone.css('opacity', 1);
                        break;

                    case 'flipOut':
                    case 'scaleSides':
                    case 'flipBottom':
                        hiddenItem.css('opacity', 1);
                        break;
                }
            });

            // if show count whas actived, call show count function again
            if (t.elems) {
                pluginObject.showCounter.call(this, t.elems);
            }

            setTimeout(function() {
                t._triggerEvent('appendItemsFinish');
            }, 900);

        }
    };

    /**
     * jQuery plugin initializer
     */
    $.fn.cubeportfolio = function(method) {

        var args = arguments;

        return this.each(function() {

            // public method calling
            if (pluginObject[method]) {

                return pluginObject[method].apply(this, Array.prototype.slice.call(args, 1));

            } else if (typeof method === 'object' || !method) {

                return pluginObject.init.apply(this, args);

            } else {

                throw new Error('Method ' + method + ' does not exist on jQuery.cubeportfolio.js');
            }


        });

    };


    // Plugin default options
    $.fn.cubeportfolio.options = {

        /**
         *  Default filter for plugin
         *  Values: strings that represent the filter name(ex: *, .logo, .web-design, .design)
         */
        defaultFilter: '*',

        /**
         *  Defines which animation to use for items that will be shown or hidden after a filter has been activated.
         *  The plugin use the best browser features when available (css3 transition and transform, GPU acceleration) and fallback to simple animations (javascript animations) for legacy browsers.
         *  Values: - fadeOut
         *          - quicksand
         *          - boxShadow
         *          - bounceLeft
         *          - bounceTop
         *          - bounceBottom
         *          - moveLeft
         *          - slideLeft
         *          - fadeOutTop
         *          - sequentially
         *          - skew
         *          - slideDelay
         *          - rotateSides
         *          - flipOutDelay
         *          - flipOut
         *          - unfold
         *          - foldLeft
         *          - scaleDown
         *          - scaleSides
         *          - frontRow
         *          - flipBottom
         *          - rotateRoom
         */
        animationType: 'fadeOut',

        /**
         *  Adjust the layout grid
         *  Values: - default (no adjustment applied)
         *          - alignCenter (align the grid on center of the page)
         *          - responsive (use a fluid grid to resize the grid)
         */
        gridAdjustment: 'default',

        /**
         *  Horizontal gap between items
         *  Values: only integers (ex: 1, 5, 10)
         */
        gapHorizontal: 10,

        /**
         *  Vertical gap between items
         *  Values: only integers (ex: 1, 5, 10)
         */
        gapVertical: 10,

        /**
         *  Caption - the overlay that is shown when you put the mouse over an item
         *  Values: - pushTop
         *          - pushDown
         *          - revealBottom
         *          - revealTop
         *          - moveRight
         *          - moveLeft
         *          - overlayBottomPush
         *          - overlayBottom
         *          - overlayBottomReveal
         *          - overlayBottomAlong
         *          - overlayRightAlong
         *          - minimal
         *          - fadeIn
         *          - zoom
         */
        caption: 'pushTop',

        /**
         *  The plugin will display his content based on the following values.
         *  Values: - default (the content will be displayed as soon as possible)
         *          - fadeIn (the content will be displayed with a fadeIn effect)
         *          - lazyLoading (the plugin will fully preload the images before displaying the items with a fadeIn effect)
         *          - fadeInToTop (the plugin will fully preload the images before displaying the items with a fadeIn effect from bottom to top)
         *          - sequentially (the plugin will fully preload the images before displaying the items with a sequentially effect)
         *          - bottomToTop (the plugin will fully preload the images before displaying the items with an animation from bottom to top)
         */
        displayType: 'default',

        /**
         *  Defines the speed of displaying the items (when `displayType == default` this option will have no effect)
         *  Values: only integers, values in ms (ex: 200, 300, 500)
         */
        displayTypeSpeed: 400,

        /**
         *  This is used to define any clickable elements you wish to use to trigger lightbox popup on click.
         *  Values: strings that represent the elements in the document (DOM selector)
         */
        lightboxDelegate: '.cbp-lightbox',

        /**
         *  Enable / disable gallery mode
         *  Values: true or false
         */
        lightboxGallery: true,

        /**
         *  Attribute of the delegate item that contains caption for lightbox
         *  Values: html atributte
         */
        lightboxTitleSrc: 'data-title',

        /**
         *  Enable / disable the counter (ex: '3 of 5') for lightbox popup
         *  Values: true or false
         */
        lightboxShowCounter: true,

        /**
         *  This is used to define any clickable elements you wish to use to trigger singlePage popup on click.
         *  Values: strings that represent the elements in the document (DOM selector)
         */
        singlePageDelegate: '.cbp-singlePage',

        /**
         *  Enable / disable the deeplinking feature for singlePage popup
         *  Values: true or false
         */
        singlePageDeeplinking: true,

        /**
         *  Enable / disable the sticky navigation for singlePage popup
         *  Values: true or false
         */
        singlePageStickyNavigation: true,

        /**
         *  Enable / disable the counter (ex: '3 of 5') for singlePage popup
         *  Values: true or false
         */
        singlePageShowCounter: true,

        /**
         *  Use this callback to update singlePage content.
         *  The callback will trigger after the singlePage popup will open.
         *  @param url = the href attribute of the item clicked
         *  @param element = the item clicked
         *  Values: function
         */
        singlePageCallback: function(url, element) {

            // to update singlePage content use the following method: this.updateSinglePage(yourContent)

        },

        /**
         *  This is used to define any clickable elements you wish to use to trigger singlePage Inline on click.
         *  Values: strings that represent the elements in the document (DOM selector)
         */
        singlePageInlineDelegate: '.cbp-singlePageInline',

        /**
         *  This is used to define the position of singlePage Inline block
         *  Values: - above ( above current element )
         *          - below ( below current elemnet)
         *          - top ( positon top )
         */
        singlePageInlinePosition: 'top',

        /**
         *  Push the open panel in focus and at close go back to the former stage
         *  Values: true or false
         */
        singlePageInlineInFocus: true,

        /**
         *  Use this callback to update singlePage Inline content.
         *  The callback will trigger after the singlePage Inline will open.
         *  @param url = the href attribute of the item clicked
         *  @param element = the item clicked
         *  Values: function
         */
        singlePageInlineCallback: function(url, element) {

            // to update singlePage Inline content use the following method: this.updateSinglePageInline(yourContent)

        }

    };

})(jQuery, window, document);
