<div class="ty-product-filters {if $collapse}hidden{/if}" id="content_{$filter_uid}">
    {if $filter.variants}
        {foreach $filter.variants as $variant}
        <div class="ty-product-filters__item {if $variant.selected} selected {/if} {if $variant.disabled} disabled {/if}">
            <label {if $variant.disabled} class="{if $variant.selected}ty-product-filters__empty-result{/if}"{/if}>
                <input class="cm-product-filters-checkbox"
                       type="checkbox"
                       {if $variant.selected}checked="checked"{/if}
                       name="product_filters[{$filter.filter_id}]"
                       data-ca-filter-id="{$filter.filter_id}"
                       value="{$variant.variant_id}"
                       id="elm_checkbox_{$filter_uid}_{$variant.variant_id}"
                        {if $variant.disabled && !$variant.selected}disabled="disabled"{/if}>
                <span>{$filter.prefix}{$variant.variant|fn_text_placeholders}{$filter.suffix}</span>
            </label>
        </div>
        {/foreach}
    {else}
        <p id="elm_search_empty_{$filter_uid}" class="ty-product-filters__no-items-found hidden">{__("no_items_found")}</p>
    {/if}
</div>
