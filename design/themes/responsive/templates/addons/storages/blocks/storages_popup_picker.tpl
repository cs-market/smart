{if $runtime.force_to_choose_storage}
    {capture name='switcher_content'}
        <div class="ty-storages__wrapper">
            <div id="switcher_content_{$block.block_id}">
                <div class="ty-storages__container">
                    {foreach from=$storages item="storage"}
                            <a class="ty-storages__item" href="{$config.current_url|fn_link_attach:"storage=`$storage.storage_id`"}" rel="nofollow">{$storage.storage}</a>
                    {/foreach}
                </div>
            <!--switcher_content_{$block.block_id}--></div>
        </div>
    {/capture}

    {include file="common/popupbox.tpl"
        link_text=__("")
        link_meta="cm-dialog-non-closable"
        title=__("storage_switcher")
        id="storage_switcher"
        content=$smarty.capture.switcher_content
        wysiwyg=false
        dialog_additional_attrs=["data-ca-dialog-class" => "ty-storages__dialog"]
    }
    <script>
        $(document).ready(function(){
            $('#opener_storage_switcher').removeClass('cm-dialog-auto-size').click();
        });
    </script>
{/if}
