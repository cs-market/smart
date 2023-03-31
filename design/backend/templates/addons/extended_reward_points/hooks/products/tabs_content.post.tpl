{if !"ULTIMATE:FREE"|fn_allowed_for}
    {assign var="usergroups" value=["type"=>"C", "status"=>["A", "H"]]|fn_get_usergroups}
{/if}
{if $is_partial_reward_points}
<div class="{if $selected_section !== "min_prices"}hidden{/if}" id="content_min_prices">
    <div class="control-group">
        {script src="js/tygh/filter_table.js"}
        <label class="control-label" for="elm_min_prices_search">{__('search')}</label>
        <div class="controls">
            <input type="text" id="min_prices_search" value="" size="30">
        </div>
    </div>
    <div class="table-responsive-wrapper">
        <table class="table table-middle table--relative table-responsive cm-filter-table" width="100%" data-ca-input-id="min_prices_search">
        <thead class="cm-first-sibling">
        <tr>
            <th width="35%">{__("value")}</th>
            {if !"ULTIMATE:FREE"|fn_allowed_for}
                <th width="45%">{__("usergroup")}</th>
            {/if}
            <th width="15%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$product_data.min_prices item="price" key="_key" name="prod_prices"}
        <tr class="cm-row-item">
            <td width="35%" class="{$no_hide_input_if_shared_product}" data-th="{__("value")}">
                <input type="text" name="product_data[min_prices][{$_key}][price]" value="{$price.min_price|default:"0.00"|fn_format_price:$primary_currency:null:false}" size="10" class="input-medium cm-numeric"/></td>
            {if !"ULTIMATE:FREE"|fn_allowed_for}
            <td width="45%" class="{$no_hide_input_if_shared_product}" data-th="{__("usergroup")}">
                    {$usergroup_id = $price.usergroup_id}
                    <input type="hidden" name="product_data[min_prices][{$_key}][usergroup_id]" value="{$usergroup_id}">
                    {$usergroups.$usergroup_id.usergroup}
                {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="price_`$_key`" name="update_all_vendors[prices][`$_key`]"}
                {assign var="default_usergroup_name" value=""}
                </td>
            {/if}
            <td width="15%" class="nowrap {$no_hide_input_if_shared_product} right">
                {include file="buttons/clone_delete.tpl" dummy_href=true microformats="cm-delete-row" no_confirm=true}
            </td>
        </tr>
        {/foreach}
        {math equation="x+1" x=$_key|default:0 assign="new_key"}
        <tr class="{cycle values="table-row , " reset=1}{$no_hide_input_if_shared_product}" id="box_add_min_prices">
            <td width="35%" data-th="{__("value")}">
                <input type="text" name="product_data[min_prices][{$new_key}][price]" value="0.00" size="10" class="input-medium cm-numeric" data-a-sep /></td>
            {if !"ULTIMATE:FREE"|fn_allowed_for}
            <td width="45%" data-th="{__("usergroup")}">
                <select id="usergroup_id" name="product_data[min_prices][{$new_key}][usergroup_id]" class="span3">
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
                {include file="buttons/multiple_buttons.tpl" item_id="add_min_prices"}
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</div>
{/if}
