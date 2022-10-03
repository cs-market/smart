{if $user_info.extra_data.returnable_packaging}
    <ul>
        {foreach from=$user_info.extra_data.returnable_packaging item='amount' key='packaging'}
        <li class="ty-dropdown-box__item">{$packaging}: {$amount}{__('items')}</li>
        {/foreach}
    </ul>
{/if}
{if $user_info.extra_data.equipment}
    <ul>
        {foreach from=$user_info.extra_data.equipment item='amount' key='equipment'}
        <li class="ty-dropdown-box__item">{$equipment}: {$amount}{__('items')}</li>
        {/foreach}
    </ul>
{/if}
