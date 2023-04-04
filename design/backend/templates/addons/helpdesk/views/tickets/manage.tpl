{capture name="mainbox"}

<div id="content_tickets">
{assign var="allow_save" value=true} {*TEMPORARY*}
<form action="{$index_script}" method="POST" name="update_tickets_form">
    <input type="hidden" name="redirect_url" value="{$config.current_url}" />
    {if $tickets}
    {include file="common/pagination.tpl" save_current_page=true div_id=$smarty.request.content_id}
        <table width="100%" class="table table-middle">
            <thead>
                <tr>
                    <th class="nowrap">{__("ticket_id")}</th>
                    <th class="nowrap" width="50%">{__("subject")}</th>
                    <th class="nowrap" width="20%">{__("users_list")}</th>
                    <th class="nowrap">{__("new_messages")} / {__("messages")}</th>
                    <th class="nowrap">{__("last_message_date")}</th>
                    <th width="5%">&nbsp;</th>
                </tr>
            </thead>
            {assign var="current_redirect_url" value=$config.current_url|escape:url}
            {foreach from=$tickets item=ticket}
            <tr class="cm-row-status-active" valign="top" >
                <td class="row-status"><a href="{"tickets.view&amp;ticket_id=`$ticket.ticket_id`"|fn_url}">{$ticket.ticket_id}</a></td>
                <td><a href="{"tickets.view&amp;ticket_id=`$ticket.ticket_id`"|fn_url}">{$ticket.subject}</a><p class="muted"><small>{$ticket.mailbox_name}</small></p></td>
                <td>
                    <ul class="helpdesk-users nowrap">
                    {foreach from=$ticket.users item=user}
                        <li {if $user.user_type != 'C'}class="hidden"{/if}>
                            <a class="user-image" href="{"profiles.update&amp;user_id=`$user.user_id`"|fn_url}">{"`$user.firstname` `$user.lastname`"|trim|default:"---"}</a>
                        </li>
                    {/foreach}
                    </ul>
                </td>
                <td class="center">{$ticket.count_new|default:0} / {$ticket.count_all}</td>
                <td class="center">{if $ticket.updated}{"j/m/Y G:i"|date:$ticket.updated}{else}-{/if}</td>
                <td class="nowrap">
                    {capture name="tools_list"}
                        <li>{btn type="list" text=__("edit") href="tickets.update?ticket_id=`$ticket.ticket_id`"}</li>
                        <li>{btn type="list" text=__("close_ticket") href="tickets.close?ticket_id=`$ticket.ticket_id`&redirect_url=`$current_redirect_url`"}</li>
                        {if $allow_save}
                        <li class="divider"></li>
                        <li>{btn type="list" text=__("delete") class="cm-confirm" href="tickets.delete?ticket_id=`$ticket.ticket_id`&redirect_url=`$current_redirect_url`"}</li>
                        <li class="divider"></li>
                        <li>{btn type="list" text=__("delete_spam") class="cm-confirm" href="tickets.delete?ticket_id=`$ticket.ticket_id`&amp;spam&redirect_url=`$current_redirect_url`"}</li>
                        {/if}
                    {/capture}
                    <div class="hidden-tools">
                        {dropdown content=$smarty.capture.tools_list}
                    </div>
                </td>
            </tr>
            {/foreach}
        </table>
    {include file="common/pagination.tpl" save_current_page=true div_id=$smarty.request.content_id}
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
</form>
<!--content_tickets--></div>
{/capture}

{capture name="buttons"}
    {include file="buttons/button.tpl" title=__("post_message") but_icon="icon-plus" but_role="action" but_href="tickets.add"}
{/capture}

{include file="common/mainbox.tpl" title=__("tickets") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra buttons=$smarty.capture.buttons no_sidebar=true}
