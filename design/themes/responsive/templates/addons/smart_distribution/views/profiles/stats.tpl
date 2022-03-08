<div class="ty-stats-container">
    {hook name="profiles:stats"}
    <div class="ty-control-group">
        <div class="ty-control-group__title">{__("total_orders_per_period")}:</div>
        <div>
            <form action="{""|fn_url}" method="get" name="stats_form">
                {include file="common/period_selector.tpl" search=$stats form_name="stats_calendar"}
                {include file="buttons/button.tpl" but_text=__('apply') but_meta="ty-btn__primary hidden cm-submit" but_id="button_apply" but_role='act'}
                <input type="hidden" name="dispatch" value="profiles.stats" />
                <script>
                    $('.ty-calendar__input').change(function () {
                        $('#button_apply').removeClass('hidden');
                    });
                </script>
            </form>
        </div>
        <span class="ty-control-group__label" for="previous_period">{__("previous_period")}: {include file="common/price.tpl" value=$stats.prev_orders}</span>
        <span class="ty-control-group__label" for="current_period">{__("current_period")}: {include file="common/price.tpl" value=$stats.current_orders}</span>
    </div>

    {if $stats.shippments}
    <div class="ty-control-group">
        <span class="ty-control-group__title">{__("total_shipments_per_period")}:</span>
        <span class="ty-control-group__label" for="current_period">{__("current_period")}: {include file="common/price.tpl" value=$stats.shippments}</span>
    </div>
    {/if}

    {/hook}
</div>

{capture name="mainbox_title"}<span>{__('my_stats')}</span>{/capture}
