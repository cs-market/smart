{script src="js/addons/product_packages/func.js"}
<script type="text/javascript">
    (function(_, $) {
        $(document).ready(function () {
            $(_.doc).on('change', '.cm-packages-switcher', function (e) {
                state = $(this).prop('checked');
                qty_id = $('#' + $(this).data('caQtyInput'));
                new_step = state ? 1 : $(this).data('caStep');
                qty_id.attr('data-ca-step', new_step).data('caMinQty', new_step).val(new_step).trigger('change');
                id = qty_id.attr('id').replace('qty_count_', '');
                new_box_contains = state ? 1 : $('#for_qty_count_'+id).attr('data-ca-box-contains');
                $('#for_qty_count_'+id).data('caBoxContains', new_box_contains);
                $.ceEvent('trigger', 'ce.valuechangerincrease', [qty_id, new_step, new_step, new_step]);
            });
        });

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
    }(Tygh, Tygh.$));
</script>
