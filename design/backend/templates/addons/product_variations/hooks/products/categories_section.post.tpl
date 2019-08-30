{if $product_data.product_type === "\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_VARIATION"|constant}
    <div class="control-group">
        <label for="variation_code" class="control-label cm-required">{__("product_variations.variation")}</label>
        <div class="controls">
            <select id="variation_code" name="product_data[variation_code]">
                {foreach from=$combinations item="combination" key="variation_code"}
                    <option value="{$variation_code}" {if $variation_code == $product_data.variation_code} selected{elseif $combination.exists} disabled{/if}>{$combination.name}</option>
                {/foreach}
            </select>
            <p>
                {__("product_variations.variation_of_product", [
                "[url]" => "products.update?product_id=`$product_data.parent_product_id`"|fn_url,
                "[product]" => $parent_product_data.product
                ])}
            </p>
        </div>
    </div>

    {$multiple_categoires = count($product_data.category_ids) > 1}

    {capture name="variation_categories"}
        {foreach from=$product_data.category_ids|default:$request_category_id item="c_id"}
            {assign var="category_data" value=$c_id|fn_get_category_data:$smarty.const.CART_LANGUAGE:'':false:true:false:true}
            {if $multiple_categoires}
                <p class="cm-js-item">
            {/if}
            {foreach from=$category_data.path_names key="path_id" item="path_name" name="path_names"}
                <a target="_blank" class="{if !$smarty.foreach.path_names.last}ty-breadcrumbs__a{else}ty-breadcrumbs__current{/if}" href="{"categories.update&category_id={$path_id}"|fn_url}">{$path_name}</a>{if !$smarty.foreach.path_names.last} / {/if}
            {/foreach}
            {if $multiple_categoires}
                </p>
            {/if}
        {/foreach}
    {/capture}

    <div class="control-group">
        <label class="control-label">{__("categories")}</label>
        <div class="controls">
            <p>
                {$smarty.capture.variation_categories nofilter}
            </p>
        </div>
    </div>
{/if}
