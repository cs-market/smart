{if $user_info.extra_data.debt_data}
    {$total_debt = $user_info.extra_data.debt_data|array_column:"debt"|array_sum}
    <ul>
        {foreach from=$user_info.extra_data.debt_data item='data' key='company'}
            <li class="ty-dropdown-box__item ty-nowrap">{$company}: <span class="ty-debt-color {if $data.debt >= $data.limit} ty-debt-limit-exceeded {/if}">{$data.debt}р</span> / <span class="ty-limit-color">{$data.limit}р</span></li>
        {/foreach}
    </ul>
    {assign var="title" value="`$block.name` $total_debtр" scope=parent}
{/if}
