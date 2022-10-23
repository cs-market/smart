{if $item.type == 'manager_selectbox'}
    <div class="sidebar-field {$item.class}">
        <label for="elm_{$key}">{__($item.label)}</label>
        <div class="break">
            <select name="{$name}" id="elm_{$key}">
                <option value="">--</option>
                {$managers = ""|fn_get_managers}
                {foreach from=$managers item="manager"}
                    <option value="{$manager.user_id}" {if $search.$key == $user_id} selected="selected" {/if}>{$manager.name}</option>
                {/foreach}
            </select>
        </div>
    </div>
{/if}
