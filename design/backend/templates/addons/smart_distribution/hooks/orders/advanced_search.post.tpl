<div class="group">
    <div class="control-group">
        <label class="control-label">{__("ordered_category_products")}</label>
        <div class="controls">
            {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="category_ids" item_ids=$s_cid hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
        </div>
    </div>

    {$usergroups = []|fn_get_usergroups}
    <div class="control-group">
        <label class="control-label" for="usergroup">{__("usergroups")}</label>
        <div class="controls">
        <select name="usergroup_id" id="usergroup">
            <option value="">--</option>
            {foreach from=$usergroups item=usergroup}
                <option value="{$usergroup.usergroup_id}" {if $search.usergroup_id == $usergroup.usergroup_id}selected="selected"{/if}>{$usergroup.usergroup}</option>
            {/foreach}
        </select>
        </div>
    </div>

    {$promotions = []|fn_get_promotions|reset}
    <div class="control-group">
        <label class="control-label" for="promotion">{__("promotion")}</label>
        <div class="controls">
        <select name="promotion_id" id="promotion">
            <option value="">--</option>
            {foreach from=$promotions item=promotion}
                <option value="{$promotion.promotion_id}" {if $search.promotion_id == $promotion.promotion_id}selected="selected"{/if}>{$promotion.name}</option>
            {/foreach}
        </select>
        </div>
    </div>
</div>
