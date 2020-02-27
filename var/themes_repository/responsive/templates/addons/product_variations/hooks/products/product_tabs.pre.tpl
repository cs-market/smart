{if $product.product_type === "\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_CONFIGURABLE"|constant}
    {script src="js/addons/product_variations/func.js"}

    <div id="configurable_product_tabs_{$product.product_id}">

    {*
        The cleans parameters for right showing product block on the detail product page when reload of tabs.
        Cleaning needed, because on detail product page could use block with product which the use transmitted parameters.
    *}
    {if $smarty.request.appearance}
        {foreach from=$smarty.request.appearance key="setting" item="value"}
            {assign var=$setting value="" scope="global"}
        {/foreach}
    {/if}
{/if}