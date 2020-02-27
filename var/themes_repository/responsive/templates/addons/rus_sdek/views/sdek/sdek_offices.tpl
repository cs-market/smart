<div class="ty-sdek-checkout-select-office" id="sdek_offices">
{assign var="count" value="1"}
{foreach from=$sdek_offices item=office name=item}
    {if $count == 1}
    <div class="clearfix">
    {/if}
    <div class="ty-sdek-office{if $old_office_id == $office.Code || $office_count == 1} ty-sdek-office__selected{/if}" id="sdek_office">
        <input type="radio" name="select_office[{$group_key}][{$shipping_id}]" value="{$office.Code}" {if $old_office_id == $office.Code || $office_count == 1}checked="checked"{/if} id="office_{$group_key}_{$shipping_id}_{$office.Code}" class="cm-sdek-select-store ty-sdek-office__radio-{$group_key} ty-valign">
        <div class="ty-sdek-store__label">
            <a data-ca-scroll="sdek_map_{$group_key}" data-ca-latitude="{$office.lat}" data-ca-longitude="{$office.lng}" data-ca-group-key="{$group_key}" class="cm-sdek-view-location ty-sdek-icon-location"></a>
            <label for="office_{$group_key}_{$shipping_id}_{$office.Code}" class="ty-one-office__name">
                {$office.Name}<i class="ty-sdek-name-office-{$group_key}-{$office.Code} ty-icon-ok {if $old_office_id != $office.Code}ty-sdek-office-point-disabled{/if}"></i>
                <div>
                    {$office.City}, {$office.Address}
                    <br/>
                    {if $office.Phone}{__("phone")}: {$office.Phone}</br>{/if}
                    {$office.WorkTime}<br />
                    {$office.Note}
                </div>
            </label>
        </div>
    </div>
    {assign var="count" value=$count+1}
    {if $count > 3}
    </div>
    {assign var="count" value="1"}
    {/if}
{/foreach}
<!--sdek_offices--></div>
