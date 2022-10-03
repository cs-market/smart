{if $user_info.managers}
    <ul>
        {foreach from=$user_info.managers item='manager'}
            <li class="ty-dropdown-box__item"><a href="tel:{$manager.phone|regex_replace:"/[^0-9]/":""}">{$manager.name}</a></li>
        {/foreach}
    </ul>
{/if}
