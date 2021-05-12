{if $settings.Appearance.calendar_date_format == "month_first"}
    {assign var="date_format" value="%m/%d/%Y"}
{else}
    {assign var="date_format" value="%d/%m/%Y"}
{/if}

<div class="ty-calendar__block">
    <input type="text" id="{$date_id}" name="{$date_name}" class="ty-calendar__input{if $date_meta} {$date_meta}{/if} cm-calendar" value="{if $date_val}{$date_val|date_format:"`$date_format`"}{/if}" {$extra} size="10" data-ca-lite-checkout-auto-save-on-change="true" data-ca-lite-checkout-field="{$date_name}"/>
    <a class="cm-external-focus ty-calendar__link" data-ca-external-focus-id="{$date_id}">
        <i class="ty-icon-calendar ty-calendar__button" title="{__("calendar")}"></i>
    </a>
</div>

<script type="text/javascript">
(function(_, $) {$ldelim}
    $.ceEvent('on', 'ce.commoninit', function(context) {

        $('#{$date_id}').datepicker({
            changeMonth: true,
            duration: 'fast',
            changeYear: true,
            numberOfMonths: 1,
            selectOtherMonths: true,
            showOtherMonths: true,
            beforeShowDay: function(date) {
                res = true;
                {if !($saturday != N) }
                    var day = date.getDay();
                    res = res && (day != 6);
                {/if}
                {if !($sunday != N) }
                    var day = date.getDay();
                    res = res && (day != 0);
                {/if}
                {if !($monday != N) }
                    now = new Date();
                    m = new Date();
                    if(now.getDay()){ m.setDate(now.getDate() + 8 - now.getDay()) } else { m.setDate(now.getDate() + 1) }
                    res = res && !((m.getDate() == date.getDate()) && (m.getMonth() == date.getMonth()));
                {/if}
                {if $limit_weekdays != '' && $limit_weekdays != 'C'}
                    res = res && (date.getDay() == {$limit_weekdays});
                {/if}
                {if $limit_weekdays == 'C'}
                    let customerCalendar = {$service_params.customer_shipping_calendar|to_json};
                    res = res && (customerCalendar.indexOf(date.getDay()) !== -1);
                {/if}
                return [res];
            },
            firstDay: {if $settings.Appearance.calendar_week_format == "sunday_first"}0{else}1{/if},
            dayNamesMin: ['{__("weekday_abr_0")}', '{__("weekday_abr_1")}', '{__("weekday_abr_2")}', '{__("weekday_abr_3")}', '{__("weekday_abr_4")}', '{__("weekday_abr_5")}', '{__("weekday_abr_6")}'],
            monthNamesShort: ['{__("month_name_abr_1")|escape:"html"}', '{__("month_name_abr_2")|escape:"html"}', '{__("month_name_abr_3")|escape:"html"}', '{__("month_name_abr_4")|escape:"html"}', '{__("month_name_abr_5")|escape:"html"}', '{__("month_name_abr_6")|escape:"html"}', '{__("month_name_abr_7")|escape:"html"}', '{__("month_name_abr_8")|escape:"html"}', '{__("month_name_abr_9")|escape:"html"}', '{__("month_name_abr_10")|escape:"html"}', '{__("month_name_abr_11")|escape:"html"}', '{__("month_name_abr_12")|escape:"html"}'],
            yearRange: '{if $start_year}{$start_year}{else}c-100{/if}:c+10',
            {if $min_date || $min_date === 0}minDate: {$min_date},{/if}
            {if $max_date || $max_date === 0}maxDate: {$max_date},{/if}
            dateFormat: '{if $settings.Appearance.calendar_date_format == "month_first"}mm/dd/yy{else}dd/mm/yy{/if}'
        });
    });
{$rdelim}(Tygh, Tygh.$));
</script>
