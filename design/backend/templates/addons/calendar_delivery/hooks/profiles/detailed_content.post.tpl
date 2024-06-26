{include file="common/subheader.tpl" title=__("delivery_date")}

{include file="addons/calendar_delivery/components/nearest_delivery.tpl" id='user_nearest_delivery' name='user_data[nearest_delivery]' params=$user_data}

<div class="control-group">
    <label class="control-label" for="elm_user_data_ignore_other_nearest_delivery">{__("calendar_delivery.ignore_other_nearest_delivery")}</label>
    <div class="controls">
        <input type="hidden" name="user_data[ignore_other_nearest_delivery]" value="{"YesNo::NO"|enum}">
        <input type="checkbox" name="user_data[ignore_other_nearest_delivery]" id="elm_user_data_ignore_other_nearest_delivery" value="{"YesNo::YES"|enum}" {if $user_data.ignore_other_nearest_delivery == "YesNo::YES"|enum} checked="checked" {/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="delivery_date">{__("delivery_date")}</label>
    <div class="controls">
        {include file="addons/calendar_delivery/components/weekdays_table.tpl" name="user_data[delivery_date]" value=$user_data.delivery_date|default:"1111111"}
    </div>
</div>

<div class="control-group">
    <label for="elm_user_data_monday_rule" class="control-label">{__("calendar_delivery.monday_rule")}:</label>
    <div class="controls">
        <input type="hidden" name="user_data[monday_rule]" value="N">
        <input type="checkbox" name="user_data[monday_rule]" id="elm_user_data_monday_rule" value="{"YesNo::YES"|enum}" {if $user_data.monday_rule != "YesNo::NO"|enum} checked="checked" {/if} />
    </div>
</div>

{if $storages}
<div class="control-group">
    <label class="control-label" for="ignore_exception_days">{__("calendar_delivery.ignore_exception_days")}</label>
    <input type="hidden" name="user_data[ignore_exception_days]" value="{"YesNo::NO"|enum}">
    <div class="controls">
        <input id="ignore_exception_days" type="checkbox" name="user_data[ignore_exception_days]" value="{"YesNo::YES"|enum}" {if $user_data.ignore_exception_days == "YesNo::YES"|enum}checked="_checked"{/if}>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="delivery_date_by_storage">{__("delivery_date_by_storage")}</label>
    <div class="controls">
        <table class="table table-middle">
            <thead class="cm-first-sibling">
                <tr>
                    <th>{__('storages.storage')}</th>
                    {include file="addons/calendar_delivery/components/weekdays_table.tpl" only_head=true}
                    <th></th>
                </tr>
            </thead>
            <tbody class="">
                {foreach from=$user_data.delivery_date_by_storage item="user_storage" name="user_storage"}
                {assign var="num" value=$smarty.foreach.user_storage.iteration}
                <tr class="cm-row-item">
                    <td style="white-space: nowrap;">
                        <select name="user_data[delivery_date_by_storage][{$num}][storage_id]">
                            <option value="">---</option>
                            {foreach from=$storages item="storage"}
                            <option value="{$storage.storage_id}" {if $storage.storage_id == $user_storage.storage_id}selected="_selected"{/if}>{$storage.storage} ({$storage.code})</option>
                            {/foreach}
                        </select>
                        <a href="{"storages.manage&storage_id=`$user_storage.storage_id`#group`$user_storage.storage_id`"|fn_url}" target="_blank"><i class="icon-external-link"></i></a>
                    </td>
                    {include file="addons/calendar_delivery/components/weekdays_table.tpl" name="user_data[delivery_date_by_storage][`$num`][delivery_date]" value=$user_storage['delivery_date'] only_body=true}
                    <td>{include file="buttons/multiple_buttons.tpl" only_delete="Y"}</td>
                </tr>
                {/foreach}
                {math equation="x + 1" assign="num" x=$num|default:0}
                <tr id="box_add_variant_{$num}">
                    <td>
                        <select name="user_data[delivery_date_by_storage][{$num}][storage_id]">
                            <option value="">---</option>
                            {foreach from=$storages item="storage"}
                            <option value="{$storage.storage_id}" >{$storage.storage} ({$storage.code})</option>
                            {/foreach}
                        </select>
                    </td>
                    {include file="addons/calendar_delivery/components/weekdays_table.tpl" name="user_data[delivery_date_by_storage][{$num}][delivery_date]" only_body=true}
                    <td>
                        {include file="buttons/multiple_buttons.tpl" item_id="add_variant_`$num`" tag_level="1"}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
{/if}
