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

				{$_country = $form_values.$elm_id|default:$settings.General.default_country}
				<select id="elm_{$element.element_id}" name="form[{$element.element_id}]" class="ty-form-builder__country cm-country cm-location-billing">
					<option value="">- {__("select_country")} -</option>
					{assign var="countries" value=1|fn_get_simple_countries}
					{foreach from=$countries item="country" key="code"}
					<option value="{$code}" {if $_country == $code}selected="selected"{/if}>{$country}</option>
					{/foreach}
				</select>

			{elseif $element.element_type == $smarty.const.FORM_STATES}

				{include file="views/profiles/components/profiles_scripts.tpl" states=1|fn_get_all_states}

				{$_state = $form_values.$elm_id|default:$settings.General.default_state}
				<select class="ty-form-builder__state cm-state cm-location-billing" id="elm_{$element.element_id}" name="form[{$element.element_id}]">
					<option label="" value="">- {__("select_state")} -</option>
				</select>
				<input type="text" class="cm-state cm-location-billing ty-input-text hidden" id="elm_{$element.element_id}_d" name="form[{$element.element_id}]" size="32" maxlength="64" value="{$_state}" disabled="disabled" />
			
			{elseif $element.element_type == $smarty.const.FORM_FILE}
				{script src="js/tygh/fileuploader_scripts.js"}
				{include file="common/fileuploader.tpl" var_name="fb_files[`$element.element_id`]"}
			{/if}

			{hook name="pages:form_elements"}
			{/hook}
		</div>
	</div>
{/if}
{/foreach}