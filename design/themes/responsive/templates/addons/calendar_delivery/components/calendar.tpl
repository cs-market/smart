{if $settings.Appearance.calendar_date_format == "month_first"}
    {assign var="date_format" value="%m/%d/%Y"}
{else}
    {assign var="date_format" value="%d/%m/%Y"}
{/if}

<div class="ty-calendar__block">
    <input type="text" id="{$date_id}" name="{$date_name}" class="ty-calendar__input{if $date_meta} {$date_meta}{/if} cm-calendar" value="{if $date_val}{$date_val|date_format:"`$date_format`"}{/if}" {$extra} size="10" data-ca-calendar-delivery-auto-save-on-change="true" data-ca-calendar-delivery-field="{$date_name}"/>
    <a class="cm-external-focus ty-calendar__link" data-ca-external-focus-id="{$date_id}">
        <i class="ty-icon-calendar ty-calendar__button" title="{__("calendar")}"></i>
    </a>
</div>

<script type="text/javascript">
(function(_, $) {$ldelim}
    $.ceEvent('on', 'ce.commoninit', function(context) {
        current_date = new Date();
        unix_offset = current_date.getTime();
        utc_offset = current_date.getTimezoneOffset() / 60;
        moscow_offset = 3;
        delivery_offset = {$min_date|default:0} * 24;
        min_date = new Date( unix_offset + (utc_offset + moscow_offset + delivery_offset) * 3600 * 1000 );
        $('#{$date_id}').datepicker({
            changeMonth: true,
            duration: 'fast',
            changeYear: true,
            numberOfMonths: 1,
            selectOtherMonths: true,
            showOtherMonths: true,
            beforeShowDay: function(date) {
                res = true;
                {if $service_params.weekdays_availability}
                    weekdays_availability = parseInt('{$service_params.weekdays_availability}', 2);
                    date_bin = 1 << date.getDay();
                    res = weekdays_availability & date_bin;
                {/if}
                {if $service_params.holidays}
                    holidays = {$service_params.holidays|json_encode nofilter};
                    dateFormat = '{if $settings.Appearance.calendar_date_format == "month_first"}mm/dd/yy{else}dd/mm/yy{/if}';
                    formated = $.datepicker.formatDate(dateFormat, date);
                    res = res && !holidays.includes(formated);
                {/if}
                return [res];
            },
            firstDay: {if $settings.Appearance.calendar_week_format == "sunday_first"}0{else}1{/if},
            dayNamesMin: ['{__("weekday_abr_0")}', '{__("weekday_abr_1")}', '{__("weekday_abr_2")}', '{__("weekday_abr_3")}', '{__("weekday_abr_4")}', '{__("weekday_abr_5")}', '{__("weekday_abr_6")}'],
            monthNamesShort: ['{__("month_name_abr_1")|escape:"html"}', '{__("month_name_abr_2")|escape:"html"}', '{__("month_name_abr_3")|escape:"html"}', '{__("month_name_abr_4")|escape:"html"}', '{__("month_name_abr_5")|escape:"html"}', '{__("month_name_abr_6")|escape:"html"}', '{__("month_name_abr_7")|escape:"html"}', '{__("month_name_abr_8")|escape:"html"}', '{__("month_name_abr_9")|escape:"html"}', '{__("month_name_abr_10")|escape:"html"}', '{__("month_name_abr_11")|escape:"html"}', '{__("month_name_abr_12")|escape:"html"}'],
            yearRange: '{if $start_year}{$start_year}{else}c-100{/if}:c+10',
            minDate: min_date,
            {if $max_date || $max_date === 0}maxDate: {$max_date},{/if}
            dateFormat: '{if $settings.Appearance.calendar_date_format == "month_first"}mm/dd/yy{else}dd/mm/yy{/if}'
        });
    });
{$rdelim}(Tygh, Tygh.$));
</script>
