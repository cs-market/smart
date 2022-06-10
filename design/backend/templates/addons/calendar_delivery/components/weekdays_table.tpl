{capture name="content"}
{$weekdays = [1,2,3,4,5,6,0]}
{if $value|is_string}
    {$value = $value|fn_delivery_date_from_line}
{/if}
<input type="hidden" name="{$name}" value="0000000">
<table class="table table-middle" style="max-width: 300px;">
    <thead class="cm-first-sibling">
        <tr>
            {capture name="head"}
                {foreach from=$weekdays item="day"}
                <th width="14%">
                    {__("weekday_abr_`$day`")}
                </th>
                {/foreach}
            {/capture}
            {$smarty.capture.head nofilter}
        </tr>
    </thead>
    <tbody class="">
        <tr class="cm-row-item">
            {capture name="body"}
            {foreach from=$weekdays item="day"}
            <td width="14%">
                <input type="checkbox" name="{$name}[]" value="{$day}" {if $day|in_array:$value}checked="_checked"{/if}>
            </td>
            {/foreach}
            {/capture}
            {$smarty.capture.body nofilter}
        </tr>
    </tbody>
</table>
{/capture}
{if $only_head}
    {$smarty.capture.head nofilter}
{elseif $only_body}
    {$smarty.capture.body nofilter}
{else}
    {$smarty.capture.content nofilter}
{/if}
