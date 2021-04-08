{if $sticker_id}
    {assign var="sticker" value=$sticker_id|fn_get_sticker_name_by_id|default:"`$ldelim`sticker`$rdelim`"}
{else}
    {assign var="sticker" value=$default_name}
{/if}

{if $multiple}
    <tr {if !$clone}id="{$holder}_{$sticker_id}" {/if}class="cm-js-item {if $clone} cm-clone hidden{/if}">
        {if $position_field}<td><input type="text" name="{$input_name}[{$sticker_id}]" value="{math equation="a*b" a=$position b=10}" size="3" class="input-text-short"{if $clone} disabled="disabled"{/if} /></td>{/if}
        <td>
            {if !$show_only_name}
                <a href="{"stickers.update?sticker_id=`$sticker_id`"|fn_url}">{$sticker}</a>
            {else}
                {$sticker}
            {/if}
        </td>
        <td width="5%" class="nowrap">
        {if !$view_only || $show_only_name}
            {capture name="tools_list"}
                <li>{btn type="list" text=__("edit") href="stickers.update?sticker_id=`$sticker_id`"}</li>
                {if !$hide_delete_button}
                    <li>{btn type="list" text=__("remove") onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$sticker_id}', 'c'); return false;"}</li>
                {/if}
            {/capture}
            <div class="hidden-tools">
                {dropdown content=$smarty.capture.tools_list}
            </div>
        {/if}
        </td>
    </tr>
{else}
    {if $view_mode != "list"}
        <span {if !$clone}id="{$holder}_{$sticker_id}" {/if}class="cm-js-item {if $clone} cm-clone hidden{/if}">
        {if !$first_item && $single_line}<span class="cm-comma{if $clone} hidden{/if}">,&nbsp;&nbsp;</span>{/if}

        <div class="input-append">
        <input class="cm-picker-value-description {$extra_class}" type="text" value="{$sticker}" {if $display_input_id}id="{$display_input_id}"{/if} size="10" name="sticker_name" readonly="readonly" {$extra} id="appendedInputButton">
        {if !$runtime.company_id || $runtime.controller != "companies"}
        {if $multiple}
            {assign var="_but_text" value=$but_text|default:__("add_stickers")}
            {assign var="_but_role" value="add"}
            {assign var="_but_icon" value="icon-plus"}
        {else}
            {assign var="_but_text" value="<i class='icon-plus'></i>"}
            {assign var="_but_role" value="icon"}
        {/if}
        {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="stickers.picker?display=`$display`&company_ids=`$company_ids`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&root=`$default_name`&except_id=`$except_id`&data_id=`$data_id``$extra_url`"|fn_url but_text=$_but_text but_role=$_but_role but_icon=$_but_icon but_target_id="content_`$data_id`" but_meta="`$but_meta` cm-dialog-opener add-on btn"}
        {/if}
        </div>
        </span>
    {else}
        {assign var="default_sticker" value="`$ldelim`sticker`$rdelim`"}
        {assign var="default_sticker_id" value="`$ldelim`sticker_id`$rdelim`"}
        {if $first_item || !$sticker_id}
            <p class="cm-js-item cm-clone hidden">
                {if $hide_input != "Y"}
                    <label class="radio inline-block" for="sticker_rb_{$default_sticker_id}">
                        <input id="sticker_rb_{$default_sticker_id}" type="radio" name="{$radio_input_name}" value="{$default_sticker_id}">
                    </label>
                {/if}
                    {$default_sticker}
                    <a class="icon-remove-sign cm-tooltip hand" onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$default_sticker_id}', 'c'); return false;" title="{__("remove")}"></a>
            </p>
        {/if}
        {if $sticker_id}
        <div class="cm-js-item {$extra_class}" id="{$holder}_{$sticker_id}" {$extra}>
            {if $hide_input != "Y"}
                <label class="radio inline-block" for="sticker_radio_button_{$sticker_id}">
                    <input id="sticker_radio_button_{$sticker_id}" {if $main_sticker == $sticker_id}checked{/if} type="radio" name="{$radio_input_name}" value="{$sticker_id}" />
                </label>
            {/if}

            {$sticker}
            
            <a onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$sticker_id}', 'c'); return false;" class="icon-remove-sign cm-tooltip hand " title="{__("remove")}"></a>
        </div>
        {/if}
    {/if}
{/if}
