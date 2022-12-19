function fn_change_amount(inp, increase = true) {
    step = 1;
    if (inp.data('caStep')) {
        step = parseFloat(inp.data('caStep'));
    }
    if (step % 1 == 0) {
        return true;
    }
    var min_qty = 0;
    if(inp.data('caMinQty')) {
        min_qty = parseFloat(inp.data('caMinQty'));
    }
    var new_val = parseFloat((parseFloat(inp.data('caVal')) + ((increase) ? step : -step)).toFixed(3));
    new_val = (new_val > min_qty ? new_val : min_qty);
    inp.val(new_val);
    inp.data('caVal', new_val);
}
(function(_, $) {
    $.ceEvent('on', 'ce.valuechangerincrease', function(inp, step, min_qty, new_val) {
        fn_change_amount(inp);
    });
    $.ceEvent('on', 'ce.valuechangerdecrease', function(inp, step, min_qty, new_val) {
        fn_change_amount(inp, false);
    });
})(Tygh, Tygh.$);
