{foreach from=$product.stickers item="sticker"}
<div class="cm-reload-{$product.product_id}" id="product_stickers_update_{$product.product_id}_update">
    {math equation="rand()" assign="rnd"}
    <div class="sticker-wrapper sticker-wrapper-{$sticker.position} sticker-type-{$sticker.type} {$sticker.class} hidden sticker-type-{$sticker.type}">
        {if $sticker.type == 'G'}
            {include file="common/image.tpl" obj_id=$rnd images=$sticker.main_pair}
        {else}
            <span {if $sticker.styles} style="{$sticker.styles}"{/if}>{$sticker.text|nl2br nofilter}</span>
        {/if}
    </div>
<!--product_stickers_update_{$product.product_id}_update--></div>
{/foreach}
