{if $form}
    {assign var="id" value=$form.submit_id}
{else}
    {assign var="id" value=0}
{/if}
{assign var="form_values" value=$form.form_data}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit cm-check-changes {if !$form|fn_allow_save_object:"" || $runtime.action == 'answer' }  cm-hide-inputs{/if}" name="forms_form_{$id}" enctype="multipart/form-data">

    <input type="hidden" name="fake" value="1" />
    <input type="hidden" class="cm-no-hide-input" name="submit_id" value="{$form.submit_id}" />

    {foreach from=$form_data.form.elements key="element_id" item="element"}

    {if $element.element_type == $smarty.const.FORM_SEPARATOR}
        <hr class="ty-form-builder__separator" />
    {elseif $element.element_type == $smarty.const.FORM_HEADER}

        {include file="common/subheader.tpl" title=$element.description}

    {elseif $element.element_type != $smarty.const.FORM_IP_ADDRESS && $element.element_type != $smarty.const.FORM_REFERER}
        <div class="control-group">
            <label for="{if $element.element_type == $smarty.const.FORM_FILE}type_{"fb_files[`$element.element_id`]"|md5}{else}elm_{$element.element_id}{/if}" class="control-label {if $element.required == "Y"}cm-required{/if}{if $element.element_type == $smarty.const.FORM_EMAIL} cm-email{/if}{if $element.element_type == $smarty.const.FORM_PHONE} cm-phone{/if} {if $element.element_type == $smarty.const.FORM_MULTIPLE_CB}cm-multiple-checkboxes{/if}">{$element.description}</label>
            <div class="controls">
                {if $element.element_type == $smarty.const.FORM_SELECT}
                    <select id="elm_{$element.element_id}" class="ty-form-builder__select" name="form[{$element.element_id}]">
                        <option label="" value="">- {__("select")} -</option>
                    {foreach from=$element.variants item=var}
                        <option value="{$var.element_id}" {if $form_values.$element_id == $var.element_id}selected="selected"{/if}>{$var.description}</option>
                    {/foreach}
                    </select>
                    
                    
                {elseif $element.element_type == $smarty.const.FORM_RADIO}
                    {foreach from=$element.variants item=var name="rd"}
                    <label class="ty-form-builder__radio-label">
                        <input class="ty-form-builder__radio radio" {if (!$form_values && $smarty.foreach.rd.iteration == 1) || ($form_values.$element_id == $var.element_id)}checked="checked"{/if} type="radio" name="form[{$element.element_id}]" value="{$var.element_id}" />{$var.description}&nbsp;&nbsp;
                    </label>
                    {/foreach}
                    
                    
                {elseif $element.element_type == $smarty.const.FORM_CHECKBOX}
                    <input type="hidden" name="form[{$element.element_id}]" value="N" />
                    <input id="elm_{$element.element_id}" class="ty-form-builder__checkbox checkbox" {if $form_values.$element_id == "Y"}checked="checked"{/if} type="checkbox" name="form[{$element.element_id}]" value="Y" />
                    
                    
                {elseif $element.element_type == $smarty.const.FORM_MULTIPLE_SB}
                    <select class="ty-form-builder__multiple-select" id="elm_{$element.element_id}" name="form[{$element.element_id}][{$var.element_id}]" multiple="multiple" >
                        {foreach from=$element.variants item=var}
                        {assign var="opt_value" value=$form_values.$element_id}
                            {assign var="eid" value=$var.element_id}
                            <option value="{$eid}" {if $opt_value.$eid == $eid}selected="selected"{/if}>{$var.description}</option>
                        {/foreach}
                    </select>
                    
                    
                {elseif $element.element_type == $smarty.const.FORM_MULTIPLE_CB}

                    <div id="elm_{$element.element_id}">
                    {foreach from=$element.variants item=var}
                        <label class="ty-form-builder__checkbox-label">
                            {assign var="opt_value" value=$form_values.$element_id}
                            {assign var="eid" value=$var.element_id}
                            <input class="ty-form-builder__checkbox" type="checkbox" {if $opt_value.$eid == $var.element_id}checked="checked"{/if} id="elm_{$element.element_id}_{$eid}" name="form[{$element.element_id}][{$eid}]" value="{$eid}" />
                            {$var.description}
                        </label>
                    {/foreach}
                    </div>
                    
                    
                {elseif $element.element_type == $smarty.const.FORM_INPUT}
                    <input id="elm_{$element.element_id}" class="ty-form-builder__input-text ty-input-text" size="50" type="text" name="form[{$element.element_id}]" value="{$form_values.$element_id}" />

                {elseif $element.element_type == $smarty.const.FORM_TEXTAREA}
                    <textarea id="elm_{$element.element_id}" class="ty-form-builder__textarea" name="form[{$element.element_id}]" cols="67" rows="10">{$form_values.$element_id}</textarea>

                {elseif $element.element_type == $smarty.const.FORM_DATE}
                    {include file="common/calendar.tpl" date_name="form[`$element.element_id`]" date_id="elm_`$element.element_id`" date_val=$form_values.$element_id}

                {elseif $element.element_type == $smarty.const.FORM_EMAIL || $element.element_type == $smarty.const.FORM_NUMBER || $element.element_type == $smarty.const.FORM_PHONE}

                    {if $element.element_type == $smarty.const.FORM_EMAIL}
                    <input type="hidden" name="customer_email" value="{$element.element_id}" />
                    {/if}
                    <input id="elm_{$element.element_id}" class="ty-input-text" size="50" type="text" name="form[{$element.element_id}]" value="{$form_values.$element_id}" />
                    
                {elseif $element.element_type == $smarty.const.FORM_COUNTRIES}

                    {$_country = $form_values.$element_id|default:$settings.General.default_country}
                    <select id="elm_{$element.element_id}" name="form[{$element.element_id}]" class="ty-form-builder__country cm-country cm-location-billing">
                        <option value="">- {__("select_country")} -</option>
                        {assign var="countries" value=1|fn_get_simple_countries}
                        {foreach from=$countries item="country" key="code"}
                        <option value="{$code}" {if $_country == $code}selected="selected"{/if}>{$country}</option>
                        {/foreach}
                    </select>

                {elseif $element.element_type == $smarty.const.FORM_STATES}
                    {$_state = $form_values.$element_id}
                    {$_country = $settings.General.default_country}
                    <select class="ty-form-builder__state" id="elm_{$element.element_id}" name="form_values[{$element.element_id}]">
                        <option label="" value="">- {__("select_state")} -</option>
                        {assign var="states" value=1|fn_get_all_states}
                        {foreach from=$states.$_country item="state"}
                            <option value="{$state.code}" {if $_state == $state.state}selected="selected"{/if}>{$state.state}</option>
                        {/foreach}
                    </select>
                {elseif $element.element_type == $smarty.const.FORM_FILE}
                    {$form_values.$element_id}
                {/if}
            </div>
        </div>
    {/if}
    {/foreach}
    <div class="control-group">
        <label for="forms_data{$form.submit_id}" class="control-label">{__("comments")}</label>
        <div class="controls">
            <textarea class="span6" rows="4" cols="25" name="comments" id="forms_data{$form.submit_id}">{strip}
                {$form.comments}
            {/strip}</textarea>
        </div>
    </div>
    {if $runtime.action != 'answer'}
        {assign var="form_status_descr" value=$smarty.const.STATUSES_POPUP_PAGES|fn_get_simple_statuses}
        {include file="common/select_status.tpl" input_name="status" id="elm_question_status" obj=$form hidden=true items_status=$form_status_descr}
    {else}
    <div class="control-group">
        <label for="forms_data{$form.submit_id}" class="control-label cm-trim">{__("answer")}</label>
        <div class="controls">
            <textarea class="span6 cm-no-hide-input" rows="4" cols="25" name="answer" id="forms_data{$form.submit_id}"></textarea>
        </div>
    </div>
    {/if}

<div class="modal-footer buttons-container">
    {if $runtime.action == 'answer'}
        {include file="buttons/button.tpl" but_name="dispatch[sent_forms.answer]" but_text=__('answer') but_role="submit" but_meta="btn btn-primary cm-ajax cm-dialog-closer" cancel_action="close" save=$id}
    {elseif $form|fn_allow_save_object:""}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[sent_forms.update]" cancel_action="close" save=$id}
    {/if}
</div>

</form>