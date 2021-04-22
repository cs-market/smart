{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

<div class="items-container cm-sortable" data-ca-sortable-table="helpdesk_templates" data-ca-sortable-id-name="template_id" id="templates_list">
{assign var="skip_delete" value=false}
{if !"RESTRICTED_ADMIN"|defined}
    {include file="common/subheader.tpl" title=__("global") target='#global_templates'}
    <div id='global_templates' class="collapse in" style="padding-bottom: 40px;" >
    {if $templates.Y}
        <table class="table table-middle table-objects table-striped">
            <tbody>
                {foreach from=$templates.Y item=template name="pf"}
                    {include file="common/object_group.tpl"
                        id=$template.template_id
                        text=$template.name
                        href="message_templates.update?template_id=`$template.template_id`"
                        object_id_name="template_id"
                        table="helpdesk_templates"
                        href_delete="message_templates.delete?template_id=`$template.template_id`"
                        delete_target_id="templates_list"
                        skip_delete=$skip_delete
                        header_text="{__("editing_template")}: `$template.name`"
                        additional_class="cm-sortable-row cm-sortable-id-`$template.template_id`"
                        no_table=true
                        draggable=true
                        nostatus=true
                    }
                {/foreach}
            </tbody>
        </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
    </div>
{/if}

    {include file="common/subheader.tpl" title=__("private") target='#private_templates'}
    <div id='private_templates' class='collapse in' >
    {if $templates.N}
        <table class="table table-middle table-objects table-striped">
            <tbody>
                {foreach from=$templates.N item=template name="pf"}
                    {include file="common/object_group.tpl"
                        id=$template.template_id
                        text=$template.name
                        href="message_templates.update?template_id=`$template.template_id`"
                        object_id_name="template_id"
                        table="helpdesk_templates"
                        href_delete="message_templates.delete?template_id=`$template.template_id`"
                        delete_target_id="templates_list"
                        skip_delete=$skip_delete
                        header_text="{__("editing_template")}: `$template.name`"
                        additional_class="cm-sortable-row cm-sortable-id-`$template.template_id`"
                        no_table=true
                        draggable=true
                        nostatus=true
                    }
                {/foreach}
            </tbody>
        </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
    </div>
<!--templates_list--></div>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="templates:manage_tools_list"}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        {include file="addons/helpdesk/views/message_templates/update.tpl" template=[] hide_for_vendor=false}
    {/capture}
    {include file="common/popupbox.tpl" id="add_new_templates" text=__("new_template") content=$smarty.capture.add_new_picker title=__("add_template") act="general" icon="icon-plus"}
{/capture}

{include file="common/mainbox.tpl" title=__("message_templates") content=$smarty.capture.mainbox select_languages=false buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
