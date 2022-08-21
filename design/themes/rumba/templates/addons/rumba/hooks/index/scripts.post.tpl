{script src="js/addons/rumba/bootstrap-toggle.js"}
<script type="text/javascript">
    (function(_, $) {
        function fn_change_amount_value(inp, new_val) {
            val = inp.val();
            elm = $('#for_'+inp.attr('id'));
            if (elm.length != 0) {
                box_val = +(val/elm.data('caBoxContains')).toFixed(2);
                elm.text(box_val);
            }
        }

        $.ceEvent('on', 'ce.valuechangerincrease', function(inp, step, min_qty, new_val) {
            fn_change_amount_value(inp, new_val)
        });
        $.ceEvent('on', 'ce.valuechangerdecrease', function(inp, step, min_qty, new_val) {
            fn_change_amount_value(inp, new_val)
        });

        $.ceEvent('on', 'ce.commoninit', function() {
            $('input[type=checkbox][data-toggle^=toggle]').bootstrapToggle();
        });
    }(Tygh, Tygh.$));
</script>
