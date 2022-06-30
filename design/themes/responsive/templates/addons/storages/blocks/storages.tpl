{if $runtime.force_to_choose_storage}
    {capture name='switcher_content'}
        <div id="switcher_content_{$block.block_id}">
            <div class="ty-trading_network__container">
                {foreach from=$storages item="storage"}
                        <a class="ty-trading_network__item" href="{$config.current_url|fn_link_attach:"storage=`$storage.storage_id`"}" rel="nofollow">{$storage.storage}</a>
                {/foreach}
            </div>
        <!--switcher_content_{$block.block_id}--></div>
    {/capture}

    {include file="common/popupbox.tpl"
        link_text=__("")
        link_meta="cm-dialog-non-closable"
        title=__("storage_switcher")
        id="storage_switcher"
        content=$smarty.capture.switcher_content
    }
    <script>
        $(document).ready(function(){
            $('#opener_storage_switcher').click();
        });
    </script>
{else}
    {$uid = uniqid()}
    {if $storages}
        <div class="ty-dropdown-box">
            <div id="sw_elm_dropdown_fields" class="ty-dropdown-box__title cm-combination"><a><i class="ty-icon-rumba-truck"><span></i>{$runtime.current_storage.storage}<i class="ty-icon-down-micro"></i></a></span></div>
            <ul id="elm_dropdown_fields" class="ty-dropdown-box__content cm-popup-box hidden">
                {foreach from=$storages item="storage"}
                    <li class="ty-dropdown-box__item">
                        <a class="ty-dropdown-box__item-a" href="{$config.current_url|fn_link_attach:"storage=`$storage.storage_id`"}" rel="nofollow">{$storage.storage}</a>
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
{/if}
