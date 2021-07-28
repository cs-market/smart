{include file="common/subheader.tpl" title=__("ticket_log")}
<input type="hidden" name="redirect_url" value="{$config.current_url}" />
{if $messages}
    {include file="common/pagination.tpl" save_current_page=true div_id=$smarty.request.content_id}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" class="table hidden-inputs">
        <tr>
            {*<th width="7%" class="nowrap">{__("message_id")}</th>*}
            <th width="75%">{__("message")}</th>
            {*<th width="10%">{__("posted_by")}</th>
            <th width="10%">{__("date")}</th>*}
            <th width="10%">{__("additional_data")}</th>
            <th width="5%">&nbsp;</th>
            <th class="right" width="5%">{__("status")}</th>
        </tr>

        {assign var="current_redirect_url" value=$config.current_url|escape:url}
        {foreach from=$messages item=message key=message_id}
        <tr valign="top" {if $message.notified != "Y"} class='not-notified' {/if}>
            <td>
                <div class="wysiwyg-content" style="">
                    {$message.message|unescape nofilter}
                    
                    {if $message.files}
                        <hr/>
                        <div class="attached-files">
                            <b>{__("files")}:</b>
                            {foreach from=$message.files item="file"}
                                </br><a href="{"tickets.get_file?file_id=`$file.file_id`"|fn_url}">{$file.filename}</a>
                            {/foreach}
                        </div>
                    {/if}
                </div>
            </td>
            <td>
                <p>#{if $runtime.mode == 'search'}<a href="{"tickets.view?ticket_id=`$message.ticket_id`"|fn_url}">{/if}{$message_id}{if $runtime.mode == 'search'}</a>{/if}</p>
                <p><a class="user-image" href="{"profiles.update&amp;user_id=`$message.user_id`"|fn_url}">{$message.user|trim|default:'---'}</a></p>
                <p>{$message.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</p>
            </td>
            <td class="nowrap">
                {capture name="tools_list"}
                    {if $smarty.const.ACCOUNT_TYPE == 'admin' || ($message.notified != 'Y' && $auth.user_id == $message.user_id)}<li>{btn type="list" text=__("update_message") href="tickets.update_message?message_id=`$message_id`"}</li>{/if}
                    {if 'tickets.move_message'|fn_check_view_permissions:'GET'}<li><a id="{$message.message_id}" class="cm-process-items move-message">{__("move_to_another_ticket")}</a></li>{/if}
                    <li class="divider"></li>
                    <li>{btn type="list" text=__("delete") class="cm-confirm" href="tickets.delete_message?message_id=`$message_id`&redirect_url=`$current_redirect_url`"}</li>
                {/capture}
                <div class="hidden-tools">
                    {dropdown content=$smarty.capture.tools_list}
                </div>
            </td>
            <td class="right nowrap">{include file="common/select_popup.tpl" id=$message_id status=$message.status items_status="helpdesk"|fn_get_predefined_statuses object_id_name="message_id" table="helpdesk_messages"}</td>
        </tr>
        {/foreach}
    </table>

    <script>
    $('.move-message').click( function(event) {
        if (result = prompt('ticket ID', '')) {
            $url = fn_url('tickets.move_message?message_id=' + this.id + '&ticket_id=' + result);
            $.redirect($url);
        }
    });
    </script>

    {include file="common/pagination.tpl" save_current_page=true div_id=$smarty.request.content_id}
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
<!--content_tickets--></div>
