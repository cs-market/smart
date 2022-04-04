<div id="eshop-account-info">
    {foreach from=$eshop_logistic_account_info item=account_data key=account_data_key }
        <div class="control-group">
            <label class="control-label" for="{$account_data_key}">{$account_data.description}:</label>
            <div class="controls">
                {if isset($account_data.value)}
                    <label id="{$account_data_key}">{$account_data.value nofilter}</label>
                {elseif !empty($account_data.services)}
                    <ul>
                        {foreach from=$account_data.services key=service_key item=service_description}
                            <li>{$service_description}</li>
                        {/foreach}
                    </ul>
                {/if}
            </div>
        </div>
    {/foreach}
<!--eshop-account-info--></div>
<a class="btn cm-ajax cm-post" data-ca-target-id="eshop-account-info" href="{"eshop_logistic.get_account_data"|fn_url}">{__('eshop_logistic.get_actual_data')}</a>
<a class="btn" href="{"eshop_logistic.get_cities_codes"|fn_url}">{__('eshop_logistic.get_cities_codes')}</a>