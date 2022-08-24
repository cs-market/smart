{script src="js/addons/rumba/bootstrap-toggle.js"}
<script type="text/javascript">
    (function(_, $) {
        $.ceEvent('on', 'ce.commoninit', function() {
            $('input[type=checkbox][data-toggle^=toggle]').bootstrapToggle();
        });
    }(Tygh, Tygh.$));
</script>
