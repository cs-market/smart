{if $product.box_contains && $product.box_contains != 1}
    {if isset($product.selected_amount)}
    {assign var="default_amount" value=$product.selected_amount}
    {elseif !empty($product.min_qty)}
    {assign var="default_amount" value=$product.min_qty}
    {elseif !empty($product.qty_step)}
    {assign var="default_amount" value=$product.qty_step}
    {else}
    {assign var="default_amount" value="1"}
    {/if}
    <div class="ty-left" style="margin-bottom: 8px;"><span id="for_qty_count_{$obj_id}" data-ca-box-contains="{$product.box_contains}">{($default_amount/$product.box_contains)|round:2}</span>&nbsp;{__('of_box')}</div>
{/if}
