{assign var="discount_label" value="discount_label_`$obj_prefix``$obj_id`"}
{assign var="discount_label_content" value=$smarty.capture.$discount_label}
{capture name="discount_label_`$obj_prefix``$obj_id`"}
    {if !$details_page}
        {foreach from=$product.stickers item="sticker"}
            {math equation="rand()" assign="rnd"}
            <div class="sticker-wrapper sticker-wrapper-{$sticker.position} sticker-type-{$sticker.type} {$sticker.class} hidden">
                {if $sticker.type == 'G'}
                    {include file="common/image.tpl" obj_id=$rnd images=$sticker.main_pair}
                {else}
                    <span {if $sticker.styles} style="{$sticker.styles}"{/if}>{$sticker.text|nl2br nofilter}</span>
                {/if}
            </div>
        {/foreach}
    {/if}
    {$discount_label_content nofilter}
{/capture}
