{script src="js/addons/aurora/bootstrap-toggle.js"}
{script src="js/addons/aurora/malma.js"}
<script type="text/javascript">
    (function(_, $) {
        $.ceEvent('on', 'ce.commoninit', function(context) {
            {if $addons.aurora.dynamic_quantity == "YesNo::YES"|enum}
            $('.ty-btn__add-to-cart').click(function() {
                dynamic_product = $(this).closest('.ty-dynamic-quantity');
                if (dynamic_product.length) {
                    dynamic_product.addClass('ty-product-in-cart')
                    qty_control = $('.ty-grid-list__qty', dynamic_product);
                    if (!qty_control.length) {
                        qty_control = $('.ty-product-block__qty', dynamic_product);
                    }
                    if (qty_control.length) {
                        qty_control.addClass('ty-cart-content__qty');
                    }
                }
            });
            {/if}
            $('.ty-btn__add-to-wish', context).click(function() {
                $('.ty-icon', $(this)).toggleClass('ty-icon-aurora-star-full').toggleClass('ty-icon-aurora-star-empty');
            });
            $('.cm-autoclick').click();
        });

        $.ceEvent('on', 'dispatch_event_pre', function (e, jelm, processed) {
            if (e.type !== 'click') {
                return;
            }

            if (jelm.hasClass('cm-save-value')) {
                id = jelm.prop('id');
                if (jelm.is('[type=checkbox]')) {
                    val = jelm.prop("checked");
                } else {
                    val = jelm.val();
                }
                $.cookie.set(id, val);
                return false;
            }
        });
    }(Tygh, Tygh.$));
</script>

