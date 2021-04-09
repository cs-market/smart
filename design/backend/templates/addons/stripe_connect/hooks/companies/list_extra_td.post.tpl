<td class="row-status {if $company.stripe_connect_account_id}text-success{/if}">
    {if $company.stripe_connect_account_id}
        {$company.stripe_connect_account_id}
    {else}
        {__("stripe_connect.not_connected")}
    {/if}
</td>