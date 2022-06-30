<script type="text/javascript">

(function(_, $) {
    function fn_change_amount_value(inp, new_val) {
        val = inp.val();
        elm = fn_find_closest_elm(inp, '.cm-box-value-changer', 8);
        if (elm.length != 0) {
            box_val = +(val/elm.data('caBoxContains')).toFixed(2);
            elm.text(box_val);
        }
    }

    function fn_find_closest_elm(elm, target, depth = 4) {
        parent = elm.parent();
        result = $(target, parent);
        if (result.length == 0 && depth) {
            result = fn_find_closest_elm(parent, target, depth-1);
        }
        return result;
    }

    $.ceEvent('on', 'ce.valuechangerincrease', function(inp, step, min_qty, new_val) {
        fn_change_amount_value(inp, new_val)
    });
    $.ceEvent('on', 'ce.valuechangerdecrease', function(inp, step, min_qty, new_val) {
        fn_change_amount_value(inp, new_val)
    });
}(Tygh, Tygh.$));

</script>
