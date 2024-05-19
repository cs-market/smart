{assign var="e_id" value=$repair.equipment_id|default:$smarty.request.equipment_id|default:0}
{assign var="r_id" value=$repair.request_id|default:0}

<div class="ty-repair-request">
    <form name="repair_request_form_{$e_id}" action="{""|fn_url}" method="post">
        <input type="hidden" name="return_url" value="{"equipment.manage"|fn_url}" />
        <input type="hidden" name="request_id" value="{$r_id}" />

        <div class="ty-control-group">
            <label for="equipment_name_{$r_id}{$e_id}" class="ty-control-group__title">{__("name")}</label>
            <input type="hidden" name="request_data[equipment_id]" value="{$e_id}" />
            <span><b>{$equipment.name}</b></span>
        </div>

        <fieldset>
            <legend>{__('equipment.malfunctions')}</legend>
            <div class="malfunctions-container">
            {if $malfunction_types}
                {if $repair.malfunctions}
                    {foreach from=$repair.malfunctions item='malfunction' name='malfunction'}
                        {$tooltip = ""}
                        {$key = $smarty.foreach.malfunction.iteration - 1}
                        <div id="box_add_malfunction_{$r_id}_{$e_id}_{$key}" class="ty-control-group">
                            <label for="malfunction_type_{$r_id}_{$e_id}_{$key}" class="ty-control-group__title">{__("equipment.malfunction_type")}</label>
                            <select id="malfunction_type_{$r_id}_{$e_id}_{$key}" name="request_data[malfunctions][{$key}][type]" onchange="Tygh.$('#tooltip_' + this.id).text(Tygh.$(this).find(':selected').data('caTooltip'));">
                                <option value="" disabled="_disabled" selected="_selected">{__('choose')}</option>
                                {foreach from=$malfunction_types key='code' item="malfunction_type"}
                                    
                                    <option value="{$code}" {if $code == $malfunction.type} selected="_selected" {$tooltip = $malfunction_type.repair_tooltip}{/if} data-ca-tooltip="{$malfunction_type.repair_tooltip nofilter}">{$malfunction_type.description}</option>
                                {/foreach}
                            </select>
                            <a class="ty-icon ty-icon-cancel-circle" title="Удалить" onclick="Tygh.$(this).parent().remove();"></a>
                            <span id="tooltip_malfunction_type_{$r_id}_{$e_id}_{$key}" class="malfunction-tooltip">{$tooltip nofilter}</span>
                        </div>
                    {/foreach}
                {else}

                {/if}
            {/if}
            <div id="box_add_malfunction_new_{$r_id}_{$e_id}" class="hidden">
                <div class="ty-control-group">
                    <label for="malfunction_type_{$r_id}_{$e_id}_0" class="ty-control-group__title">{__("equipment.malfunction_type")}</label>
                    <select id="malfunction_type_{$r_id}_{$e_id}_0" name="request_data[malfunctions][0][type]" onchange="Tygh.$('#tooltip_' + this.id).text(Tygh.$(this).find(':selected').data('caTooltip'));">
                        <option value="" disabled="_disabled" selected="_selected">{__('choose')}</option>
                        {foreach from=$malfunction_types key='code' item="malfunction_type"}
                            <option value="{$code}" data-ca-tooltip="{$malfunction_type.repair_tooltip nofilter}">{$malfunction_type.description}</option>
                        {/foreach}
                    </select>
                    <a class="ty-icon ty-icon-cancel-circle" title="Удалить" onclick="Tygh.$(this).parent().remove();"></a>
                    <span id="tooltip_malfunction_type_{$r_id}_{$e_id}_0" style="color: red;"></span>
                </div>
            </div>
            </div>
            <script type="text/javascript" data-no-defer>
                function fn_add_new_malfunction() {
                    $length = $('.malfunctions-container > div').length;
                    $new = Tygh.$('#box_add_malfunction_new_{$r_id}_{$e_id}').cloneNode(2, false, true); 
                    $('#' + $new).removeClass('hidden'); 
                }
            </script>
            {* <div class="ty-control-group">
                <label for="malfunction_comment_0" class="ty-control-group__title">{__("equipment.malfunction_comment")}</label>
                <textarea id="equipment_comment_0" name="request_data[malfunctions][0][comment]" class="ty-input-textarea cm-focus" autofocus rows="5" cols="69"></textarea>
            </div> *}
            {script src="js/tygh/node_cloning.js"}
            {include file="buttons/button.tpl" but_onclick="fn_add_new_malfunction(); Tygh.$.ceDialog('get_last').ceDialog('reload'); return false;" but_meta="ty-btn__secondary ty-float-right" but_text=__('equipment.add_more')}
        </fieldset>

        <div class="ty-control-group">
            <label for="equipment_comment_{$r_id}_{$e_id}" class="ty-control-group__title">{__("equipment.common_comment")}</label>
            <textarea id="equipment_comment_{$r_id}_{$e_id}" name="request_data[comment]" class="ty-input-textarea cm-focus cm-required" autofocus rows="5" cols="72">{$repair.comment}</textarea>
        </div>

        <div class="buttons-container">
            {include file="buttons/button.tpl" but_text=__("send") but_meta="ty-btn__primary cm-post cm-reset-link" but_role="submit" but_name="dispatch[equipment.add_repair_request]"}
        </div>
    </form>
</div>

{capture name="mainbox_title"}{__("equipment.repair_request")}{/capture}

