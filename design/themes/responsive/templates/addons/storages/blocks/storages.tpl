{if $storages}
{$uid = uniqid()}
    <div class="ty-dropdown-box">
        <div id="sw_elm_dropdown_fields" class="ty-dropdown-box__title cm-combination">
            <a>
                <i class="ty-icon-aurora-truck"></i>
                <span class="ty-storages__dropdown-title">{$runtime.current_storage.storage}</span>
                <i class="ty-icon-down-micro"></i>
            </a>
        </div>
        <ul id="elm_dropdown_fields" class="ty-dropdown-box__content cm-popup-box hidden">
            {foreach from=$storages item="storage"}
                <li class="ty-dropdown-box__item">
                    <a class="ty-dropdown-box__item-a" href="{$config.current_url|fn_link_attach:"storage=`$storage.storage_id`"}" rel="nofollow">{$storage.storage}</a>
                </li>
            {/foreach}
        </ul>
    </div>
{/if}
