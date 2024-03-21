{script src="js/addons/aurora/bootstrap-toggle.js"}
{script src="js/addons/aurora/malma.js"}
{if $addons.aurora.dynamic_quantity == "YesNo::YES"|enum}
<script type="text/javascript">
    (function(_, $) {
        $.ceEvent('on', 'ce.commoninit', function() {
            $('.ty-btn__add-to-cart').click(function() {
                $(this).closest('.cm-product-controls').addClass('in-cart').find('.ty-grid-list__qty').addClass('ty-cart-content__qty');
            });
            $('.ty-btn__add-to-wish').click(function() {
                $(this).find('.ty-icon-aurora-star-empty').addClass('ty-icon-aurora-star-full');
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
{/if}
