<div class="{if $selected_section !== "prices"}hidden{/if}" id="content_prices">
    <div class="table-responsive-wrapper">
        <table width="100%" class="table table-middle table--relative table-responsive">
        <thead>
            <tr>
                <th width="50%">{__("product")}</th>
                <th width="10%">{__("base_price")}</th>
                <th width="10%">{__("price")}</th>
                {if $order_info.use_discount}
                <th width="5%">{__("discounted_price")}</th>
                {/if}
            </tr>
        </thead>
        {foreach from=$order_info.products item="oi" key="key"}
        {if !$oi.extra.parent}
        <tr>
            <td data-th="{__("product")}">
                <div class="order-product-image">
                    {include file="common/image.tpl" image=$oi.main_pair.icon|default:$oi.main_pair.detailed image_id=$oi.main_pair.image_id image_width=$settings.Thumbnails.product_admin_mini_icon_width image_height=$settings.Thumbnails.product_admin_mini_icon_height href="products.update?product_id=`$oi.product_id`"|fn_url}
                </div>
                <div class="order-product-info">
                    {if !$oi.deleted_product}<a href="{"products.update?product_id=`$oi.product_id`"|fn_url}">{/if}{$oi.product nofilter}{if !$oi.deleted_product}</a>{/if}
                    <div class="products-hint">
                    {hook name="orders:product_info"}
                        {if $oi.product_code}<p class="products-hint__code">{__("sku")}:{$oi.product_code}</p>{/if}
                    {/hook}
                    </div>
                    {if $oi.product_options}<div class="options-info">{include file="common/options_info.tpl" product_options=$oi.product_options}</div>{/if}
                </div>
            </td>
            <td class="nowrap" data-th="{__("base_price")}">
                {include file="common/price.tpl" value=$oi.initial_price}</td>
            <td class="nowrap" data-th="{__("price")}">
                {include file="common/price.tpl" value=$oi.original_price}</td>
            </td>
            {if $order_info.use_discount}
            <td class="nowrap" data-th="{__("discounted_price")}">
                {include file="common/price.tpl" value=$oi.price}</td>
            {/if}
        </tr>
        {/if}
        {/foreach}
        </table>
    </div>
<!--content_prices--></div>
