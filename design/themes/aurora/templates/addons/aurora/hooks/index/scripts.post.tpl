{script src="js/addons/aurora/bootstrap-toggle.js"}
<script type="text/javascript">
    (function(_, $) {
        $.ceEvent('on', 'ce.commoninit', function() {
            $('.ty-btn__add-to-cart').click(function() {
                $(this).closest('.cm-product-controls').addClass('in-cart').find('.ty-grid-list__qty').addClass('ty-cart-content__qty');
            });
        });
    }(Tygh, Tygh.$));
</script>
