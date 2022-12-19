{if ""|fn_get_mailboxes}
{$tickets = ''|fn_get_tickets:0}

{if $addons.helpdesk.ticketing_system == 'Y'}
    {$url = "tickets.list"}
{else}
    {$url = "tickets.view"}
{/if}
<li class="ty-account-info__item ty-dropdown-box__item ty-account-info__helpdesk"><a class="ty-account-info__a underlined" href="{$url|fn_url}" rel="nofollow">{__("helpdesk")}</a>{if $tickets[1]['has_unviewed']}<span class="helpdesk-unviewed"></span>{/if}</li>
{/if}
