{if !$checkbox_name}{assign var="checkbox_name" value="sticker_ids"}{/if}
{include file="common/pagination.tpl" save_current_page=true div_id=$smarty.request.content_id}

{if $stickers}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="5%">
        {if $display != "radio"}
            {include file="common/check_items.tpl" check_statuses=''|fn_get_default_statuses:true}
        {/if}
    </th> 
    <th width="10%"><span>{__("preview")}</span></th>
    <th width="15%">{__("name")}</th>
    <th width="25%">{__("position")}</th>
    {if $show_links}<th width="5%"></th>{/if}
    {if $show_status}<th width="10%" class="right">{__("status")}</th>{/if}
</tr>
</thead>
{foreach from=$stickers item=sticker}
    <tr class="cm-row-status-{$status.status|lower}">
        <td class="left">
            {if $display == "radio"}
                <input type="radio" name="{$checkbox_name}" id="radio_{$sticker.sticker_id}" value="{$sticker.sticker_id}" class="cm-item" />
            {else}
                <input type="checkbox" name="{$checkbox_name}[]" id="checkbox_{$sticker.sticker_id}" value="{$sticker.sticker_id}" class="cm-item" />
            {/if}
        <td>
            {if $sticker.sticker_id}
                {if $sticker.type == 'G'}
                    {if $show_links}
                        {include file="common/image.tpl" image=$sticker.main_pair.icon|default:$sticker.main_pair.detailed image_id=$sticker.main_pair.image_id image_width=50 href="stickers.update?sticker_id=`$sticker.sticker_id`"|fn_url}
                    {else}
                        {include file="common/image.tpl" image=$sticker.main_pair.icon|default:$sticker.main_pair.detailed image_id=$sticker.main_pair.image_id image_width=50}
                    {/if}
                {else}
                    <span {if $sticker.properties} style="display: inline-block; {$sticker.properties|fn_product_sticker_render_styles}"{/if}>{$sticker.text|nl2br nofilter}</span>
                {/if}
            {/if}
        </td>   
        <td>
            <span id="sticker_{$sticker.sticker_id}">{if $show_links}<a href="{"stickers.update&sticker_id=`$sticker.sticker_id`"|fn_url}">{/if}{$sticker.name}{if $show_links}</a>{/if}</span>
            {include file="views/companies/components/company_name.tpl" object=$sticker}
        </td>
        <td>
            {if $sticker.position == 'A'}{__("top_left")}{elseif $sticker.position == 'B'}{__("top_center")}{elseif $sticker.position == 'C'}{__("top_right")}{elseif $sticker.position == 'D'}{__("bottom_left")}{elseif $sticker.position == 'E'}{__("bottom_center")}{elseif $sticker.position == 'F'}{__("bottom_right")}{elseif $sticker.position == 'G'}{__("middle_left")}{elseif $sticker.position == 'H'}{__("middle_center")}{elseif $sticker.position == 'I'}{__("middle_right")}{/if}
        </td>
        {if $show_links}
            <td class="right nowrap">
                {capture name="tools_list"}
                    <li>{btn type="list" text=__("edit") href="stickers.update?sticker_id=`$sticker.sticker_id`"}</li>
                    <li>{btn type="list" text=__("delete") class="cm-confirm" href="stickers.delete?sticker_id=`$sticker.sticker_id`"}</li>
                {/capture}
                <div class="hidden-tools">
                    {dropdown content=$smarty.capture.tools_list}
                </div>
            </td>
        {/if}
        {if $show_status}
            <td class="right">
                {include file="common/select_popup.tpl" id=$sticker.sticker_id status=$sticker.status hidden="" object_id_name="sticker_id" table="product_stickers"}
            </td>
        {/if}
    </tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
{include file="common/pagination.tpl" save_current_page=true div_id=$smarty.request.content_id}
