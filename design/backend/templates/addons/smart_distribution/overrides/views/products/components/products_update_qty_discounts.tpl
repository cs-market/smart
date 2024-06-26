{if !"ULTIMATE:FREE"|fn_allowed_for}
    {assign var="usergroups" value=["type"=>"C", "status"=>["A", "H"]]|fn_get_usergroups}
{/if}

<div class="{if $selected_section !== "qty_discounts"}hidden{/if}" id="content_qty_discounts">
    <div class="control-group">
        {script src="js/tygh/filter_table.js"}
        <label class="control-label" for="elm_prices_search">{__('search')}</label>
        <div class="controls">
            <input type="text" id="prices_search" value="" size="30">
        </div>
    </div>
    <div class="table-responsive-wrapper">
        <table class="table table-middle table--relative table-responsive cm-filter-table" width="100%" data-ca-input-id="prices_search">
        <thead class="cm-first-sibling">
        <tr>
            <th width="5%">{__("quantity")}</th>
            <th width="20%">{__("value")}</th>
            <th width="25%">{__("type")}{include file="common/tooltip.tpl" tooltip=__("qty_discount_type_tooltip")}</th>
            {if !"ULTIMATE:FREE"|fn_allowed_for}
                <th width="25%">{__("usergroup")}</th>
            {/if}
            <th width="15%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$product_data.prices item="price" key="_key" name="prod_prices"}
        <tr class="cm-row-item">
            <td width="5%" class="{$no_hide_input_if_shared_product}" data-th="{__("quantity")}">
                {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                    &nbsp;{$price.lower_limit}
                {else}
                <input type="text" name="product_data[prices][{$_key}][lower_limit]" value="{$price.lower_limit}" class="input-micro" />
                {/if}</td>
            <td width="20%" class="{$no_hide_input_if_shared_product}" data-th="{__("value")}">
                {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                    &nbsp;{if $price.percentage_discount == 0}{$price.price|default:"0.00"|fn_format_price:$primary_currency:null:false}{else}{$price.percentage_discount}{/if}
                {else}
                <input type="text" name="product_data[prices][{$_key}][price]" value="{if $price.percentage_discount == 0}{$price.price|default:"0.00"|fn_format_price:$primary_currency:null:false}{else}{$price.percentage_discount}{/if}" size="10" class="input-medium cm-numeric" data-a-sep />
                {/if}</td>
            <td width="25%" class="{$no_hide_input_if_shared_product}" data-th="{__("type")}">
                {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                    &nbsp;{if $price.percentage_discount == 0}{__("absolute")}{else}{__("percent")}{/if}
                {else}
                <select class="span3" name="product_data[prices][{$_key}][type]">
                    <option value="A" {if $price.percentage_discount == 0}selected="selected"{/if}>{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                    <option value="P" {if $price.percentage_discount != 0}selected="selected"{/if}>{__("percent")} (%)</option>
                </select>
                {/if}</td>
            {if !"ULTIMATE:FREE"|fn_allowed_for}
            <td width="25%" class="{$no_hide_input_if_shared_product}" data-th="{__("usergroup")}">
                {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                    &nbsp;{__("all")}
                {else}
                    {$usergroup_id = $price.usergroup_id}
                    <input type="hidden" name="product_data[prices][{$_key}][usergroup_id]" value="{$usergroup_id}">
                    {$usergroups.$usergroup_id.usergroup}
                {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="price_`$_key`" name="update_all_vendors[prices][`$_key`]"}
                {assign var="default_usergroup_name" value=""}
                {/if}</td>
            {/if}
            <td width="15%" class="nowrap {$no_hide_input_if_shared_product} right">
                {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                &nbsp;{else}
                {include file="buttons/clone_delete.tpl" dummy_href=true microformats="cm-delete-row" no_confirm=true}
                {/if}
            </td>
        </tr>
        {/foreach}
        {math equation="x+1" x=$_key|default:0 assign="new_key"}
        <tr class="{cycle values="table-row , " reset=1}{$no_hide_input_if_shared_product}" id="box_add_qty_discount">
            <td width="5%" data-th="{__("quantity")}">
                <input type="text" name="product_data[prices][{$new_key}][lower_limit]" value="" class="input-micro" /></td>
            <td width="20%" data-th="{__("value")}">
                <input type="text" name="product_data[prices][{$new_key}][price]" value="0.00" size="10" class="input-medium cm-numeric" data-a-sep /></td>
            <td width="25%" data-th="{__("type")}">
            <select class="span3" name="product_data[prices][{$new_key}][type]">
                <option value="A" selected="selected">{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                <option value="P">{__("percent")} (%)</option>
            </select></td>
            {if !"ULTIMATE:FREE"|fn_allowed_for}
            <td width="25%" data-th="{__("usergroup")}">
                <select id="usergroup_id" name="product_data[prices][{$new_key}][usergroup_id]" class="span3">
                    {foreach from=fn_get_default_usergroups() item="usergroup"}
                        <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                    {/foreach}
                    {foreach from=$usergroups item="usergroup"}
                        <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                    {/foreach}
                </select>
                {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="price_`$new_key`" name="update_all_vendors[prices][`$new_key`]"}
            </td>
            {/if}
            <td width="15%" class="right">
                {include file="buttons/multiple_buttons.tpl" item_id="add_qty_discount"}
            </td>
        </tr>
        </tbody>
        </table>
    </div>

</div>
