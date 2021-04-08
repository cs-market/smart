{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');
    var display_type = '{$smarty.request.display|escape:javascript nofilter}';

    $.ceEvent('on', 'ce.formpost_stickers_form', function(frm, elm) {
        var stickers = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                stickers[id] = $('#sticker_' + id).text();
            });
            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), stickers, 'c', {
                '{sticker_id}': '%id',
                '{sticker}': '%item'
            });
            {/literal}

            if (display_type != 'radio') {
                $.ceNotification('show', {
                    type: 'N', 
                    title: _.tr('notice'), 
                    message: _.tr('text_items_added'), 
                    message_state: 'I'
                });
            }
        }

        return false;
    });
}(Tygh, Tygh.$));
</script>
{/if}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="stickers_form">

<div class="items-container multi-level">
    {include file="addons/product_stickers/components/stickers_list.tpl" header=true checkbox_name=$smarty.request.checkbox_name|default:"stickers_ids" parent_id=$sticker_id display=$smarty.request.display show_links=false show_status=false}   
</div>

<div class="buttons-container">
    {if $smarty.request.display == "radio"}
        {assign var="but_close_text" value=__("choose")}
    {else}
        {assign var="but_close_text" value=__("add_stickers_and_close")}
        {assign var="but_text" value=__("add_stickers")}
    {/if}
    {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}
</div>

</form>
