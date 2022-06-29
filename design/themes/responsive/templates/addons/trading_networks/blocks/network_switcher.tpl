{if $auth.network_users}
    {capture name='switcher_content'}
        <div id="switcher_content_{$block.block_id}">
            <div class="ty-trading_network__container">
                {foreach from=$auth.network_users item="network" key="network_id"}
                <a href="{$config.current_url|fn_link_attach:"switch_user_id=`$network_id`"}" class="ty-trading_network__item">
                    {$network.firstname nofilter}
                </a>
                {/foreach}
            </div>
        <!--switcher_content_{$block.block_id}--></div>
        <div class="buttons-container">
            <a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" class="ty-btn ty-btn__primary ty-btn__big">{__('logout_from_system')}</a>
        </div>
    {/capture}

    {include file="common/popupbox.tpl"
        link_text=__("")
        link_meta="cm-dialog-non-closable"
        title=__("trade_network_switcher")
        id="trade_network_switcher"
        content=$smarty.capture.switcher_content
    }
    <script>
        $(document).ready(function(){
            $('#opener_trade_network_switcher').click();
        });
    </script>
{/if}
