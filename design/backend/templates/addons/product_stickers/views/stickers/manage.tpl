{capture name="mainbox"}
<form action="{""|fn_url}" method="post" name="stickers_form" id="stickers_form" class="form-horizontal form-edit cm-processed-form">
    <input type="hidden" name="fake" value="1" />
    {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
    {assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}

    {include file="addons/product_stickers/components/stickers_list.tpl" show_links=true show_status=true}

    {capture name="buttons"}
        {include file="buttons/button.tpl" title=__("add_sticker") but_icon="icon-plus" but_role="action" but_href="stickers.add"}
        {capture name="tools_list"}
        {if $stickers}
            <li>{btn type="delete_selected" dispatch="dispatch[stickers.m_delete]" form="stickers_form"}</li>
        {/if}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/capture}
</form>
{/capture}

{include file="common/mainbox.tpl" title=__("stickers") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons content_id="manage_users" select_languages=true}
