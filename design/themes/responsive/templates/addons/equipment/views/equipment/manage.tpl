{include file="common/subheader.tpl" title=__("equipment.my_equipment") class="ty-mt-l"}
<table class="ty-table ty-equipment__table">
    <thead>
        <tr>
            <th width="15%">{__("name")}</th>
            <th width="15%">{__("equipment.inventory_number")}</th>
            <th width="15%">{__("equipment.serial_number")}</th>
            <th width="15%">{__("status")}</th>

            {hook name="equipment:manage_header"}{/hook}

            <th class="ty-orders-search__header--actions">{__("actions")}</th>
        </tr>
    </thead>
    {foreach from=$equipment item="e" key="equipment_id"}
        <tr>
            <td><strong>{$e.name}</strong></td>
            <td>{$e.inventory_number}</td>
            <td>{$e.serial_number}</td>
            <td>{$e.status}</td>
            {hook name="equipment:manage_data"}{/hook}
            <td class="ty-equipment__item ty-equipment__item--actions">
                {if $e.is_new_repair_allowed}
                {include file="common/popupbox.tpl"
                    href="equipment.add_repair_request&equipment_id=`$equipment_id`"
                    link_text=__('equipment.claim_repair')
                    link_text_meta="ty-btn ty-btn__secondary"
                    text=__('equipment.repair_request')
                    id="repair_dialog_{$equipment_id}"
                    content=""
                }
                {/if}
            </td>
        </tr>
    {foreachelse}
        <tr class="ty-table__no-items">
            <td colspan="6"><p class="ty-no-items">{__("text_no_items")}</p></td>
        </tr>
    {/foreach}
</table>
{include file="common/subheader.tpl" title=__("equipment.repair_requests") class="ty-mt-l"}
{if $repairs}
<table class="ty-table ty-equipment__table">
    <thead>
        <tr>
            <th>{__("name")}</th>
            <th>{__("date")}</th>
            <th>{__("equipment.malfunctions")}</th>
            <th>{__("comment")}</th>
            <th>{__("status")}</th>
            <th class="ty-orders-search__header--actions">{__("actions")}</th>
        </tr>
    </thead>
{foreach from=$repairs item="r"}
    <tr>
        <td>
            {$equipment_id = $r.equipment_id}
            <span>{$equipment.$equipment_id.name}</span> <span class="muted">{$equipment.$equipment_id.inventory_number}</span>
        </td>
        <td>{$r.timestamp|date_format:"`$settings.Appearance.date_format`"}</td>
        <td>
            {if $r.malfunctions}
                {foreach from=$r.malfunctions item="malfunction"}
                    {$type = $malfunction.type}
                    <div>{$malfunction_types.$type.description}</div>
                {/foreach}
            {/if}
        </td>
        <td>{$r.comment}</td>
        <td>
            {$r.status}
        </td>
        <td>
            {if $r.is_editable}
                {include file="common/popupbox.tpl"
                    href="equipment.update_repair_request&request_id=`$r.request_id`"
                    link_meta="ty-btn ty-btn__secondary"
                    text=__('equipment.repair_request')
                    id="repair_dialog_{$equipment_id}_{$r.request_id}"
                    link_icon="ty-icon-edit"
                    link_icon_first=true
                    content=""
                }
                {include file="buttons/button.tpl" but_href="equipment.cancel_repair_request&request_id=`$r.request_id`" but_meta="ty-btn__secondary cm-post" but_icon="ty-icon-cancel"}
            {/if}
        </td>
    </tr>
{/foreach}
</table>
{/if}
