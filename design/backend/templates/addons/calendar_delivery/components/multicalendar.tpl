{if $settings.Appearance.calendar_date_format == "month_first"}
    {assign var="date_format" value="%m/%d/%Y"}
{else}
    {assign var="date_format" value="%d/%m/%Y"}
{/if}

<div class="calendar">
    <input type="text" data-ca-meta-class="{$meta_class}" id="{$date_id}" name="{$date_name}" class="input-xxlarge cm-calendar {if $date_meta}{$date_meta}{/if}" value="{if $date_val}{$date_val}{/if}" {$extra nofilter} size="10" autocomplete="disabled" style="width: 500px !important;" />
    {if $show_time}
    <input class="input-time" data-ca-meta-class="{$meta_class}" size="5" maxlength="5" type="text" name="{$time_name}" value="{if $date_val}{$date_val|fn_parse_date|date_format:"%H:%M"}{/if}" placeholder="00:00" />
    {/if}
    <span data-ca-external-focus-id="{$date_id}" class="icon-calendar cm-external-focus"></span>
    {* autocomplete="off" for Chrome *}
    <input type="text" hidden disabled name="fake_mail" aria-hidden="true">
</div>

<script type="text/javascript">
(function(_, $) {$ldelim}
    $.ceEvent('on', 'ce.commoninit', function(context) {
        var dates = {","|explode:$date_val|default:[]|json_encode nofilter};

        function addDate(date) {
            if (jQuery.inArray(date, dates) < 0) 
                dates.push(date);
        }

        function removeDate(index) {
            dates.splice(index, 1);
        }

        // Adds a date if we don't have it yet, else remove it
        function addOrRemoveDate(date) {
            var index = jQuery.inArray(date, dates);
            if (index >= 0) 
                removeDate(index);
            else 
                addDate(date);
        }

        // Takes a 1-digit number and inserts a zero before it
        function padNumber(number) {
            var ret = new String(number);
            if (ret.length == 1) 
                ret = "0" + ret;
            return ret;
        }
        $('#{$date_id}').datepicker( {
            onClose: function() {
                $(this).data('datepicker').inline = false;
            },
            onSelect: function (dateText, inst) {
                addOrRemoveDate(dateText);
                $(this).val(dates);
                $(this).data('datepicker').inline = true;
            },
            beforeShowDay: function(date){
                var gotDate = $.inArray($.datepicker.formatDate($(this).datepicker('option', 'dateFormat'), date), dates);
                if (gotDate >= 0) {
                    return [true,"ui-state-default ui-state-active"];
                }
                return [true, ""];
            },
            autoSize: true,
            changeMonth: true,
            duration: 'fast',
            changeYear: true,
            numberOfMonths: 2,
            selectOtherMonths: true,
            showOtherMonths: true,
            firstDay: {if $settings.Appearance.calendar_week_format == "sunday_first"}0{else}1{/if},
            dayNamesMin: ['{__("weekday_abr_0")|escape:"javascript"}', '{__("weekday_abr_1")|escape:"javascript"}', '{__("weekday_abr_2")|escape:"javascript"}', '{__("weekday_abr_3")|escape:"javascript"}', '{__("weekday_abr_4")|escape:"javascript"}', '{__("weekday_abr_5")|escape:"javascript"}', '{__("weekday_abr_6")|escape:"javascript"}'],
            monthNamesShort: ['{__("month_name_abr_1")|escape:"javascript"}', '{__("month_name_abr_2")|escape:"javascript"}', '{__("month_name_abr_3")|escape:"javascript"}', '{__("month_name_abr_4")|escape:"javascript"}', '{__("month_name_abr_5")|escape:"javascript"}', '{__("month_name_abr_6")|escape:"javascript"}', '{__("month_name_abr_7")|escape:"javascript"}', '{__("month_name_abr_8")|escape:"javascript"}', '{__("month_name_abr_9")|escape:"javascript"}', '{__("month_name_abr_10")|escape:"javascript"}', '{__("month_name_abr_11")|escape:"javascript"}', '{__("month_name_abr_12")|escape:"javascript"}'],
            yearRange: '{if $start_year}{$start_year}{else}c-100{/if}:c+10',
            {if $min_date || $min_date === 0}minDate: {$min_date},{/if}
            {if $max_date || $max_date === 0}maxDate: {$max_date},{/if}
            dateFormat: '{if $settings.Appearance.calendar_date_format == "month_first"}mm/dd/yy{else}dd/mm/yy{/if}'
        });
    });
{$rdelim}(Tygh, Tygh.$));
</script>
