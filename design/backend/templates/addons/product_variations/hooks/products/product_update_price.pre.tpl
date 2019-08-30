{if !$id}
    <div class="control-group">
        <label for="product_type" class="control-label cm-required">{__("product_variations.product_type")}</label>
        <div class="controls">
            <select name="product_data[product_type]" form="form" id="product_type">
                {foreach from=$product_types item="product_type_name" key="product_type_id"}
                    <option {if $product_data.product_type == $product_type_id}selected="selected"{/if} value="{$product_type_id}">{$product_type_name}</option>
                {/foreach}
            </select>
        </div>
    </div>
{/if}
