{if $template}
    {assign var="id" value=$template.template_id}
{else}
    {assign var="id" value="0"}
{/if}

{assign var="allow_save" value=$template|fn_allow_save_object:"message_templates"}

<div id="content_group{$id}">

<form action="{""|fn_url}" method="post" name="templates_form_{$id}" enctype="multipart/form-data" class=" form-horizontal{if !$allow_save} cm-hide-inputs{/if}">
<input type="hidden" name="template_id" value="{$id}" />

<div class="cm-tabs-content" id="tabs_content_{$id}">
    <div id="content_tab_details_{$id}">
    <fieldset>
        <div class="control-group">
            <label for="elm_template_name_{$id}" class="cm-required control-label">{__("name")}:</label>
            <div class="controls">
                <input id="elm_template_name_{$id}" type="text" name="template_data[name]" value="{$template.name}">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_template_instructions_{$id}">{__("block_template")}:</label>
            <div class="controls">
                <textarea id="elm_template_instructions_{$id}" name="template_data[template]" cols="55" rows="8" class="cm-wysiwyg input-textarea-long">{$template.template}</textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_template_global_{$id}">{__("global")}:</label>
            <div class="controls">
                <input type="hidden" name="template_data[is_global]" value="N">
                <input id="elm_template_name_{$id}" type="checkbox" name="template_data[is_global]" {if $template.is_global == 'Y'} checked="checked" {/if} value="Y">
            </div>
        </div>
    </fieldset>
    <!--content_tab_details_{$id}--></div>
</div>

{if !$hide_for_vendor}
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[message_templates.update]" cancel_action="close" save=$id}
    </div>
{/if}

</form>
<!--content_group{$id}--></div>
