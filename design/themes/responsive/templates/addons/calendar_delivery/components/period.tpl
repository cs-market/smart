{if $period_step}
    {$periods = $period_start|fn_get_calendar_delivery_period:$period_finish:$period_step}

    <select name="{$date_name}" id="{$date_id}">
    {foreach from=$periods item="period"}
    <option value="{$period.value}" data-ca-period-hour = {$period.hour} {if $period.value == $date_val}selected="selected"{/if}>{$period.value}</option>
    {/foreach}
    </select>

    <script type="text/javascript">
    (function(_, $) {
        $.ceEvent('on', 'ce.commoninit', function(context) {
            var hidePeriod = function() {
                if ($('#{$datapicker_id}').datepicker('getDate') < new Date()) {
                    // if set today in calendar
                    var nowHour = new Date().getHours();
                    // disable past periods
                    $('#{$date_id}').children().each(function() {
                        if (nowHour > $(this).data('caPeriodHour')) {
                            $(this).prop('disabled', true);
                        }
                    });
                } else {
                    // show all options
                    $('#{$date_id}').children().prop('disabled', false);
                }

                // select first availble
                $('#{$date_id}').children("*:enabled").eq(0).prop('selected', true);
            };

            // on load
            hidePeriod();

            // on change
            $('#{$datapicker_id}').datepicker()
            .change(function () 
            {
                hidePeriod();
            });
        });
    }(Tygh, Tygh.$));
    </script>
{/if}
