{if $mailbox}
    {assign var="id" value=$mailbox.mailbox_id}
{else}
    {assign var="id" value="0"}
{/if}

{assign var="allow_save" value=$mailbox|fn_allow_save_object:"mailboxes"}

<div id="content_group{$id}">

<form action="{""|fn_url}" method="post" name="mailboxs_form_{$id}" enctype="multipart/form-data" class=" form-horizontal{if !$allow_save} cm-hide-inputs{/if}">
<input type="hidden" name="mailbox_id" value="{$id}" />

<div class="cm-tabs-content" id="tabs_content_{$id}">
    <div id="content_tab_details_{$id}">
    <fieldset>
        <div class="control-group">
            <label for="elm_mailbox_mailbox_name_{$id}" class="cm-required control-label">{__("mailbox_name")}:</label>
            <div class="controls">
                <input id="elm_mailbox_mailbox_name_{$id}" type="text" name="mailbox_data[mailbox_name]" value="{$mailbox.mailbox_name}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_host_{$id}" class="cm-required control-label">{__("host")}:</label>
            <div class="controls">
                <input id="elm_mailbox_host_{$id}" type="text" name="mailbox_data[host]" value="{$mailbox.host}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_email_{$id}" class="cm-required control-label">{__("email")}:</label>
            <div class="controls">
                <input id="elm_mailbox_email_{$id}" type="text" name="mailbox_data[email]" value="{$mailbox.email}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_password_{$id}" class="cm-required control-label">{__("password")}:</label>
            <div class="controls">
                <input id="elm_mailbox_password_{$id}" type="password" name="mailbox_data[password]" value="{if $runtime.mode == "update"}            {/if}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_ticket_prefix_{$id}" class="cm-required control-label">{__("ticket_prefix")}:</label>
            <div class="controls">
                <input id="elm_mailbox_ticket_prefix_{$id}" type="text" name="mailbox_data[ticket_prefix]" value="{$mailbox.ticket_prefix}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_smtp_server_{$id}" class="control-label">{__("smtp_server")}:</label>
            <div class="controls">
                <input id="elm_mailbox_smtp_server_{$id}" type="text" name="mailbox_data[smtp_server]" value="{$mailbox.smtp_server}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_domain_{$id}" class="control-label">{__("domain")}:</label>
            <div class="controls">
                <input id="elm_mailbox_domain_{$id}" type="text" name="mailbox_data[domain]" value="{$mailbox.domain}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_private_{$id}" class="control-label">{__("private")}:</label>
            <div class="controls">
                <input id="elm_mailbox_private_{$id}" type="text" name="mailbox_data[private]" value="{$mailbox.private}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_selector_{$id}" class="control-label">{__("selector")}:</label>
            <div class="controls">
                <input id="elm_mailbox_selector_{$id}" type="text" name="mailbox_data[selector]" value="{$mailbox.selector}">
            </div>
        </div>
        <div class="control-group">
            <label for="elm_mailbox_admin_notifications_{$id}" class="control-label">{__("admin_notifications")}:</label>
            <div class="controls">
                {include
                    file="pickers/users/picker.tpl"
                    but_text=__("choose")
                    extra_url="&user_type=A"
                    data_id="responsible_admin"
                    but_meta="btn"
                    input_name="mailbox_data[responsible_admin]"
                    item_ids=$mailbox.responsible_admin
                    display="radio"
                    view_mode="single_button"
                    user_info=$mailbox.responsible_admin|fn_get_user_short_info
                }
            </div>
        </div>
    </fieldset>
    <!--content_tab_details_{$id}--></div>
</div>

{if !$hide_for_vendor}
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[mailboxes.update]" cancel_action="close" save=$id}
    </div>
{/if}

</form>
<!--content_group{$id}--></div>
