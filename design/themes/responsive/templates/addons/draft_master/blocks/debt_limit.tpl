{if $extra_user_data.debt_data}
    {$total_debt = $extra_user_data.debt_data|array_column:"debt"|array_sum|cat:__('balance_currency')}
    <table class="ty-table debt-table">
        <tr>
            <td></td>
            <td>{__('fact')}</td>
            <td>{__('debt_limit')}</td>
        </tr>
        {foreach from=$extra_user_data.debt_data item='data' key='company'}
            <tr>
                <td>
                    {$company}
                </td>
                <td>
                    <span class="ty-debt-color {if $data.debt >= $data.limit} ty-debt-limit-exceeded {/if}">{$data.debt}{__('balance_currency')}</span>
                </td>
                <td>
                    <span class="ty-limit-color">{$data.limit}{__('balance_currency')}</span>
                </td>
            </tr>
        {/foreach}
    </table>
    {assign var="title" value="`$block.name` `$total_debt`" scope=parent}
{/if}
