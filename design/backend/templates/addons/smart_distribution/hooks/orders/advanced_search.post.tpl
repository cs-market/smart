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

{$managers = []|fn_smart_distribution_get_managers}
<div class="control-group">
    <label class="control-label" for="manager">{__("manager")}</label>
    <div class="controls">
    <select name="managers" id="manager">
        <option value="">--</option>
        {foreach from=$managers item=manager}
            <option value="{$manager.user_id}" {if $search.managers == $manager.user_id}selected="selected"{/if}>{$manager.name}</option>
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
