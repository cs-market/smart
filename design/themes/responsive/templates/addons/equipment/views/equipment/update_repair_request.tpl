{assign var="id" value=$smarty.request.equipment_id}

<div class="ty-repair-request">
    <form name="repair_request_form_{$id}" action="{""|fn_url}" method="post">
        <input type="hidden" name="return_url" value="{"equipment.manage"|fn_url}" />

        <div class="ty-control-group">
            <label for="equipment_name_{$id}" class="ty-control-group__title">{__("name")}</label>
            <input type="hidden" name="request_data[equipment_id]" value="{$id}" />
            <span><b>{$equipment.name}</b></span>
        </div>

        <fieldset>
            <legend>{__('equipment.malfunctions')}</legend>
            <div id="box_add_malfunction_0">
                {if $malfunction_types}
                <div class="ty-control-group">
                    <label for="malfunction_type_0" class="ty-control-group__title">{__("equipment.malfunction_type")}</label>
                    <select id="malfunction_type_0" name="request_data[malfunctions][0][type]">
                        <option value="" disabled="_disabled" selected="_selected">{__('choose')}</option>
                        {foreach from=$malfunction_types key='code' item="malfunction"}
                            <option value="{$code}">{$malfunction}</option>
                        {/foreach}
                    </select>
                </div>
                {/if}

                <div class="ty-control-group">
                    <label for="malfunction_comment_0" class="ty-control-group__title">{__("equipment.malfunction_comment")}</label>
                    <textarea id="equipment_comment_0" name="request_data[malfunctions][0][comment]" class="ty-input-textarea cm-focus" autofocus rows="5" cols="69"></textarea>
                </div>
            </div>
            {script src="js/tygh/node_cloning.js"}
            {include file="buttons/button.tpl" but_onclick="Tygh.$('#box_add_malfunction_0').cloneNode(2); Tygh.$.ceDialog('get_last').ceDialog('reload');" but_meta="ty-btn__secondary ty-float-right" but_text=__('equipment.add_more')}
        </fieldset>

        <div class="ty-control-group">
            <label for="equipment_comment_{$id}" class="ty-control-group__title">{__("equipment.common_comment")}</label>
            <textarea id="equipment_comment_{$id}" name="request_data[comment]" class="ty-input-textarea cm-focus cm-required" autofocus rows="5" cols="72"></textarea>
        </div>

        <div class="buttons-container">
            {include file="buttons/button.tpl" but_text=__("send") but_meta="ty-btn__primary cm-post cm-reset-link" but_role="submit" but_name="dispatch[equipment.add_repair_request]"}
        </div>
    </form>
</div>

{capture name="mainbox_title"}{__("equipment.repair_request")}{/capture}

