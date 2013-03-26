/* jQuery Captify Plugin
 *
 * Copyright (C) 2008 Brian Reavis
 * Licenced under the MIT license
 *
 * jQueryDate: 2008-2-27 [Fri, 27 Feb 2009] jQuery
 */

jQuery.fn.extend({
	captify: function(o) {
		var o = jQuery.extend({

			speedOver: 'fast',
			// speed of the mouseover effect
			speedOut: 'normal',
			// speed of the mouseout effect
			hideDelay: 500,
			// how long to delay the hiding of the caption after mouseout (ms)
			animation: 'slide',
			// 'fade', 'slide', 'always-on'
			prefix: '',
			// text/html to be placed at the beginning of every caption
			opacity: '0.35',
			// opacity of the caption on mouse over
			className: 'caption-bottom',
			// the name of the CSS class to apply to the caption box
			position: 'bottom',
			// position of the caption (top or bottom)
			spanWidth: '100%' // caption span % of the image
		}, o);

		jQuery(this).each(function() {

			var img = this;

			//jQuery(this).load(function() {
			jQuerythis = img;

			if(this.hasInit) {

				return false;

			}

			this.hasInit = true;

			var over_caption = false;

			var over_img = false;



			//pull the label from another element if there is a
			//valid element id inside the rel="..." attribute, otherwise,
			//just use the text in the alt="..." attribute.
			var captionLabelSrc = jQuery('#' + jQuery(this).attr('rel'));

			var captionLabelHTML = !captionLabelSrc.length ? jQuery(this).attr('alt') : captionLabelSrc.html();

			captionLabelSrc.remove();

			var toWrap = this.parent && this.parent.tagName == 'a' ? this.parent : jQuery(this);

			var wrapper = toWrap.wrap('<div></div>').parent();

			wrapper.css({

				overflow: 'hidden',

				padding: 0,

				fontSize: 0.1

			})

			wrapper.addClass('caption-wrapper');

			wrapper.width(jQuery(this).width());

			wrapper.height(jQuery(this).height());

			wrapper.click(function() {

				window.location = jQuery(this).parent().attr('href');

			});



			//transfer the margin and border properties from the image to the wrapper
			jQuery.map(['top', 'right', 'bottom', 'left'], function(i) {

				wrapper.css('margin-' + i, jQuery(img).css('margin-' + i));

				jQuery.map(['style', 'width', 'color'], function(j) {

					var key = 'border-' + i + '-' + j;

					wrapper.css(key, jQuery(img).css(key));

				});

			});

			jQuery(img).css({
				border: '0 none'
			});



			//create two consecutive divs, one for the semi-transparent background,
			//and other other for the fully-opaque label
			var caption = jQuery('div:last', wrapper.append('<div></div>')).addClass(o.className);

			var captionContent = jQuery('div:last', wrapper.append('<div></div>')).addClass(o.className).append(o.prefix).append(captionLabelHTML);



			//override hiding from CSS, and reset all margins (which could have been inherited)
			jQuery('*', wrapper).css({
				margin: 0
			}).show();



			//ensure the background is on bottom
			var captionPositioning = jQuery.browser.msie ? 'static' : 'relative';
			if(jQuery.browser.msie && jQuery.browser.version.substr(0, 1) > 8) {
				captionPositioning = 'relative';
			}
			caption.css({

				zIndex: 1,

				position: captionPositioning,

				opacity: o.animation == 'fade' ? 0 : o.opacity,

				width: o.spanWidth

			});



			if(o.position == 'bottom') {

				var vLabelOffset = parseInt(caption.css('border-top-width').replace('px', '')) + parseInt(captionContent.css('padding-top').replace('px', '')) - 1;

				captionContent.css('paddingTop', vLabelOffset);

			}

			//clear the backgrounds/borders from the label, and make it fully-opaque
			captionContent.css({
				position: captionPositioning,
				zIndex: 2,
				background: 'none',
				border: '0 none',
				opacity: o.animation == 'fade' ? 0 : 1,
				width: o.spanWidth
			});
			caption.width(captionContent.outerWidth());
			caption.height(captionContent.height());

			// represents caption margin positioning for hide and show states
			var topBorderAdj = (o.position == 'bottom' && jQuery.browser.msie) ? -4 : 0;
			var captionPosition = (o.position == 'top') ? {
				hide: -jQuery(img).height() - caption.outerHeight() - 1,
				show: -jQuery(img).height()
			} : {
				hide: 0,
				show: -caption.outerHeight() + topBorderAdj
			};

			//pull the label up on top of the background
			captionContent.css('marginTop', -caption.outerHeight());
			caption.css('marginTop', captionPosition[o.animation == 'fade' || o.animation == 'always-on' ? 'show' : 'hide']);

			//function to push the caption out of view
			var cHide = function() {
				if (! captionContent.data('animating_out')) {
					captionContent.data('animating_out', true);

					if(!over_caption && !over_img) {
						var props = o.animation == 'fade' ? {
							opacity: 0
						} : {
							marginTop: captionPosition.hide
						};

						caption.animate(props, o.speedOut, function() {
							captionContent.data('animating_out', false);
						});

						if(o.animation == 'fade') {
							captionContent.animate({opacity: 0}, o.speedOver, function() {
								captionContent.data('animating_out', false);
							});
						}
					}
					else
					{
						captionContent.data('animating_out', false);
					}
				}
			};

			if(o.animation != 'always-on') {
				//when the mouse is over the image
				jQuery(this).hover(
					function() {
						if (! captionContent.data('animating_in')) {
							captionContent.data('animating_in', true);

							over_img = true;
							if(!over_caption) {
								var props = o.animation == 'fade' ? {
									opacity: o.opacity
								} : {
									marginTop: captionPosition.show
								};
								caption.animate(props, o.speedOver, function() {
									captionContent.data('animating_in', false);
								});
								if(o.animation == 'fade') {
									captionContent.animate({opacity: 1}, o.speedOver / 2, function() {
										captionContent.data('animating_in', false);
									});
								}
							}
							else
							{
								captionContent.data('animating_in', false);
							}
						}
					},
					function() {
						over_img = false;
						window.setTimeout(cHide, o.hideDelay);
					}
				);
				//when the mouse is over the caption on top of the image (the caption is a sibling of the image)
				jQuery('div', wrapper).hover(
					function() {
						over_caption = true;
					},
					function() {
						over_caption = false;
						window.setTimeout(cHide, o.hideDelay);
					}
				);
			}
			//});
			//if the image has already loaded (due to being cached), force the load function to be called
			if(this.complete || this.naturalWidth > 0) {
				//jQuery(img).trigger('load');
			}
		});
	}
});
