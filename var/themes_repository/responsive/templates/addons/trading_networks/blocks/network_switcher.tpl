{if $auth.network_users}
    {capture name='switcher_content'}
        <div class="ty-trading-network__wrapper object-container">
            <div id="switcher_content_{$block.block_id}">
                <div class="ty-trading-network__container">
                    {foreach from=$auth.network_users item="network" key="network_id"}
                    <a href="{$config.current_url|fn_link_attach:"switch_user_id=`$network_id`"}" class="ty-trading-network__item">
                        <p><b>{$network.firstname nofilter}</b></p>
                        <p class="muted">{__('code')}: {$network.email nofilter}
                            {if $network.s_address|trim}
                                <br>{$network.s_address nofilter}
                            {/if}
                        </p>
                    </a>
                    {/foreach}
                </div>
            <!--switcher_content_{$block.block_id}--></div>
        </div>
        <div class="buttons-container ty-center">
            {$auth_url = "auth.login_form"}
            <a href="{"auth.logout?redirect_url=`$auth_url`"|fn_url}" class="ty-btn ty-btn__primary ty-btn__big">{__('logout_from_system')}</a>
        </div>
    {/capture}

    {include file="common/popupbox.tpl"
        link_text=__("")
        link_meta="cm-dialog-non-closable"
        title=__("trade_network_switcher")
        id="trade_network_switcher"
        content=$smarty.capture.switcher_content
        wysiwyg=false
        dialog_additional_attrs=["data-ca-dialog-class" => "ty-trading-network__dialog"]
    }
    <script>
        $(document).ready(function(){
            $('#opener_trade_network_switcher').removeClass('cm-dialog-auto-size').click();
        });
    </script>
{/if}
