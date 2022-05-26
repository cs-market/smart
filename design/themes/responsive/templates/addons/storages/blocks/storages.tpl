<div id="localizations_{$block.block_id}">
{$uid = uniqid()}
{if $storages && $storages|count > 1}
    <div class="ty-select-wrapper">{include file="common/select_object.tpl" style="graphic" suffix="storage_{$uid}" link_tpl=$config.current_url|fn_link_attach:"storage=" items=$storages selected_id=$smarty.const.STORAGE display_icons=false key_name="storage" text=__("storages.storage")}</div>
{/if}

<!--localizations_{$block.block_id}--></div>
