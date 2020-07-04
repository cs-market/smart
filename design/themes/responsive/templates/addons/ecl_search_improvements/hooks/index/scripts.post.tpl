<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    $.ceEvent('on', 'ce.formpre_search_form', function(frm, elm) {

        var q = $('input.ty-search-block__input', frm).focus();

        if (q.val().length < {$addons.ecl_search_improvements.char_amount|default:0}) {
            var old_bk = q.css('border-left-color');
            q.css('border-color', '{$addons.ecl_search_improvements.border_color}');
            setTimeout (function () {
                q.animate({
                    borderColor: old_bk,
                }, 600);
            }, 300);
            return false;
        }
    });
}(Tygh, Tygh.$));
//]]>
</script>