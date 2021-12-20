{********************** Old Price *****************}
{capture name="old_price_`$obj_id`"}
    {if $show_price_values && $show_old_price && ($product.discount || $product.list_discount)}
        <div class="cm-reload-{$obj_prefix}{$obj_id} ty-price-old" id="old_price_update_{$obj_prefix}{$obj_id}">
            {hook name="products:old_price"}
            {if $product.discount}
                <span class="ty-strike">{include file="common/price.tpl" value=$product.original_price|default:$product.base_price span_id="old_price_`$obj_prefix``$obj_id`" class="ty-nowrap"}</span>
            {elseif $product.list_discount}
                <span class="ty-strike">{include file="common/price.tpl" value=$product.list_price span_id="list_price_`$obj_prefix``$obj_id`" class="ty-nowrap"}</span>
            {/if}
            {/hook}
        <!--old_price_update_{$obj_prefix}{$obj_id}--></div>
    {/if}
{/capture}
{if $no_capture}
    {assign var="capture_name" value="old_price_`$obj_id`"}
    {$smarty.capture.$capture_name nofilter}
{/if}

{********************** Price *********************}
{capture name="price_`$obj_id`"}
    <div class="{if $product.zero_price_action !== "A"}cm-reload-{$obj_prefix}{$obj_id}{/if} ty-price-update ty-price-actual" id="price_update_{$obj_prefix}{$obj_id}">
        <input type="hidden" name="appearance[show_price_values]" value="{$show_price_values}" />
        <input type="hidden" name="appearance[show_price]" value="{$show_price}" />
        {if $show_price_values}
            {if $show_price}
            {hook name="products:prices_block"}
                {if $product.price|floatval || $product.zero_price_action == "P" || ($hide_add_to_cart_button == "Y" && $product.zero_price_action == "A")}
                    <span class="ty-price{if !$product.price|floatval && !$product.zero_price_action} hidden{/if}" id="line_discounted_price_{$obj_prefix}{$obj_id}">{include file="common/price.tpl" value=$product.price span_id="discounted_price_`$obj_prefix``$obj_id`" class="" live_editor_name="product:price:{$product.product_id}" live_editor_phrase=$product.base_price}</span>
                {elseif $product.zero_price_action == "A" && $show_add_to_cart}
                    {assign var="base_currency" value=$currencies[$smarty.const.CART_PRIMARY_CURRENCY]}
                    <span class="ty-price-curency"><span class="ty-price-curency__title">{__("enter_your_price")}:</span>
                    <div class="ty-price-curency-input">
                        <input 
                            type="text"
                            name="product_data[{$obj_id}][price]"
                            class="ty-price-curency__input cm-numeric"
                            data-a-sign="{$base_currency.symbol nofilter}" 
                            data-a-dec="{if $base_currency.decimal_separator}{$base_currency.decimal_separator nofilter}{else}.{/if}" 
                            data-a-sep="{if $base_currency.thousands_separator}{$base_currency.thousands_separator nofilter}{else},{/if}"
                            data-p-sign="{if $base_currency.after === "YesNo::YES"|enum}s{else}p{/if}"
                            data-m-dec="{$base_currency.decimals}"
                            size="3"
                            value=""
                        />
                    </div>
                    </span>

                {elseif $product.zero_price_action == "R"}
                    <span class="ty-no-price">{__("contact_us_for_price")}</span>
                    {assign var="show_qty" value=false}
                {/if}
            {/hook}
            {/if}
        {elseif $settings.Checkout.allow_anonymous_shopping == "hide_price_and_add_to_cart" && !$auth.user_id}
            <span class="ty-price">{__("sign_in_to_view_price")}</span>
        {/if}
    <!--price_update_{$obj_prefix}{$obj_id}--></div>
{/capture}
{if $no_capture}
    {assign var="capture_name" value="price_`$obj_id`"}
    {$smarty.capture.$capture_name nofilter}
{/if}
