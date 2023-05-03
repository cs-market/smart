{script src="js/addons/aurora/bootstrap-toggle.js"}
<script type="text/javascript">
    (function(_, $) {
        $.ceEvent('on', 'ce.commoninit', function() {
            $('.ty-btn__add-to-cart').click(function() { $(this).addClass('ty-btn__active'); });
            $('input[type=checkbox][data-toggle^=toggle]').bootstrapToggle();
        });
    }(Tygh, Tygh.$));
</script>
