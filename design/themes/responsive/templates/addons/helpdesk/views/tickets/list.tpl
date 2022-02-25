<div class="tickets">
    {capture name="title"}
        <span>{__("tickets")}</span>
        <div class="ty-float-right">
            {include file="buttons/button.tpl" but_href="tickets.new" but_text=__("new_ticket") but_meta="ty-btn__primary"}
        </div>
    {/capture}

    {include file="common/pagination.tpl"}
        <table class="ty-table table-width">
            <thead>
                <tr>
                    <th>{__("subject")}</th>
                    <th style="width: 10%">{__("messages")}</th>
                    <th style="width: 10%">{__("new_messages")}</th>
                    <th style="width: 20%">{__("last_message_date")}</th>
                </tr>
            </thead>
            {foreach from=$tickets item=ticket}
            <tr>
                <td>
                    <a href="{"tickets.view&amp;ticket_id=`$ticket.ticket_id`"|fn_url}">{if $ticket.count_unviewed}<b>{/if}{$ticket.subject}{if $ticket.count_unviewed}</b>{/if}</a>
                </td>
                <td>
                    {$ticket.count_all}
                </td>
                <td>
                    {$ticket.count_unviewed|default:'0'}
                </td>
                <td>
                    {if $ticket.updated}{"j/m/Y G:i"|date:$ticket.updated}{else}-{/if}
                </td>
            </tr>
            {foreachelse}
                <tr class="ty-table__no-items">
                    <td colspan="4"><p class="ty-no-items">{__("text_no_tickets")}</p></td>
                </tr>
            {/foreach}
        </table>
    {include file="common/pagination.tpl"}
</div>
