{capture name="mainbox"}
    {include file="common/pagination.tpl"}

    {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
    {assign var="r_url" value=$c_url|escape:"url"}
    {assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}

    {if $logs}
        <table width="100%" class="table table-middle eshop-log-table">
            <thead>
            <tr>
                <th width="5%">
                    <a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("eshop_logistic.log_id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                </th>
                <th width="10%">
                    <a class="cm-ajax" href="{"`$c_url`&sort_by=start_time&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("eshop_logistic.log_date")}{if $search.sort_by == "start_time"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                </th>
                <th width="5%">
                    <a class="cm-ajax" href="{"`$c_url`&sort_by=time&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("eshop_logistic.log_time")}{if $search.sort_by == "time"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                </th>
                <th width="10%">{__("eshop_logistic.log_message")}</th>
                <th width="45%">{__("eshop_logistic.log_data")}</th>
                <th width="10%">
                    <a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("eshop_logistic.log_status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                </th>
                <th width="10%">
                    <a class="cm-ajax" href="{"`$c_url`&sort_by=type&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("eshop_logistic.log_operation")}{if $search.sort_by == "type"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                </th>
                <th width="5%">
                    <a class="cm-ajax" href="{"`$c_url`&sort_by=caching&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("eshop_logistic.is_caching")}{if $search.sort_by == "caching"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
                </th>
            </tr>
            </thead>

            {foreach from=$logs item=log}
                <tbody>
                <tr class="{if $log.status == "Addons\\EshopLogistic\\LoggerEnum::ERROR"|enum} eshop-log-error {/if}">
                    <td data-th="{__("eshop_logistic.log_id")}">{$log.log_id}</td>
                    <td data-th="{__("eshop_logistic.log_date")}">{$log.start_time|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
                    <td data-th="{__("eshop_logistic.log_time")}">{$log.time}</td>
                    <td data-th="{__("eshop_logistic.log_message")}">{$log.message nofilter}</td>
                    <td data-th="{__("eshop_logistic.log_data")}">
                        {if $log.data}
                            <p><a onclick="Tygh.$('#data_{$log.log_id}').toggle(); return false;" class="underlined"><span>{__("eshop_logistic.view_log_data")}&rsaquo;&rsaquo;</span></a></p>
                            <div id="data_{$log.log_id}" class="notice-box hidden">
                                {include file="addons/eshop_logistic/components/logs_record.tpl" data=$log.data log_lvl=0}
                            </div>
                        {/if}
                    </td>
                    <td data-th="{__("eshop_logistic.log_status")}">{if $log.status == "Addons\\EshopLogistic\\LoggerEnum::ERROR"|enum}
                            {__("eshop_logistic.log_status_error")}
                        {elseif $log.status == "Addons\\EshopLogistic\\LoggerEnum::SUCCESS"|enum}
                            {__("eshop_logistic.log_status_success")}
                        {/if}
                    </td>
                    <td data-th="{__("eshop_logistic.log_operation")}">{$log.type}</td>
                    <td data-th="{__("eshop_logistic.is_caching")}">{$log.caching}</td>
                </tr>
                </tbody>
            {/foreach}
        </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    {include file="common/pagination.tpl"}

{/capture}
{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("eshop_logistic.clear_logs") href="eshop_logistic.clear_logs" class="cm-confirm" method="POST"}</li>
        {if $settings.Logging.log_lifetime|intval}
            <li>{btn type="list" text=__("eshop_logistic.clear_old_logs", [$settings.Logging.log_lifetime|intval]) href="eshop_logistic.clear_old_logs" class="cm-confirm" method="POST"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}
{include file="common/mainbox.tpl" title=__("eshop_logistic.logs_title") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}