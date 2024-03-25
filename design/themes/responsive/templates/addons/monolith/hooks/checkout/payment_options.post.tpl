{if $auth.company_id == 45}
    {$extra = ""|fn_get_extra_user_data}
    {if $extra.returnable_packaging}
        {__('tara_text', ['[link]' => 'categories.view&category_id=9064'|fn_url])}
    {/if}
{/if}
