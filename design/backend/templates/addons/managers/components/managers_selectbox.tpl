{$class = $class|default:           "control-group"}
{$key = $key|default:               'manager'}
{$label = $label|default:           __("manager")}
{$name  = $name|default:            __("managers")}
{$search_key = $search_key|default: "manager_users"}

{$managers = $params|default:[]|fn_get_managers}
<div class="{$class}">
    <label class="control-label" for="elm_{$key}">{$label}</label>
    <div class="controls">
    <select name="{$name}" id="elm_{$key}">
        <option value="">--</option>
        {if $params['group_by']}
            {foreach from=$managers key="group_name" item="group"}
                <optgroup label="{$group_name}">
                {foreach from=$group item="manager"}
                    <option value="{$manager.user_id}" {if $manager.user_id == $search.$search_key} selected="_selected"{/if}>{$manager.firstname} {$manager.lastname}</option>
                {/foreach}
                </optgroup>
            {/foreach}
        {else}
            {foreach from=$managers item="manager"}
                <option value="{$manager.user_id}" {if $manager.user_id == $search.$search_key} selected="_selected"{/if}>{$manager.firstname} {$manager.lastname}</option>
            {/foreach}
        {/if}
    </select>
    </div>
</div>
