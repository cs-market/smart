{if isset($block.content.items.filling) && $block.content.items.filling == "product_variations.variations_filling"}
    <td class="ty-variations-content__product-elem ty-variations-content__product-elem-options">
        <bdi>
            <span class="ty-product-options">
                <span class="ty-product-options-content">
                    {$smarty.capture.options_content nofilter}
                </span>
            </span>
        </bdi>
    </td>
{/if}