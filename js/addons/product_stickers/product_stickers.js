(function(_, $) {
	var methods = {
		appendStickerImage: function (location, $container) {
			$('.sticker-wrapper.hidden').each(function() {
				sticker = $(this);
				sticker_container = sticker.parent();

				product_image = methods.findProductImage(sticker);

				if (product_image.length > 0) {
					sticker.removeClass('hidden');
					image_link = product_image.closest('a');

					if (image_link.hasClass('cm-previewer')) {
						sticker.find('img').click(function(event) {
							event.preventDefault();
						});
					}
					image_link.append(sticker);
					image_link.closest('div').css('position','relative');
				}
			});
		},
		findProductImage: function (elm, depth = 4) {
			sticker_container = elm.parent();
			product_image = sticker_container.find( "a:not(.ty-product-thumbnails__item) img" );
			if (product_image.length == 0 && depth) {
				product_image = methods.findProductImage(sticker_container, depth-1);
			}
			return product_image;
		}
	}

	$.extend({
		ceProductStickers: function (method) {
			if (methods[method]) {
				return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
			} else {
				$.error('ceProductStickers: method ' + method + ' does not exist');
			}
		}
	});

	$.ceEvent('on', 'ce.commoninit', function() {
		$.ceProductStickers('appendStickerImage');
	});
	$.ceEvent('on', 'ce.ajaxdone', function() {
		$.ceProductStickers('appendStickerImage');
	});
}(Tygh, Tygh.$));
