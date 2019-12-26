(function(_, $) {
	$(document).ready(function () {
		function AppendProductStickerImage() {
			$('.sticker-wrapper.hidden').each(function() {
				sticker = $(this);
				sticker_container = sticker.parent();
				product_image = sticker_container.find( "a:not(.ty-product-thumbnails__item) img" );
					
				if (product_image.length == 0) {
					product_container = sticker.parent().parent().parent().parent();
					product_image = product_container.find( "a:not(.ty-product-thumbnails__item) img" );
				}

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
		}
		$.ceEvent('on', 'ce.commoninit', AppendProductStickerImage);
		$.ceEvent('on', 'ce.ajaxdone', AppendProductStickerImage);
	});
}(Tygh, Tygh.$));