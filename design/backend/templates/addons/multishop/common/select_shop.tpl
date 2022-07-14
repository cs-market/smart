{$id = $select_id|default:"top_shop_id"}

<div class="store-selector">
{$curl = $config.current_url|urlencode}
{if $runtime.company_id && !$runtime.shop_id}
{$type = "opened"}
{else}
{$text = $runtime.shop_id|fn_get_shop_name}
{/if}
    {__("pick_shop")} - {include file="addons/multishop/common/shop_select_object.tpl" data_url="shops.get_shops_list?curl=$curl&action=href" type=$type text=$text|default:__("select") id=$id}
</div>