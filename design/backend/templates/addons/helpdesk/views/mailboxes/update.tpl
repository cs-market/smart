{capture name="mainbox"}
{if $mailbox}
    {assign var="id" value=$mailbox.mailbox_id}
{else}
    {assign var="id" value="0"}
{/if}

{assign var="allow_save" value=$mailbox|fn_allow_save_object:"mailboxes"}

<div id="content_group{$id}">

<form action="{""|fn_url}" method="post" name="mailbox_form_{$id}" enctype="multipart/form-data" class=" form-horizontal{if !$allow_save} cm-hide-inputs{/if}">
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
        {if "MULTIVENDOR"|fn_allowed_for}
            {include file="views/companies/components/company_field.tpl"
                name="mailbox_data[company_id]"
                id="elm_mailbox_data_company_id_`$id`"
                selected=$mailbox.company_id
                zero_company_id_name_lang_var="none"
            }
        {else}
            <div class="control-group">
                {$company_field_name = $company_field_name|default: __("owner_company")}
                <label class="control-label" for="elm_mailbox_data_company_id_{$id}">{$company_field_name}</label>
                <div class="controls">
                    <input type="hidden" class="cm-no-failed-msg" name="mailbox_data[company_id]" id="elm_mailbox_data_company_id_{$id}" value="{$mailbox.company_id}">
                    {include file="common/ajax_select_object.tpl"
                        data_url="companies.get_companies_list?show_all=Y&default_label=none"
                        text=$mailbox.company_id|fn_get_company_name:"none"
                        result_elm="elm_mailbox_data_company_id_`$id`"
                        id="elm_mailbox_data_company_id_`$id`_selector"
                    }
                </div>
            </div>
        {/if}

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
                }
            </div>
        </div>
    </fieldset>
    <!--content_tab_details_{$id}--></div>
</div>

</form>
<!--content_group{$id}--></div>

{/capture}

{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_role="submit-link" but_name="dispatch[mailboxes.update]" but_target_form="mailbox_form_{$id}" save=$id}
{/capture}

{capture name="mainbox_title"}
    {if $mailbox.mailbox_id}
        {"{__("editing_mailbox")}: `$mailbox.mailbox_name`"}
    {else}
        {__("new_mailbox")}
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=$smarty.capture.mainbox_title content=$smarty.capture.mainbox select_languages=false buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
