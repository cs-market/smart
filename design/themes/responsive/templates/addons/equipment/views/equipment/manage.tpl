<table class="ty-table">
    <thead>
        <tr>
            <th>{__("name")}</th>
            <th>{__("product_code")}</th>
            <th>{__("equipment.inventory_number")}</th>
            <th>{__("equipment.serial_number")}</th>
            <th>{__("status")}</th>

            {hook name="equipment:manage_header"}{/hook}

            <th class="ty-orders-search__header--actions">{__("actions")}</th>
        </tr>
    </thead>
    {foreach from=$equipment item="e" key="equipment_id"}
        <tr>
            <td><strong>{$e.name}</strong></td>
            <td>{$e.product_code}</td>
            <td>{$e.inventory_number}</td>
            <td>{$e.serial_number}</td>
            <td>{$e.status}</td>
            {hook name="equipment:manage_data"}{/hook}
            <td class="ty-equipment__item ty-equipment__item--actions">
                {if $e.repairs}
                    <a class="ty-btn ty-btn__secondary ty-mb-s cm-combination {if $smarty.request.equipment_id}hidden{/if}" id="on_repairs_{$equipment_id}">{__('equipment.show_repairs')}</a>
                    <a class="ty-btn ty-btn__secondary ty-mb-s cm-combination {if !$smarty.request.equipment_id}hidden{/if}" id="off_repairs_{$equipment_id}">{__('equipment.hide_repairs')}</a>
                {/if}
                    {include file="common/popupbox.tpl"
                        href="equipment.add_repair_request&equipment_id=`$equipment_id`"
                        link_text=__('equipment.claim_repair')
                        link_text_meta="ty-btn ty-btn__secondary"
                        text=__('equipment.repair_request')
                        id="repair_dialog_{$equipment_id}"
                        content=""
                    }
            </td>
        </tr>
        {if $e.repairs}
            <tr id="repairs_{$e.equipment_id}" class='{if !$smarty.request.equipment_id}hidden{/if}'>
                <td colspan="6">
                    <table class="ty-table">
                        <thead>
                            <tr>
                                <th>{__("date")}</th>
                                <th>{__("equipment.malfunctions")}</th>
                                <th>{__("comment")}</th>
                                <th>{__("status")}</th>
                            </tr>
                        </thead>
                    {foreach from=$e.repairs item="r"}
                        <tr>
                            <td>{$r.timestamp|date_format:"`$settings.Appearance.date_format`"}</td>
                            <td>
                                {if $r.malfunctions}
                                    {foreach from=$r.malfunctions item="malfunction"}
                                        {$type = $malfunction.type}
                                        {$malfunction_types.$type}: {$malfunction.comment nofilter}
                                    {/foreach}
                                {/if}
                            </td>
                            <td>{$r.comment}</td>
                            <td>{$r.status}</td>
                        </tr>
                    {/foreach}
                    </table>
                </td>
            </tr>
        {/if}
    {foreachelse}
        <tr class="ty-table__no-items">
            <td colspan="6"><p class="ty-no-items">{__("text_no_items")}</p></td>
        </tr>
    {/foreach}
</table>
