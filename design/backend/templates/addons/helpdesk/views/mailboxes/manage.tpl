{capture name="adv_buttons"}
    {include file="buttons/button.tpl" id="add_new_mailbox" text=__("new_mailbox") but_href="mailboxes.add" title=__("add_mailbox") but_role="action" but_icon="icon-plus"}
{/capture}

{capture name="mainbox"}
    <div class="items-container cm-sortable" data-ca-sortable-table="helpdesk_mailboxes" data-ca-sortable-id-name="helpdesk_mailboxes" id="mailboxes_list">
    {assign var="skip_delete" value=false}
    {if !"RESTRICTED_ADMIN"|defined}
        {if $mailboxes}
            <div class="table-wrapper">
                <table class="table table-middle table-objects">
                {foreach from=$mailboxes item=mailbox}
                    {include file="common/object_group.tpl"
                        id=$mailbox.mailbox_id
                        text=$mailbox.mailbox_name
                        href="mailboxes.update?mailbox_id=`$mailbox.mailbox_id`"
                        href_delete="mailboxes.delete?mailbox_id=`$mailbox.mailbox_id`" delete_target_id="mailboxes_list,actions_panel"
                        header_text="{__("editing_mailbox")}: `$mailbox.mailbox_name`"
                        additional_class="cm-sortable-row cm-sortable-id-`$mailbox.mailbox_name`"
                        table="helpdesk_mailboxes"
                        object_id_name="mailbox_id"
                        no_table=true
                        no_popup=true
                        draggable=false
                        tool_items=$smarty.capture.tool_items
                        extra_data=$smarty.capture.extra_data
                        text_wrap=true
                        status=$mailbox.status
                    }
                {/foreach}
                </table>
            </div>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
        </div>
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=__("mailboxes") content=$smarty.capture.mainbox select_languages=false adv_buttons=$smarty.capture.adv_buttons}
