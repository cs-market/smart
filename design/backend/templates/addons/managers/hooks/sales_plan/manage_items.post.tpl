{if $item.type == 'manager_selectbox'}
    {include file="addons/managers/components/managers_selectbox.tpl" 
        params=['group_by' => 'user_role_descr']
        class="sidebar-field `$item.class`"
        label=__($item.label)
        key=$key
        name=$name 
        search_key=$key
    }
{/if}
