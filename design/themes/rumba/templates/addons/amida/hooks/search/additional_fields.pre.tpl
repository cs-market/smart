{$brand_categories = $addons.amida.brand_category_id|fn_get_subcategories}
{$manufacturer_categories = $addons.amida.manufacturer_category_id|fn_get_subcategories}

{if $brand_categories || $manufacturer_categories}
<select class="ty-search-block__brand" name="cid">
    <option disabled="disabled" {if !$search.cid}selected="_selected"{/if}>{__("brand")}/{__("manufacturer")}</option>
    {if $brand_categories}
    <optgroup label="{__('brand')}">
        {foreach from=$brand_categories item='category'}
            <option value="{$category.category_id}" {if $search.cid == $category.category_id}selected="_selected"{/if}>{$category.category}</option>
        {/foreach}
    </optgroup>
    {/if}
    {if $manufacturer_categories}
    <optgroup label="{__('manufacturer')}">
        {foreach from=$manufacturer_categories item='category'}
            <option value="{$category.category_id}" {if $search.cid == $category.category_id}selected="_selected"{/if}>{$category.category}</option>
        {/foreach}
    </optgroup>
    {/if}
</select>
{/if}
