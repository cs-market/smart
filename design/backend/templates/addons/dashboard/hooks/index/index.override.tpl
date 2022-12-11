{hook name="index:index"}
    {hook name="index:alert_block"}
        {if "MULTIVENDOR"|fn_allowed_for && $dashboard_alert}
            <div class="alert alert-block">
                <div class="debt-notification__text">
                    {$dashboard_alert nofilter}
                </div>
            </div>
        {/if}
    {/hook}

    <div class="dashboard-row row-fluid" id="dashboard">
        {foreach from=$stats item='period_stat' key='period'}
        <div class="dashboard-cards span4">
            <h4 class="center">{__("`$period`_period")}</h4>
            <div class="dashboard-card dashboard-card--balance">
                <div class="dashboard-card-title">{__("reports_parameter_1")}</div>
                <div class="dashboard-card-content">
                    <h3>
                        {include file="common/price.tpl" value=$period_stat.total}
                    </h3>
                    {if $period_stat.total_rel}{$period_stat.total_rel}%{else}&nbsp;{/if}
                </div>
            </div>
            <div class="dashboard-card dashboard-card--balance">
                <div class="dashboard-card-title">{__("reports_parameter_2")}</div>
                <div class="dashboard-card-content">
                    <h3>
                        {$period_stat.count_orders}
                    </h3>
                    {if $period_stat.count_orders_rel}{$period_stat.count_orders_rel}%{else}&nbsp;{/if}
                </div>
            </div>
            <div class="dashboard-card dashboard-card--balance">
                <div class="dashboard-card-title">{__("avg_total")}</div>
                <div class="dashboard-card-content">
                    <h3>
                        {include file="common/price.tpl" value=$period_stat.avg_total}
                    </h3>
                    {if $period_stat.avg_total_rel}{$period_stat.avg_total_rel}%{else}&nbsp;{/if}
                </div>
            </div>
            <div class="dashboard-card dashboard-card--balance">
                <div class="dashboard-card-title">{__("avg_sku")}</div>
                <div class="dashboard-card-content">
                    <h3>
                        {$period_stat.avg_sku}
                    </h3>
                    {if $period_stat.avg_sku_rel}{$period_stat.avg_sku_rel}%{else}&nbsp;{/if}
                </div>
            </div>
            <div class="dashboard-card dashboard-card--balance">
                <div class="dashboard-card-title">{__("customers")}</div>
                <div class="dashboard-card-content">
                    <h3>
                        {$period_stat.customers}
                    </h3>
                    {if $period_stat.customers_rel}{$period_stat.customers_rel}%{else}&nbsp;{/if}
                </div>
            </div>
            <div class="dashboard-card dashboard-card--balance">
                <div class="dashboard-card-title">{__("avg_sku_per_customer")}</div>
                <div class="dashboard-card-content">
                    <h3>
                        {$period_stat.avg_sku_per_customer}
                    </h3>
                    {if $period_stat.avg_sku_per_customer_rel}{$period_stat.avg_sku_per_customer_rel}%{else}&nbsp;{/if}
                </div>
            </div>
        </div>
        {/foreach}
    <!--dashboard--></div>
{/hook}
