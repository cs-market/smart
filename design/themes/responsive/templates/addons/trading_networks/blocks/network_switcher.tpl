<div id="localizations_{$block.block_id}">
{$uid = uniqid()}

{if $auth.network_users}
    <div class="ty-select-wrapper">{include file="common/select_object.tpl" style="graphic" suffix="network_{$uid}" link_tpl=$config.current_url|fn_link_attach:"swithc_user_id=" items=$auth.network_users selected_id=$smarty.const.STORAGE display_icons=false key_name="firstname" text=__("trading_network")}</div>}
{/if}

<!--localizations_{$block.block_id}--></div>
