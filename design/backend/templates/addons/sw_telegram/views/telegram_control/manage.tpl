{capture name="mainbox"}

    {capture name="sidebar"}
        {include file="addons/sw_telegram/views/telegram_control/components/search.tpl"}
    {/capture}

    {include file="common/pagination.tpl" save_current_page=true save_current_url=true}

    {assign var="return_current_url" value=$config.current_url|escape:url}
    
    {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
    {assign var="c_icon" value="<i class=\"icon-`$search.sort_order_rev`\"></i>"}
    {assign var="c_dummy" value="<i class=\"icon-dummy\"></i>"}
    
    {assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}

    {if $data_list}
        <table width="100%" class="table table-middle table--relative">
            <thead>
                <tr>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=user_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("user_id")}{if $search.sort_by == "user_id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=email&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("email")}{if $search.sort_by == "email"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=phone&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("phone")}{if $search.sort_by == "phone"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=firstname&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("user")}{if $search.sort_by == "firstname"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=chat_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>Chat ID{if $search.sort_by == "chat_id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=chat_id_timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("sw_telegram.date_add")}{if $search.sort_by == "firstnchat_id_timestampame"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                    <th><a class="cm-ajax" href="{"`$c_url`&sort_by=noty_tg&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("sw_telegram.confirm_noty_short")}{if $search.sort_by == "noty_tg"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                </tr>
            </thead>
            {foreach from=$data_list item="user"}
                <tr>
                    <td>
                        {if $user.user_id}<a href="{"profiles.update&user_id=`$user.user_id`"|fn_url}">#{$user.user_id}{else}-{/if}
                    </td>
                    <td>
                        {$user.email}<br/>
                        <em>{if $user.user_type == 'C'}
                            {__("user")}
                        {elseif $user.user_type == 'V'}
                            {__("vendor")}
                        {/if}</em>
                        
                    </td>
                    <td>
                        {$user.phone}
                    </td>
                    <td>
                        {$user.firstname} {$user.firstname}
                    </td>
                    <td>
                        {$user.chat_id}
                    </td>
                    <td>
                        {if $user.chat_id_timestamp}{$user.chat_id_timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{else}---{/if}
                    </td>
                    <td>
                        {if $user.noty_tg == 'Y'}{__("subscribed")}{else}{__("no")}{/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    {include file="common/pagination.tpl"}
{/capture}


{include file="common/mainbox.tpl" title=__("sw_telegram.manage_list") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar}
