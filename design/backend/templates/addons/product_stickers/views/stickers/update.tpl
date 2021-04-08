{if $sticker_data.sticker_id}
    {assign var="id" value=$sticker_data.sticker_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}
{$graphic = true}
{if $id && $sticker_data.type == 'T'}
    {$graphic = false}
{/if}
{if $sticker_data.sticker_id}
    {assign var="id" value=$sticker_data.sticker_id}
{else}
    {assign var="id" value=0}
{/if}
{if $id && "MULTIVENDOR"|fn_allowed_for && $runtime.company_id && $runtime.company_id != $sticker_data.company_id}
    {$hide = true}
{/if}
<form action="{""|fn_url}" method="post" name="sticker_update_form" class="form-horizontal form-edit {if $hide}cm-hide-inputs{/if}" enctype="multipart/form-data">

<div id="update_sticker_form_{$sticker_data.sticker_id}">
    <input type="hidden" class="cm-no-hide-input" id="selected_section" name="selected_section" value="{$selected_section}"/>
    <input type="hidden" class="cm-no-hide-input" id="sticker_id" name="sticker_id" value="{$id}" />
    <input type="hidden" class="cm-no-hide-input" name="come_from" value="{$come_from}" />
    <input type="hidden" class="cm-no-hide-input" name="result_ids" value="update_sticker_form_{$sticker_data.sticker_id}"/>

    <div id="content_basic">

    {include file="common/subheader.tpl" title=__("information") target="#stickers_information_setting"}
    <div id="stickers_information_setting" class="in collapse">
    <fieldset>
        <div class="control-group">
            <label for="elm_sticker_name" class="control-label cm-required">{__("text_sticker_name")}:</label>
            <div class="controls">
                <input type="text" name="sticker_data[name]" id="elm_sticker_name" size="55" value="{$sticker_data.name}" class="input-text-short" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_sticker_position">{__("position")}:</label>
            <div class="controls">
                <select id="elm_sticker_position" name="sticker_data[position]">
                    <option {if $sticker_data.position == 'A'} selected="selected" {/if} value="A" >{__("top_left")}</option>
                    <option {if $sticker_data.position == 'B'} selected="selected" {/if} value="B" >{__("top_center")}</option>
                    <option {if $sticker_data.position == 'C'} selected="selected" {/if} value="C" >{__("top_right")}</option>

                    <option {if $sticker_data.position == 'G'} selected="selected" {/if} value="G" >{__("middle_left")}</option>
                    <option {if $sticker_data.position == 'H'} selected="selected" {/if} value="H" >{__("middle_center")}</option>
                    <option {if $sticker_data.position == 'I'} selected="selected" {/if} value="I" >{__("middle_right")}</option>

                    <option {if $sticker_data.position == 'D'} selected="selected" {/if} value="D" >{__("bottom_left")}</option>
                    <option {if $sticker_data.position == 'E'} selected="selected" {/if} value="E" >{__("bottom_center")}</option>
                    <option {if $sticker_data.position == 'F'} selected="selected" {/if} value="F" >{__("bottom_right")}</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_sticker_type">{__("type")}:</label>
            <div class="controls">
                <select id="elm_sticker_type" name="sticker_data[type]" onchange="Tygh.$('.sticker_graphic').toggle();  Tygh.$('.sticker_text').toggle(); Tygh.$('#banner_url').toggle();  Tygh.$('#banner_target').toggle();">
                    <option {if $sticker_data.type == 'G'} selected="selected" {/if} value="G" >{__("image")}</option>
                    <option {if $sticker_data.type == 'T'} selected="selected" {/if} value="T" >{__("text")}</option>
                </select>
            </div>
        </div>
        <div class="sticker_graphic" {if !$graphic}style="display: none;"{/if}>
            <div class="control-group">
                <label class="control-label">{__("images")}:</label>
                <div class="controls">
                    {include file="common/attach_images.tpl" image_name="stickers_main" image_object_type="sticker" image_pair=$sticker_data.main_pair image_object_id=$id no_detailed=true hide_titles=true}
                </div>
            </div>
        </div>
        <div class="sticker_text clearfix" {if $graphic}style="display: none;"{/if}>
            {assign var="prefix_md5" value='property'|md5}
            {script src="js/tygh/node_cloning.js"}
            <div class="control-group">
                <label class="control-label" for="elm_sticker_text">{__("text")}:</label>
                <div class="controls">
                    <textarea id="elm_sticker_text" name="sticker_data[text]" cols="55" rows="8" class="input-large {if $addons.product_stickers.wysiwyg_editor} cm-wysiwyg {/if} input-textarea-short">{$sticker_data.text}</textarea>
                </div>
            </div>
            <script type="text/javascript">
                function fn_add_css_property(id, skip_select) {
                    var $ = Tygh.$,
                    new_id = $('#container_' + id).cloneNode(2, true, true).str_replace('container_', ''),
                    $new_container = $('#container_' + new_id);
                    $new_container.removeClass('hidden');
                    $('select', $new_container).prop('id', new_id);
                }
            </script>
            <label class="control-label">{__("stickers.css_properties")} {include file="common/tools.tpl" hide_tools=true tool_onclick="fn_add_css_property('add_css_property_{$prefix_md5}', false, 'property');" prefix="simple" link_text=""}:</label>
            <div class="controls">
                {if $sticker_data.properties}
                    {foreach from=$sticker_data.properties item='value' key='property' name='styles'}
                        {include file="addons/product_stickers/views/stickers/dynamic_style.tpl" property=$schema.$property value=$value}
                    {/foreach}
                {/if}
                <div id="container_add_css_property_{$prefix_md5}" class="hidden cm-row-item">
                    <div class="conditions-tree-node">
                    <select onchange="Tygh.$.ceAjax('request', '{"stickers.dynamic_style"|fn_url nofilter}&property=' + this.value + '&elm_id=' + this.id, {$ldelim}result_ids: 'container_' + this.id, force_exec: true{$rdelim})">
                        <option value=""> -- </option>
                        {foreach from=$schema key="name" item="prop"}
                                <option value="{$name}">{$prop.label}</option>
                        {/foreach}
                    </select>
                    </div>
                </div>
            </div>
        </div>
        {if "MULTIVENDOR"|fn_allowed_for}
            {include file="views/companies/components/company_field.tpl"
                name="sticker_data[company_id]"
                id="sticker_data_company_id"
                selected=$sticker_data.company_id
                zero_company_id_name_lang_var='all_vendors'
            }
        {/if}
        <div class="control-group">
            <label for="elm_sticker_display" class="control-label">{__("display_on")}:</label>
            <div class="controls">
                <label class="checkbox inline" for="sticker_display_p"><input type="checkbox" {if $sticker_data.display|strpos:'P' !== false} checked="checked" {/if} name="sticker_data[display][]" id="sticker_display_p" value="P">{__('stickers.product_page')}</label>
                <label class="checkbox inline" for="sticker_display_c"><input type="checkbox" {if $sticker_data.display|strpos:'C' !== false} checked="checked" {/if} name="sticker_data[display][]" id="sticker_display_c" value="C">{__('stickers.category_page')}</label>
                <label class="checkbox inline" for="sticker_display_b"><input type="checkbox" {if $sticker_data.display|strpos:'B' !== false} checked="checked" {/if} name="sticker_data[display][]" id="sticker_display_b" value="B">{__('stickers.blocks')}</label>
            </div>
        </div>
        <div class="control-group">
            <label for="elm_sticker_class" class="control-label">{__("user_class")}:</label>
            <div class="controls">
                <input type="text" name="sticker_data[class]" id="elm_sticker_class" size="55" value="{$sticker_data.class}" class="input-text" />
            </div>
        </div>
        {capture name="calendar_disable"}{if $sticker_data.use_avail_period != "Y"}disabled="disabled"{/if}{/capture}
        <div class="control-group">
            <label class="control-label" for="elm_page_use_avail_period">{__("use_avail_period")}:</label>
            <div class="controls">
                <input type="hidden" name="sticker_data[use_avail_period]" value="N">
                <span class="checkbox">
                    <input type="checkbox" name="sticker_data[use_avail_period]" id="elm_page_use_avail_period" {if $sticker_data.use_avail_period == "Y"}checked="checked"{/if} value="Y"  onclick="fn_activate_calendar(this);">
                </span>
            </div>
        </div>
        <div class="control-group">
            <label for="elm_sticker_avail_from" class="control-label">{__("avail_from")}:</label>
            <div class="controls">
                {include file="common/calendar.tpl" date_id="elm_sticker_avail_from" date_name="sticker_data[avail_from_timestamp]" date_val=$sticker_data.avail_from_timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year extra=$smarty.capture.calendar_disable}
            </div>
        </div>
        <div class="control-group">
            <label for="elm_sticker_avail_till" class="control-label">{__("avail_till")}:</label>
            <div class="controls">
                {include file="common/calendar.tpl" date_id="elm_sticker_avail_till" date_name="sticker_data[avail_till_timestamp]" date_val=$sticker_data.avail_till_timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year extra=$smarty.capture.calendar_disable}
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{__("usergroups")}:</label>
            <div class="controls">
                {include file="common/select_usergroups.tpl" id="ug_id" name="sticker_data[usergroup_ids]" usergroups=["type"=>"C", "status"=>["A", "H"]]|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$sticker_data.usergroup_ids input_extra="" list_mode=false}
            </div>
        </div>
        {include file="common/select_status.tpl" input_name="sticker_data[status]" id="elm_page_status" obj=$sticker_data hidden=false}
    </fieldset>
    </div>
        {literal}
            <script language="javascript">
                function fn_activate_calendar(el)
                {
                    Tygh.$('#elm_sticker_avail_from').prop('disabled', !el.checked);
                    Tygh.$('#elm_sticker_avail_till').prop('disabled', !el.checked);
                }
            </script>
        {/literal}
    {capture name="buttons"}
    {if !$hide}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[stickers.update]" hide_first_button=$hide_first_button hide_second_button=$hide_second_button but_target_form="sticker_update_form" save=$id}
    {/if}
    {/capture}

    {if !$id}
        {assign var="_title" value=__('new_sticker')}
    {else}
        {assign var="_title" value="{__('editing_sticker')}: `$sticker_data.name`"}
    {/if}
<!--update_sticker_form_{$sticker_data.sticker_id}--></div>
</form>

{/capture}

{include file="common/mainbox.tpl" title=$_title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}
