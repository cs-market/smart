<div class="control-group">
    <label class="control-label" for="elm_use_avail_period">{__("use_avail_period")}:</label>
    <div class="controls">
        <input type="checkbox" name="avail_period" id="elm_use_avail_period" {if $banner.from_date || $banner.to_date}checked="checked"{/if} value="Y" onclick="fn_activate_calendar(this);"/>
    </div>
</div>

{capture name="calendar_disable"}{if !$banner.from_date && !$banner.to_date}disabled="disabled"{/if}{/capture}
<div class="control-group">
    <label class="control-label" for="elm_date_holder_from">{__("avail_from")}:</label>
    <div class="controls">
    {if $banner.from_date}
        {$from_date = $banner.from_date}
    {else}
        {$from_date = $smarty.const.TIME}
    {/if}

    <input type="hidden" name="banner_data[from_date]" value="0" />
    {include file="common/calendar.tpl" date_id="elm_date_holder_from" date_name="banner_data[from_date]" date_val=$from_date start_year=$settings.Company.company_start_year extra=$smarty.capture.calendar_disable} <input id="elm_date_holder_from_hours" name="banner_data[from_hours]" type="text" class="input-micro" value="{'H'|date:$from_date}" {$smarty.capture.calendar_disable} />:<input id="elm_date_holder_from_minutes" name="banner_data[from_minutes]" type="text" class="input-micro" value="{'i'|date:$from_date}" {$smarty.capture.calendar_disable} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_date_holder_to">{__("avail_till")}:</label>
    <div class="controls">
    {if $banner.to_date}
        {$to_date = $banner.to_date}
    {else}
        {$to_date = $smarty.const.TIME}
    {/if}

    <input type="hidden" name="banner_data[to_date]" value="0" />
    {include file="common/calendar.tpl" date_id="elm_date_holder_to" date_name="banner_data[to_date]" date_val=$to_date|default:$smarty.const.TIME start_year=$settings.Company.company_start_year extra=$smarty.capture.calendar_disable} <input id="elm_date_holder_to_hours" name="banner_data[to_hours]" type="text" class="input-micro" value="{'H'|date:$to_date}" {$smarty.capture.calendar_disable}} />:<input id="elm_date_holder_to_minutes" name="banner_data[to_minutes]" type="text" class="input-micro" value="{'i'|date:$to_date}" {$smarty.capture.calendar_disable} />
    </div>
</div>

{if !"ULTIMATE:FREE"|fn_allowed_for}
    <div class="control-group">
        <label class="control-label">{__("usergroups")}:</label>
        <div class="controls">
            {include file="common/select_usergroups.tpl" id="ug_id" name="banner_data[usergroup_ids]" usergroups=["type"=>"C", "status"=>["A", "H"]]|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$banner.usergroup_ids input_extra="" list_mode=false}
        </div>
    </div>
{/if}

<script language="javascript">
function fn_activate_calendar(el)
{
    var $ = Tygh.$;
    var jelm = $(el);
    var checked = jelm.prop('checked');

    $('#elm_date_holder_from,#elm_date_holder_to,#elm_date_holder_from_hours,#elm_date_holder_from_minutes,#elm_date_holder_to_hours,#elm_date_holder_to_minutes').prop('disabled', !checked);
}

fn_activate_calendar(Tygh.$('#elm_use_avail_period'));
</script>